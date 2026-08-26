<?php

namespace App\Http\Controllers\Customer;

use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Contact;
use App\Http\Controllers\Controller;
use App\Product;
use App\Transaction;
use App\Utils\BusinessUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use App\Variation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class CustomerPortalController extends Controller
{
    protected $util;
    protected $transactionUtil;
    protected $productUtil;
    protected $businessUtil;

    public function __construct(
        Util $util,
        TransactionUtil $transactionUtil,
        ProductUtil $productUtil,
        BusinessUtil $businessUtil
    ) {
        $this->util = $util;
        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;
        $this->businessUtil = $businessUtil;
    }

    protected function contact(): Contact
    {
        return Auth::guard('customer')->user();
    }

    protected function initials(Contact $contact): string
    {
        $name = trim((string) ($contact->supplier_business_name ?: $contact->name ?: 'C'));
        $parts = preg_split('/\s+/', $name) ?: [];
        $a = strtoupper(substr($parts[0] ?? 'C', 0, 1));
        $b = isset($parts[1]) ? strtoupper(substr($parts[1], 0, 1)) : '';

        return $a.$b;
    }

    /**
     * Active business locations for the logged-in customer.
     * Label uses state (falls back to name if state is empty).
     */
    protected function customerLocations(Contact $contact)
    {
        return BusinessLocation::where('business_id', $contact->business_id)
            ->Active()
            ->orderBy('state')
            ->orderBy('name')
            ->get(['id', 'name', 'state'])
            ->mapWithKeys(function ($loc) {
                $label = trim((string) ($loc->state ?? ''));
                if ($label === '') {
                    $label = (string) $loc->name;
                }

                return [$loc->id => $label];
            });
    }

    /**
     * Preferred location id (session), falling back to first active location.
     */
    protected function defaultLocationId(Contact $contact, $locations = null): ?int
    {
        $locations = $locations ?? $this->customerLocations($contact);
        $ids = $locations->keys()->map(fn ($id) => (string) $id)->all();
        $current = session('customer.default_location_id');

        if (! empty($current) && in_array((string) $current, $ids, true)) {
            return (int) $current;
        }

        $first = $locations->keys()->first();
        if ($first) {
            session(['customer.default_location_id' => (int) $first]);

            return (int) $first;
        }

        return null;
    }

    protected function productImageUrl(?string $image): string
    {
        $default = asset('/img/default.png');
        if (empty($image)) {
            return $default;
        }
        $rel = 'uploads/'.config('constants.product_img_path').'/'.$image;
        if (! is_file(public_path($rel))) {
            return $default;
        }

        return asset($rel);
    }

    public function home()
    {
        $contact = $this->contact();
        $business_id = (int) $contact->business_id;
        $due = (float) $this->util->getContactDue($contact->id, $business_id);
        $locations = BusinessLocation::where('business_id', $business_id)->Active()->get(['id', 'name', 'state', 'landmark', 'city']);
        $defaultId = $this->defaultLocationId($contact, $this->customerLocations($contact));
        $location = $locations->firstWhere('id', $defaultId) ?: $locations->first();
        $business = Business::find($business_id);
        $newArrivals = $this->productCards($business_id, 8, 'newest');
        $topProducts = $this->productCards($business_id, 8, 'top');
        $initials = $this->initials($contact);

        return view('customer.home', compact(
            'contact', 'due', 'locations', 'location', 'business', 'newArrivals', 'topProducts', 'initials'
        ));
    }

    public function orders()
    {
        $contact = $this->contact();
        $orders = Transaction::where('business_id', $contact->business_id)
            ->where('contact_id', $contact->id)
            ->where(function ($q) {
                $q->where('type', 'sales_order')
                    ->orWhere(function ($q2) {
                        $q2->where('type', 'sell')->where('status', 'final');
                    });
            })
            ->orderByDesc('transaction_date')
            ->limit(50)
            ->get();
        $initials = $this->initials($contact);

        return view('customer.orders.index', compact('contact', 'orders', 'initials'));
    }

    public function showOrder($id)
    {
        $contact = $this->contact();
        $order = Transaction::with(['sell_lines.product', 'sell_lines.variations'])
            ->where('business_id', $contact->business_id)
            ->where('contact_id', $contact->id)
            ->where('id', $id)
            ->firstOrFail();

        $initials = $this->initials($contact);
        $fulfill = strtolower(trim((string) ($order->shipping_method ?: $order->shipping_details ?: 'pickup')));
        if (str_contains($fulfill, 'deliver')) {
            $fulfill = 'delivery';
        } elseif (str_contains($fulfill, 'ship')) {
            $fulfill = 'shipping';
        } else {
            $fulfill = 'pickup';
        }

        $lines = $order->sell_lines->map(function ($line) {
            $product = $line->product;
            $variation = $line->variations;
            $qty = (float) $line->quantity;
            $unitBefore = (float) ($line->unit_price_before_discount ?: $line->unit_price ?: $line->unit_price_inc_tax);
            $unitFinal = (float) ($line->unit_price_inc_tax ?: $line->unit_price);
            $lineDiscount = (float) $line->get_discount_amount();
            $size = (! empty($variation->name) && $variation->name !== 'DUMMY') ? $variation->name : 'N/A';
            $sku = $variation->sub_sku ?? $product->sku ?? 'N/A';
            $upc = $product->sku ?? 'N/A';
            if (! empty($variation->sub_sku) && $variation->sub_sku !== ($product->sku ?? '')) {
                $upc = $product->sku ?: 'N/A';
            }

            return [
                'name' => $product->name ?? 'Item',
                'image' => $product->image_url ?? asset('/img/default.png'),
                'sku' => $sku,
                'size' => $size,
                'upc' => $upc ?: 'N/A',
                'qty' => $qty,
                'unit_before' => $unitBefore,
                'unit_final' => $unitFinal,
                'line_total' => $qty * $unitFinal,
                'discount' => $lineDiscount * $qty,
                'has_discount' => $lineDiscount > 0.0001 || $unitBefore > $unitFinal + 0.0001,
            ];
        });

        $itemsTotal = (float) $lines->sum('line_total');

        return view('customer.orders.show', compact(
            'contact', 'order', 'initials', 'fulfill', 'lines', 'itemsTotal'
        ));
    }

    public function createOrder()
    {
        $contact = $this->contact();
        $locations = $this->customerLocations($contact);
        $default_location = $this->defaultLocationId($contact, $locations);
        $initials = $this->initials($contact);

        return view('customer.orders.create', compact('contact', 'locations', 'default_location', 'initials'));
    }

    public function storeOrder(Request $request)
    {
        $contact = $this->contact();
        $business_id = (int) $contact->business_id;

        $data = $request->validate([
            'location_id' => 'required|integer',
            'shipping_method' => 'nullable|string|max:100',
            'shipping_details' => 'nullable|string|max:1000',
            'shipping_address' => 'nullable|string|max:1000',
            'sale_note' => 'nullable|string|max:500',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer',
            'products.*.variation_id' => 'required|integer',
            'products.*.quantity' => 'required|numeric|min:0.0001',
            'products.*.unit_price' => 'required|numeric|min:0',
        ]);

        if (! BusinessLocation::where('business_id', $business_id)->where('id', $data['location_id'])->exists()) {
            return back()->withInput()->with('error', 'Invalid location.');
        }

        $business = Business::find($business_id);
        $user_id = (int) ($business->owner_id ?? $contact->created_by ?? 1);

        try {
            $transaction = DB::transaction(function () use ($data, $contact, $business_id, $user_id) {
                $products = [];
                foreach ($data['products'] as $line) {
                    $product = Product::where('business_id', $business_id)->findOrFail($line['product_id']);
                    $variation = Variation::where('product_id', $product->id)->findOrFail($line['variation_id']);
                    $price = (float) $line['unit_price'];
                    $qty = (float) $line['quantity'];
                    $products[] = [
                        'product_id' => $product->id,
                        'variation_id' => $variation->id,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'unit_price_inc_tax' => $price,
                        'item_tax' => 0,
                        'tax_id' => null,
                        'enable_stock' => $product->enable_stock,
                        'product_type' => $product->type,
                        'line_discount_type' => 'fixed',
                        'line_discount_amount' => 0,
                    ];
                }

                $invoice_total = $this->productUtil->calculateInvoiceTotal($products, null, null, false);
                if ($invoice_total === false) {
                    throw new \RuntimeException('Could not calculate total.');
                }
                $final_total = (float) $invoice_total['final_total'];
                $shipMethod = $data['shipping_method'] ?? 'Delivery';
                $customerLabel = trim((string) ($contact->supplier_business_name ?: $contact->name ?: 'Customer'));

                $input = [
                    'type' => 'sales_order',
                    'status' => 'ordered',
                    'contact_id' => $contact->id,
                    'customer_group_id' => $contact->customer_group_id,
                    'transaction_date' => Carbon::now(),
                    'location_id' => $data['location_id'],
                    'final_total' => $final_total,
                    'discount_type' => 'fixed',
                    'discount_amount' => 0,
                    'tax_rate_id' => null,
                    'sale_note' => $data['sale_note'] ?? null,
                    'staff_note' => 'Ordered via Customer App by '.$customerLabel,
                    'is_direct_sale' => 1,
                    'is_created_from_api' => 1,
                    'source' => 'customer_app',
                    'commission_agent' => null,
                    'exchange_rate' => 1,
                    'products' => $products,
                    'shipping_details' => $data['shipping_details'] ?? $shipMethod,
                    'shipping_address' => $data['shipping_address'] ?? ($contact->shipping_address ?: ''),
                    'shipping_status' => 'ordered',
                    'shipping_method' => $shipMethod,
                    'shipping_charges' => 0,
                ];

                $transaction = $this->transactionUtil->createSellTransaction(
                    $business_id,
                    $input,
                    array_merge($invoice_total, ['final_total' => $final_total]),
                    $user_id,
                    false
                );

                $this->transactionUtil->createOrUpdateSellLines(
                    $transaction,
                    $products,
                    $data['location_id'],
                    false,
                    null,
                    [],
                    false
                );

                return $transaction;
            });

            return redirect()
                ->route('customer.orders.show', $transaction->id)
                ->with('success', 'Order placed: '.$transaction->invoice_no);
        } catch (\Throwable $e) {
            Log::error('Customer app order failed: '.$e->getMessage());

            return back()->withInput()->with('error', 'Could not place order. Please try again.');
        }
    }

    public function documents(Request $request)
    {
        $contact = $this->contact();

        $start = trim((string) $request->get('start_date', ''));
        $end = trim((string) $request->get('end_date', ''));
        $q = trim((string) $request->get('q', ''));
        $hasDateFilter = ($start !== '' || $end !== '');

        $query = Transaction::where('business_id', $contact->business_id)
            ->where('contact_id', $contact->id)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->with('payment_lines');

        // Date filter only when user applies From/To
        if ($start !== '') {
            try {
                $query->whereDate('transaction_date', '>=', Carbon::parse($start)->toDateString());
            } catch (\Throwable $e) {
            }
        }
        if ($end !== '') {
            try {
                $query->whereDate('transaction_date', '<=', Carbon::parse($end)->toDateString());
            } catch (\Throwable $e) {
            }
        }
        if ($q !== '') {
            $query->where('invoice_no', 'like', '%'.$q.'%');
        }

        // Default = all invoices; with date filter = all matching in range
        $invoices = $query->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get()
            ->map(function ($t) {
                $t->total_paid = $t->payment_lines->sum('amount');

                return $t;
            });

        $grouped = $invoices->groupBy(function ($t) {
            return Carbon::parse($t->transaction_date)->format('Y-m-d');
        });

        $taxSummary = [
            'invoice_count' => $invoices->count(),
            'sales_total' => (float) $invoices->sum('final_total'),
            'tax_total' => (float) $invoices->sum('tax_amount'),
            'paid_total' => (float) $invoices->sum('total_paid'),
        ];

        $initials = $this->initials($contact);

        return view('customer.documents', compact(
            'contact', 'invoices', 'grouped', 'initials', 'start', 'end', 'hasDateFilter', 'q', 'taxSummary'
        ));
    }

    /**
     * Show the real order invoice (same receipt layout as admin/sale print).
     */
    public function showInvoice($id)
    {
        $contact = $this->contact();
        $invoice = Transaction::with('location')
            ->where('business_id', $contact->business_id)
            ->where('contact_id', $contact->id)
            ->where('id', $id)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->firstOrFail();

        try {
            $location = $invoice->location;
            if (empty($location)) {
                throw new \RuntimeException('Invoice location not found.');
            }

            $business_id = (int) $contact->business_id;
            $business_details = $this->businessUtil->getDetails($business_id);
            $invoice_layout_id = ! empty($invoice->is_direct_sale)
                ? ($location->sale_invoice_layout_id ?: $location->invoice_layout_id)
                : $location->invoice_layout_id;
            $invoice_layout = $this->businessUtil->invoiceLayout($business_id, $invoice_layout_id);

            $receipt_details = $this->transactionUtil->getReceiptDetails(
                $invoice->id,
                $invoice->location_id,
                $invoice_layout,
                $business_details,
                $location,
                'browser'
            );
            $receipt_details->currency = [
                'symbol' => $business_details->currency_symbol,
                'thousand_separator' => $business_details->thousand_separator,
                'decimal_separator' => $business_details->decimal_separator,
            ];
            $receipt_details->design = 'us_letter';
            $layout = 'sale_pos.receipts.us_letter';
            if (! view()->exists($layout)) {
                $layout = 'sale_pos.receipts.classic';
            }

            $html = view($layout, compact('receipt_details'))->render();
            $title = $invoice->invoice_no;

            return response()->view('customer.invoice', compact('html', 'title', 'invoice'));
        } catch (\Throwable $e) {
            Log::error('Customer invoice view failed: '.$e->getMessage());

            return redirect()
                ->route('customer.documents')
                ->with('error', 'Could not open invoice.');
        }
    }

    public function priceCheck()
    {
        $contact = $this->contact();
        $initials = $this->initials($contact);
        $locations = $this->customerLocations($contact);
        $default_location = $this->defaultLocationId($contact, $locations);

        return view('customer.price_check', compact('contact', 'initials', 'locations', 'default_location'));
    }

    public function profile()
    {
        $contact = $this->contact();
        $initials = $this->initials($contact);
        $locations = $this->customerLocations($contact);
        $current_location_id = $this->defaultLocationId($contact, $locations);

        return view('customer.profile', compact('contact', 'initials', 'locations', 'current_location_id'));
    }

    public function updateLocation(Request $request)
    {
        $contact = $this->contact();
        $data = $request->validate([
            'location_id' => 'required|integer',
        ]);

        $locations = $this->customerLocations($contact);
        $locationId = (int) $data['location_id'];
        if (! $locations->keys()->contains(fn ($id) => (int) $id === $locationId)) {
            return back()->with('error', 'Invalid location.');
        }

        session(['customer.default_location_id' => $locationId]);

        return back()->with('success', 'Location updated: '.($locations[$locationId] ?? ''));
    }

    public function updatePassword(Request $request)
    {
        $contact = $this->contact();

        $data = $request->validate([
            'current_password' => 'required|string|max:191',
            'password' => ['required', 'string', 'confirmed', Password::min(4)],
        ]);

        if (! Hash::check($data['current_password'], (string) $contact->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        $contact->password = Hash::make($data['password']);
        $contact->save();

        return back()->with('success', 'Password updated successfully.');
    }

    public function categoriesTree()
    {
        $contact = $this->contact();
        $tree = Category::catAndSubCategories((int) $contact->business_id, false);

        return response()->json(collect($tree)->map(function ($cat) {
            return [
                'id' => (int) $cat['id'],
                'name' => $cat['name'],
                'sub_categories' => collect($cat['sub_categories'] ?? [])->map(function ($sub) {
                    return [
                        'id' => (int) $sub['id'],
                        'name' => $sub['name'],
                    ];
                })->values(),
            ];
        })->values());
    }

    public function searchProducts(Request $request)
    {
        $contact = $this->contact();
        $business_id = (int) $contact->business_id;
        $location_id = (int) $request->get('location_id');
        $term = trim((string) $request->get('q', ''));
        $category_id = (int) $request->get('category_id', 0);
        $sub_category_id = (int) $request->get('sub_category_id', 0);
        $variation_id = (int) $request->get('variation_id', 0);
        $limit = min(80, max(1, (int) $request->get('limit', 20)));

        if ($location_id < 1) {
            return response()->json([]);
        }

        $query = Variation::join('products as p', 'variations.product_id', '=', 'p.id')
            ->leftJoin('variation_location_details as vld', function ($join) use ($location_id) {
                $join->on('variations.id', '=', 'vld.variation_id')
                    ->where('vld.location_id', '=', $location_id);
            })
            ->where('p.business_id', $business_id)
            ->where('p.type', '!=', 'modifier')
            ->where('p.is_inactive', 0)
            ->whereNull('variations.deleted_at')
            ->where(function ($q) {
                $q->whereNull('p.not_for_selling')->orWhere('p.not_for_selling', 0);
            });

        if ($variation_id > 0) {
            $query->where('variations.id', $variation_id);
        } elseif ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('p.name', 'like', "%{$term}%")
                    ->orWhere('p.sku', 'like', "%{$term}%")
                    ->orWhere('variations.sub_sku', 'like', "%{$term}%");
            });
        }

        if ($sub_category_id > 0) {
            $query->where('p.sub_category_id', $sub_category_id);
        } elseif ($category_id > 0) {
            $query->where('p.category_id', $category_id);
        }

        $rows = $query->select(
            'variations.id as variation_id',
            'variations.name as variation_name',
            'variations.sub_sku',
            'variations.sell_price_inc_tax',
            'variations.default_sell_price',
            'p.id as product_id',
            'p.name as product_name',
            'p.image as product_image',
            DB::raw('COALESCE(vld.qty_available, 0) as stock')
        )
            ->orderBy('p.name')
            ->limit($limit)
            ->get();

        return response()->json($rows->map(function ($r) {
            $label = $r->product_name;
            if (! empty($r->variation_name) && $r->variation_name !== 'DUMMY') {
                $label .= ' - '.$r->variation_name;
            }

            return [
                'product_id' => (int) $r->product_id,
                'variation_id' => (int) $r->variation_id,
                'name' => $label,
                'sku' => $r->sub_sku,
                'price' => (float) ($r->sell_price_inc_tax ?: $r->default_sell_price ?: 0),
                'stock' => (float) $r->stock,
                'image' => $this->productImageUrl($r->product_image),
            ];
        }));
    }

    protected function productCards(int $business_id, int $limit, string $mode)
    {
        $query = Product::where('business_id', $business_id)
            ->where('is_inactive', 0)
            ->where(function ($q) {
                $q->whereNull('not_for_selling')->orWhere('not_for_selling', 0);
            })
            ->with(['variations' => function ($q) {
                $q->whereNull('deleted_at');
            }]);

        if ($mode === 'newest') {
            $query->orderByDesc('id');
        } else {
            $query->orderBy('name');
        }

        return $query->limit($limit)->get()->map(function ($p) {
            $v = $p->variations->first();
            if (! $v) {
                return null;
            }

            return [
                'product_id' => $p->id,
                'variation_id' => $v->id,
                'name' => $p->name,
                'sku' => $v->sub_sku ?? $p->sku,
                'price' => (float) ($v->sell_price_inc_tax ?: $v->default_sell_price ?: 0),
                'image' => $this->productImageUrl($p->image),
            ];
        })->filter()->values();
    }
}
