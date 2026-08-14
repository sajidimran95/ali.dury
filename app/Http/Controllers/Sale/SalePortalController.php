<?php

namespace App\Http\Controllers\Sale;

use App\BusinessLocation;
use App\Category;
use App\Contact;
use App\Http\Controllers\Controller;
use App\Product;
use App\Transaction;
use App\Utils\BusinessUtil;
use App\Utils\ContactUtil;
use App\Utils\ProductUtil;
use App\Utils\SalesOrderPermission;
use App\Utils\TransactionUtil;
use App\Variation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Mobile sales-rep portal: list own orders, create order for customer.
 * Creates Sales Orders (type=sales_order, status=ordered) — same as admin Sales Orders.
 * Source = sale_app. Admin converts SO → invoice as usual (no admin code changes).
 */
class SalePortalController extends Controller
{
    protected $transactionUtil;

    protected $productUtil;

    protected $contactUtil;

    protected $businessUtil;

    public function __construct(
        TransactionUtil $transactionUtil,
        ProductUtil $productUtil,
        ContactUtil $contactUtil,
        BusinessUtil $businessUtil
    ) {
        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;
        $this->contactUtil = $contactUtil;
        $this->businessUtil = $businessUtil;
    }

    public function home(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user_id = auth()->id();

        $locations = BusinessLocation::forDropdown($business_id, false);
        $locationIds = array_map('strval', array_keys($locations->toArray()));
        $current_location_id = $request->session()->get('user.default_location_id');
        if (empty($current_location_id) || ! in_array((string) $current_location_id, $locationIds, true)) {
            $current_location_id = count($locations) ? array_key_first($locations->toArray()) : null;
            if ($current_location_id) {
                $request->session()->put('user.default_location_id', $current_location_id);
            }
        }
        $current_location_name = $current_location_id
            ? ($locations[$current_location_id] ?? $locations[(string) $current_location_id] ?? '—')
            : 'No location';

        $base = Transaction::where('business_id', $business_id)
            ->where('type', 'sales_order')
            ->where('created_by', $user_id);

        $todayStart = Carbon::now()->startOfDay();
        $monthStart = Carbon::now()->startOfMonth();

        $invoiceMap = $this->saleAppInvoiceMap((int) $business_id);
        $convertedIds = array_keys($invoiceMap);
        $invoiceTotals = $this->saleAppInvoiceTotals($invoiceMap);

        $todayRows = (clone $base)->where('transaction_date', '>=', $todayStart)->get(['id', 'final_total']);
        $monthRows = (clone $base)->where('transaction_date', '>=', $monthStart)->get(['id', 'final_total']);

        $stats = [
            'total_orders' => (clone $base)->count(),
            'today_orders' => $todayRows->count(),
            'today_total' => $this->saleAppSumDisplayTotals($todayRows, $invoiceMap, $invoiceTotals),
            'month_orders' => $monthRows->count(),
            'month_total' => $this->saleAppSumDisplayTotals($monthRows, $invoiceMap, $invoiceTotals),
            'paid_orders' => (clone $base)->where('status', 'completed')
                ->when($convertedIds !== [], fn ($q) => $q->whereIn('id', $convertedIds), fn ($q) => $q->whereRaw('1 = 0'))
                ->count(),
            'due_orders' => (clone $base)->where(function ($q) use ($convertedIds) {
                $q->where('status', '!=', 'completed');
                if ($convertedIds !== []) {
                    $q->orWhereNotIn('id', $convertedIds);
                }
            })->count(),
        ];

        $recent = (clone $base)
            ->with(['contact:id,name,mobile,supplier_business_name'])
            ->orderByDesc('id')
            ->limit(8)
            ->get();
        $this->applySaleAppOrderStatus($recent, $invoiceMap, $invoiceTotals);

        // Newest products
        $newProducts = Product::where('business_id', $business_id)
            ->where('type', '!=', 'modifier')
            ->where('is_inactive', 0)
            ->where(function ($q) {
                $q->whereNull('not_for_selling')->orWhere('not_for_selling', 0);
            })
            ->with(['variations' => function ($q) {
                $q->whereNull('deleted_at')->orderBy('id');
            }])
            ->orderByDesc('id')
            ->limit(6)
            ->get()
            ->map(function ($p) {
                $v = $p->variations->first();
                $price = $v ? (float) ($v->sell_price_inc_tax ?: $v->default_sell_price ?: 0) : 0;
                $img = $this->saleProductImage($p->image);

                return [
                    'id' => $p->id,
                    'variation_id' => $v ? $v->id : null,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'price' => $price,
                    'image' => $img['url'],
                    'has_image' => $img['has_image'],
                ];
            });

        // Top products by qty sold by this rep
        $topProducts = DB::table('transaction_sell_lines as tsl')
            ->join('transactions as t', 't.id', '=', 'tsl.transaction_id')
            ->join('products as p', 'p.id', '=', 'tsl.product_id')
            ->leftJoin('variations as v', 'v.id', '=', 'tsl.variation_id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->where('t.created_by', $user_id)
            ->whereNull('tsl.parent_sell_line_id')
            ->select(
                'p.id as product_id',
                'p.name',
                'p.sku',
                'p.image',
                DB::raw('MAX(tsl.variation_id) as variation_id'),
                DB::raw('SUM(tsl.quantity) as qty_sold'),
                DB::raw('MAX(COALESCE(v.sell_price_inc_tax, v.default_sell_price, tsl.unit_price_inc_tax, 0)) as price')
            )
            ->groupBy('p.id', 'p.name', 'p.sku', 'p.image')
            ->orderByDesc('qty_sold')
            ->limit(6)
            ->get()
            ->map(function ($row) {
                $img = $this->saleProductImage($row->image);
                $row->image_url = $img['url'];
                $row->has_image = $img['has_image'];

                return $row;
            });

        return view('sale.dashboard', compact(
            'stats',
            'recent',
            'locations',
            'current_location_id',
            'current_location_name',
            'newProducts',
            'topProducts'
        ));
    }

    public function orders(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user_id = auth()->id();
        $q = trim((string) $request->get('q', ''));
        $status = (string) $request->get('status', '');

        $invoiceMap = $this->saleAppInvoiceMap((int) $business_id);
        $convertedIds = array_keys($invoiceMap);

        $orders = Transaction::where('business_id', $business_id)
            ->where('type', 'sales_order')
            ->where('created_by', $user_id)
            ->with(['contact:id,name,mobile,supplier_business_name'])
            ->when($q !== '', function ($query) use ($q, $invoiceMap) {
                $term = '%'.$q.'%';
                $matchedSoIds = [];
                foreach ($invoiceMap as $soId => $invoices) {
                    foreach ($invoices as $inv) {
                        if (stripos((string) ($inv->invoice_no ?? ''), $q) !== false) {
                            $matchedSoIds[] = (int) $soId;
                            break;
                        }
                    }
                }
                $query->where(function ($inner) use ($term, $matchedSoIds) {
                    $inner->where('invoice_no', 'like', $term)
                        ->orWhereHas('contact', function ($c) use ($term) {
                            $c->where('name', 'like', $term)
                                ->orWhere('mobile', 'like', $term)
                                ->orWhere('supplier_business_name', 'like', $term);
                        });
                    if ($matchedSoIds !== []) {
                        $inner->orWhereIn('id', $matchedSoIds);
                    }
                });
            })
            ->when($status === 'sale', function ($query) use ($convertedIds) {
                if ($convertedIds !== []) {
                    $query->whereNotIn('id', $convertedIds);
                }
            })
            ->when($status === 'invoiced', function ($query) use ($convertedIds) {
                if ($convertedIds === []) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('id', $convertedIds);
                }
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $this->applySaleAppOrderStatus($orders->getCollection(), $invoiceMap);

        return view('sale.orders.index', compact('orders', 'q', 'status'));
    }

    public function show(Request $request, $id)
    {
        $order = $this->findOwnOrder($id);
        $order->load(['contact', 'sell_lines.product', 'sell_lines.variations', 'payment_lines']);
        $invoiceMap = $this->saleAppInvoiceMap((int) $order->business_id);
        $this->applySaleAppInvoiceSnapshot($order, $invoiceMap);
        $amounts = $this->saleAppAmountBreakdown($order);

        return view('sale.orders.show', compact('order', 'amounts'));
    }

    /**
     * Show the same invoice HTML as admin Sales Order → Print Invoice.
     */
    public function downloadInvoice(Request $request, $id)
    {
        $order = $this->findOwnOrder($id);
        $business_id = $request->session()->get('user.business_id');

        try {
            $invoiceMap = $this->saleAppInvoiceMap((int) $order->business_id);
            $printTx = $this->saleAppPrintTransaction($order, $invoiceMap);
            $printTx->loadMissing('location');
            $location = $printTx->location;
            if (empty($location)) {
                throw new \RuntimeException('Order location not found.');
            }

            $business_details = $this->businessUtil->getDetails($business_id);
            // Same layout selection as SellPosController::printInvoice / printInvoicePdf
            $invoice_layout_id = ! empty($printTx->is_direct_sale)
                ? ($location->sale_invoice_layout_id ?: $location->invoice_layout_id)
                : $location->invoice_layout_id;
            $invoice_layout = $this->businessUtil->invoiceLayout($business_id, $invoice_layout_id);

            $receipt_details = $this->transactionUtil->getReceiptDetails(
                $printTx->id,
                $printTx->location_id,
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

            // Keep the sales-order number (same series as admin Sales Orders).
            $receipt_details->invoice_no = $order->invoice_no;
            if ((int) $printTx->id !== (int) $order->id) {
                $soHeading = data_get($invoice_layout->common_settings, 'sales_order_heading');
                $receipt_details->invoice_heading = ! empty($soHeading) ? $soHeading : __('lang_v1.sales_order');
                if (! empty($invoice_layout->quotation_no_prefix)) {
                    $receipt_details->invoice_no_prefix = $invoice_layout->quotation_no_prefix;
                }
                if (! empty($receipt_details->barcode)) {
                    $receipt_details->barcode = $order->invoice_no;
                }
            }

            // Sale App always uses US Letter invoice design
            $receipt_details->design = 'us_letter';
            $layout = 'sale_pos.receipts.us_letter';
            if (! view()->exists($layout)) {
                $layout = 'sale_pos.receipts.classic';
            }

            $html = view($layout, compact('receipt_details'))->render();
            $title = $order->invoice_no;
            $orderId = $order->id;

            return response()->view('sale.orders.invoice', compact('html', 'title', 'orderId'));
        } catch (\Throwable $e) {
            \Log::error('Sale app invoice show failed: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('status', [
                'success' => 0,
                'msg' => 'Could not open invoice.',
            ]);
        }
    }

    /**
     * Delete own sale (same stock/mapping cleanup as admin).
     */
    public function destroy(Request $request, $id)
    {
        $order = $this->findOwnOrder($id);

        if (! SalesOrderPermission::canDelete(auth()->user(), $order)) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        try {
            DB::beginTransaction();
            $output = $this->transactionUtil->deleteSale($business_id, $order->id);
            DB::commit();

            $ok = ! empty($output['success']);
            $msg = $output['msg'] ?? ($ok ? 'Order deleted.' : 'Could not delete order.');

            return redirect()
                ->route('sale.orders')
                ->with('status', ['success' => $ok ? 1 : 0, 'msg' => $msg]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Sale app order delete failed: '.$e->getMessage());

            return redirect()
                ->route('sale.orders')
                ->with('status', ['success' => 0, 'msg' => 'Could not delete order.']);
        }
    }

    /**
     * Own sales order only (created by logged-in sales rep).
     */
    protected function findOwnOrder($id): Transaction
    {
        return Transaction::where('business_id', session('user.business_id'))
            ->where('type', 'sales_order')
            ->where('created_by', auth()->id())
            ->findOrFail($id);
    }

    public function create(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $locations = BusinessLocation::forDropdown($business_id, false);
        $default_location = null;
        if (count($locations) === 1) {
            $default_location = array_key_first($locations->toArray());
        } elseif ($request->session()->has('user.default_location_id')) {
            $default_location = $request->session()->get('user.default_location_id');
        } elseif (count($locations) > 0) {
            $default_location = array_key_first($locations->toArray());
        }

        $default_customer = null;
        $oldContactId = old('contact_id');
        if (! empty($oldContactId)) {
            $c = Contact::where('business_id', $business_id)
                ->onlyCustomers()
                ->find($oldContactId);
            if ($c) {
                $default_customer = [
                    'id' => $c->id,
                    'text' => trim(($c->supplier_business_name ? $c->supplier_business_name.' — ' : '').$c->name.
                        ($c->mobile ? ' ('.$c->mobile.')' : '')),
                    'shipping_address' => $c->shipping_address,
                ];
            }
        }
        if (empty($default_customer)) {
            $walkIn = $this->contactUtil->getWalkInCustomer($business_id, false);
            if ($walkIn) {
                $default_customer = [
                    'id' => $walkIn->id,
                    'text' => trim(($walkIn->supplier_business_name ? $walkIn->supplier_business_name.' — ' : '').$walkIn->name.
                        ($walkIn->mobile ? ' ('.$walkIn->mobile.')' : '')),
                    'shipping_address' => $walkIn->shipping_address,
                ];
            }
        }

        return view('sale.orders.create', compact('locations', 'default_location', 'default_customer'));
    }

    public function searchCustomers(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $term = trim((string) $request->get('q', ''));

        $query = Contact::where('business_id', $business_id)
            ->onlyCustomers()
            ->where('contact_status', 'active');

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('mobile', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('supplier_business_name', 'like', "%{$term}%")
                    ->orWhere('contact_id', 'like', "%{$term}%");
            });
        }

        $rows = $query->orderBy('name')->limit(25)->get(['id', 'name', 'mobile', 'supplier_business_name', 'shipping_address']);

        return response()->json($rows->map(function ($c) {
            return [
                'id' => $c->id,
                'text' => trim(($c->supplier_business_name ? $c->supplier_business_name.' — ' : '').$c->name.
                    ($c->mobile ? ' ('.$c->mobile.')' : '')),
                'name' => $c->name,
                'mobile' => $c->mobile,
                'shipping_address' => $c->shipping_address,
            ];
        }));
    }

    public function searchProducts(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $location_id = (int) $request->get('location_id');
        $term = trim((string) $request->get('q', ''));
        $category_id = (int) $request->get('category_id', 0);
        $sub_category_id = (int) $request->get('sub_category_id', 0);
        $variation_id = (int) $request->get('variation_id', 0);
        $limit = min(100, max(1, (int) $request->get('limit', 30)));

        if (empty($location_id)) {
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
        }

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('p.name', 'like', "%{$term}%")
                    ->orWhere('p.sku', 'like', "%{$term}%")
                    ->orWhere('variations.sub_sku', 'like', "%{$term}%")
                    ->orWhere('variations.name', 'like', "%{$term}%");
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
            'p.type as product_type',
            'p.enable_stock',
            'p.tax as tax_id',
            'p.category_id',
            'p.sub_category_id',
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
            if (! empty($r->sub_sku)) {
                $label .= ' ('.$r->sub_sku.')';
            }

            $price = (float) ($r->sell_price_inc_tax ?: $r->default_sell_price ?: 0);
            $img = $this->saleProductImage($r->product_image);

            return [
                'product_id' => (int) $r->product_id,
                'variation_id' => (int) $r->variation_id,
                'name' => $label,
                'sku' => $r->sub_sku,
                'price' => $price,
                'stock' => (float) $r->stock,
                'enable_stock' => (int) $r->enable_stock,
                'product_type' => $r->product_type,
                'tax_id' => $r->tax_id,
                'category_id' => (int) $r->category_id,
                'sub_category_id' => (int) $r->sub_category_id,
                'image' => $img['url'],
                'has_image' => $img['has_image'],
            ];
        }));
    }

    /**
     * Category → subcategory tree for catalog / filters.
     */
    public function categoriesTree(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $tree = Category::catAndSubCategories($business_id, false);

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

    /**
     * Browse all products with category filters (Sales App Products tab).
     */
    public function products(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $locations = BusinessLocation::forDropdown($business_id, false);
        $locationIds = array_map('strval', array_keys($locations->toArray()));
        $default_location = $request->session()->get('user.default_location_id');
        if (empty($default_location) || ! in_array((string) $default_location, $locationIds, true)) {
            $default_location = count($locations) ? array_key_first($locations->toArray()) : null;
        }
        $categories = Category::catAndSubCategories($business_id, false);
        $categoriesJson = collect($categories)->map(function ($c) {
            return [
                'id' => (int) $c['id'],
                'name' => $c['name'],
                'sub_categories' => collect($c['sub_categories'] ?? [])->map(function ($s) {
                    return [
                        'id' => (int) $s['id'],
                        'name' => $s['name'],
                    ];
                })->values(),
            ];
        })->values();

        return view('sale.products.index', compact('locations', 'default_location', 'categories', 'categoriesJson'));
    }

    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user_id = auth()->id();

        $data = $request->validate([
            'contact_id' => 'required|integer',
            'location_id' => 'required|integer',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer',
            'products.*.variation_id' => 'required|integer',
            'products.*.quantity' => 'required|numeric|min:0.0001',
            'products.*.unit_price' => 'required|numeric|min:0',
            'payment_type' => 'required|in:cash,due',
            'sale_note' => 'nullable|string|max:500',
            'shipping_details' => 'required|string|max:1000',
            'shipping_address' => 'required|string|max:1000',
            'shipping_status' => 'nullable|in:ordered,packed,shipped,delivered,cancelled',
        ]);

        // Verify customer belongs to business
        $contact = Contact::where('business_id', $business_id)
            ->onlyCustomers()
            ->findOrFail($data['contact_id']);

        // Verify location access
        $locations = BusinessLocation::forDropdown($business_id, false)->toArray();
        if (! array_key_exists($data['location_id'], $locations)) {
            return back()->withInput()->with('status', ['success' => 0, 'msg' => 'Invalid location.']);
        }

        try {
            $transaction = DB::transaction(function () use ($request, $data, $business_id, $user_id, $contact) {
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

                $shipping_charges = 0;
                $final_total = (float) $invoice_total['final_total'] + $shipping_charges;

                $cg = $this->contactUtil->getCustomerGroup($business_id, $contact->id);

                $input = [
                    'type' => 'sales_order',
                    'status' => 'ordered',
                    'contact_id' => $contact->id,
                    'customer_group_id' => (empty($cg) || empty($cg->id)) ? null : $cg->id,
                    'transaction_date' => Carbon::now(),
                    'location_id' => $data['location_id'],
                    'final_total' => $final_total,
                    'discount_type' => 'fixed',
                    'discount_amount' => 0,
                    'tax_rate_id' => null,
                    'sale_note' => $data['sale_note'] ?? null,
                    'is_direct_sale' => 1,
                    'source' => 'sale_app',
                    'commission_agent' => null,
                    'exchange_rate' => 1,
                    'products' => $products,
                    'shipping_details' => $data['shipping_details'] ?? null,
                    'shipping_address' => $data['shipping_address'],
                    'shipping_status' => $data['shipping_status'] ?? 'ordered',
                    'shipping_charges' => 0,
                ];

                $cmsn = $request->session()->get('business.sales_cmsn_agnt');
                if ($cmsn === 'logged_in_user') {
                    $input['commission_agent'] = $user_id;
                }

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

                // Sales order: no stock decrease / payment until admin invoices it

                return $transaction;
            });

            return redirect()
                ->route('sale.orders')
                ->with('status', [
                    'success' => 1,
                    'msg' => 'Sales order created: '.$transaction->invoice_no,
                ]);
        } catch (\Throwable $e) {
            \Log::error('Sale portal create failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()->withInput()->with('status', [
                'success' => 0,
                'msg' => 'Could not create order. '.$e->getMessage(),
            ]);
        }
    }

    public function edit(Request $request, $id)
    {
        $order = $this->findOwnOrder($id);
        $order->load(['contact', 'sell_lines.product', 'sell_lines.variations']);

        if (! $this->saleAppCanEdit($order)) {
            return redirect()
                ->route('sale.orders')
                ->with('status', ['success' => 0, 'msg' => 'This order is invoiced and cannot be edited.']);
        }

        $business_id = $request->session()->get('user.business_id');
        $locations = BusinessLocation::forDropdown($business_id, false);
        $default_location = $order->location_id;

        $c = $order->contact;
        $default_customer = $c ? [
            'id' => $c->id,
            'text' => trim(($c->supplier_business_name ? $c->supplier_business_name.' — ' : '').$c->name.
                ($c->mobile ? ' ('.$c->mobile.')' : '')),
            'shipping_address' => $order->shipping_address ?: $c->shipping_address,
        ] : null;

        $edit_lines = $order->sell_lines
            ->whereNull('parent_sell_line_id')
            ->values()
            ->map(function ($line) {
                $name = optional($line->product)->name ?: 'Item';
                $vname = optional($line->variations)->name;
                if (! empty($vname) && $vname !== 'DUMMY') {
                    $name .= ' - '.$vname;
                }

                return [
                    'product_id' => (int) $line->product_id,
                    'variation_id' => (int) $line->variation_id,
                    'transaction_sell_lines_id' => (int) $line->id,
                    'name' => $name,
                    'price' => (float) $line->unit_price_inc_tax,
                    'quantity' => (float) $line->quantity,
                    'enable_stock' => (int) optional($line->product)->enable_stock,
                    'product_type' => optional($line->product)->type,
                ];
            });

        $edit_order = $order;

        return view('sale.orders.create', compact(
            'locations',
            'default_location',
            'default_customer',
            'edit_order',
            'edit_lines'
        ));
    }

    public function update(Request $request, $id)
    {
        $order = $this->findOwnOrder($id);

        if (! $this->saleAppCanEdit($order)) {
            return redirect()
                ->route('sale.orders')
                ->with('status', ['success' => 0, 'msg' => 'This order is invoiced and cannot be edited.']);
        }

        $business_id = $request->session()->get('user.business_id');
        $user_id = auth()->id();

        $data = $request->validate([
            'contact_id' => 'required|integer',
            'location_id' => 'required|integer',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer',
            'products.*.variation_id' => 'required|integer',
            'products.*.quantity' => 'required|numeric|min:0.0001',
            'products.*.unit_price' => 'required|numeric|min:0',
            'products.*.transaction_sell_lines_id' => 'nullable|integer',
            'payment_type' => 'required|in:cash,due',
            'sale_note' => 'nullable|string|max:500',
            'shipping_details' => 'required|string|max:1000',
            'shipping_address' => 'required|string|max:1000',
            'shipping_status' => 'nullable|in:ordered,packed,shipped,delivered,cancelled',
        ]);

        $contact = Contact::where('business_id', $business_id)
            ->onlyCustomers()
            ->findOrFail($data['contact_id']);

        $locations = BusinessLocation::forDropdown($business_id, false)->toArray();
        if (! array_key_exists($data['location_id'], $locations)) {
            return back()->withInput()->with('status', ['success' => 0, 'msg' => 'Invalid location.']);
        }

        try {
            DB::transaction(function () use ($data, $business_id, $user_id, $contact, $order) {
                $products = [];
                foreach ($data['products'] as $line) {
                    $product = Product::where('business_id', $business_id)->findOrFail($line['product_id']);
                    $variation = Variation::where('product_id', $product->id)->findOrFail($line['variation_id']);
                    $price = (float) $line['unit_price'];
                    $qty = (float) $line['quantity'];

                    $row = [
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
                    if (! empty($line['transaction_sell_lines_id'])) {
                        $row['transaction_sell_lines_id'] = (int) $line['transaction_sell_lines_id'];
                    }
                    $products[] = $row;
                }

                $invoice_total = $this->productUtil->calculateInvoiceTotal($products, null, null, false);
                if ($invoice_total === false) {
                    throw new \RuntimeException('Could not calculate total.');
                }

                $final_total = (float) $invoice_total['final_total'];
                $cg = $this->contactUtil->getCustomerGroup($business_id, $contact->id);

                $input = [
                    'status' => $order->status ?: 'ordered',
                    'contact_id' => $contact->id,
                    'customer_group_id' => (empty($cg) || empty($cg->id)) ? null : $cg->id,
                    'final_total' => $final_total,
                    'discount_type' => $order->discount_type ?: 'fixed',
                    'discount_amount' => $order->discount_amount ?: 0,
                    'tax_rate_id' => $order->tax_id,
                    'sale_note' => $data['sale_note'] ?? null,
                    'commission_agent' => $order->commission_agent,
                    'exchange_rate' => $order->exchange_rate ?: 1,
                    'shipping_details' => $data['shipping_details'] ?? null,
                    'shipping_address' => $data['shipping_address'],
                    'shipping_status' => $data['shipping_status'] ?? ($order->shipping_status ?: 'ordered'),
                    'shipping_charges' => $order->shipping_charges ?: 0,
                    'source' => $order->source ?: 'sale_app',
                ];

                $this->transactionUtil->updateSellTransaction(
                    $order,
                    $business_id,
                    $input,
                    array_merge($invoice_total, ['final_total' => $final_total]),
                    $user_id,
                    false,
                    false
                );

                $this->transactionUtil->createOrUpdateSellLines(
                    $order->fresh(),
                    $products,
                    $data['location_id'],
                    false,
                    'draft',
                    [],
                    false
                );
            });

            return redirect()
                ->route('sale.orders')
                ->with('status', [
                    'success' => 1,
                    'msg' => 'Sales order updated: '.$order->invoice_no,
                ]);
        } catch (\Throwable $e) {
            \Log::error('Sale portal update failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()->withInput()->with('status', [
                'success' => 0,
                'msg' => 'Could not update order. '.$e->getMessage(),
            ]);
        }
    }

    public function account(Request $request)
    {
        $user = auth()->user();
        $business_id = $request->session()->get('user.business_id');
        $locations = BusinessLocation::forDropdown($business_id, false);
        $locationIds = array_map('strval', array_keys($locations->toArray()));

        $current_location_id = $request->session()->get('user.default_location_id');
        if (empty($current_location_id) || ! in_array((string) $current_location_id, $locationIds, true)) {
            $current_location_id = count($locations) ? array_key_first($locations->toArray()) : null;
            if ($current_location_id) {
                $request->session()->put('user.default_location_id', $current_location_id);
            }
        }

        return view('sale.account', compact('user', 'locations', 'current_location_id'));
    }

    /**
     * Change default business location for Sales App (used on Create order).
     */
    public function updateLocation(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $data = $request->validate([
            'location_id' => 'required|integer',
        ]);

        $locations = BusinessLocation::forDropdown($business_id, false)->toArray();
        $locationId = (int) $data['location_id'];
        if (! isset($locations[$locationId]) && ! isset($locations[(string) $locationId])) {
            return back()->with('status', ['success' => 0, 'msg' => 'Invalid location.']);
        }

        $request->session()->put('user.default_location_id', $locationId);
        $label = $locations[$locationId] ?? $locations[(string) $locationId];

        return back()->with('status', [
            'success' => 1,
            'msg' => 'Location updated: '.$label,
        ]);
    }

    /**
     * Customers menu always visible in app; access checked on open.
     */
    public static function userCanAccessCustomers(): bool
    {
        return true;
    }

    /** View all or own customers (list permission). */
    public static function userCanListCustomers(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->can('customer.view') || $user->can('customer.view_own');
    }

    public static function userCanCreateCustomers(): bool
    {
        return auth()->check() && auth()->user()->can('customer.create');
    }

    public function customers(Request $request)
    {
        $canList = self::userCanListCustomers();
        $canCreate = self::userCanCreateCustomers();

        // No list / create → still open Customers screen with no-permission message
        if (! $canList && ! $canCreate) {
            return view('sale.customers.no_permission');
        }

        // Create-only: form (no list data)
        if (! $canList && $canCreate) {
            return redirect()->route('sale.customers.create');
        }

        $business_id = $request->session()->get('user.business_id');
        $term = trim((string) $request->get('q', ''));

        $query = Contact::where('business_id', $business_id)
            ->onlyCustomers()
            ->where('contact_status', 'active')
            ->select('contacts.id', 'contacts.name', 'contacts.mobile', 'contacts.email', 'contacts.supplier_business_name', 'contacts.contact_id', 'contacts.created_at');

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('contacts.name', 'like', "%{$term}%")
                    ->orWhere('contacts.mobile', 'like', "%{$term}%")
                    ->orWhere('contacts.email', 'like', "%{$term}%")
                    ->orWhere('contacts.supplier_business_name', 'like', "%{$term}%")
                    ->orWhere('contacts.contact_id', 'like', "%{$term}%");
            });
        }

        $customers = $query->orderBy('contacts.name')
            ->distinct()
            ->paginate(20)
            ->appends(['q' => $term]);

        return view('sale.customers.index', compact('customers', 'term', 'canCreate', 'canList'));
    }

    public function createCustomer()
    {
        if (! self::userCanCreateCustomers()) {
            return view('sale.customers.no_permission', ['action' => 'create']);
        }

        $canList = self::userCanListCustomers();

        return view('sale.customers.create', compact('canList'));
    }

    public function storeCustomer(Request $request)
    {
        if (! self::userCanCreateCustomers()) {
            return view('sale.customers.no_permission', ['action' => 'create']);
        }

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'mobile' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:191',
            'address_line_1' => 'nullable|string|max:255',
            'supplier_business_name' => 'nullable|string|max:191',
        ]);

        $business_id = $request->session()->get('user.business_id');
        $user_id = auth()->id();

        try {
            $name = trim($data['name']);
            $parts = preg_split('/\s+/', $name, 2);
            $input = [
                'type' => 'customer',
                'business_id' => $business_id,
                'created_by' => $user_id,
                'name' => $name,
                'first_name' => $parts[0] ?? $name,
                'last_name' => $parts[1] ?? null,
                'mobile' => $data['mobile'] ?? null,
                'email' => $data['email'] ?? null,
                'address_line_1' => $data['address_line_1'] ?? null,
                'supplier_business_name' => $data['supplier_business_name'] ?? null,
                'contact_status' => 'active',
            ];

            $output = $this->contactUtil->createNewContact($input);

            $redirect = self::userCanListCustomers()
                ? route('sale.customers')
                : route('sale.customers.create');

            return redirect()
                ->to($redirect)
                ->with('status', ['success' => 1, 'msg' => 'Customer added: '.$output['data']->name]);
        } catch (\Throwable $e) {
            \Log::error('Sale portal customer create failed: '.$e->getMessage());

            return back()->withInput()->with('status', [
                'success' => 0,
                'msg' => 'Could not add customer. '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Invoices created from sales orders (Sale App display only).
     */
    protected function saleAppInvoiceMap(int $businessId): array
    {
        return Transaction::invoicesBySalesOrderId($businessId);
    }

    /**
     * @return array<int, float>
     */
    protected function saleAppInvoiceTotals(array $invoiceMap): array
    {
        $ids = [];
        foreach ($invoiceMap as $invoices) {
            foreach ($invoices as $inv) {
                $ids[] = (int) $inv->id;
            }
        }
        $ids = array_values(array_unique(array_filter($ids)));
        if ($ids === []) {
            return [];
        }

        return Transaction::whereIn('id', $ids)->pluck('final_total', 'id')->all();
    }

    protected function saleAppDisplayTotal(Transaction $order, array $invoiceMap, array $invoiceTotals): float
    {
        $invoices = $invoiceMap[(int) $order->id] ?? [];
        if ($invoices === []) {
            return (float) $order->final_total;
        }

        $sum = 0.0;
        foreach ($invoices as $inv) {
            $sum += (float) ($invoiceTotals[(int) $inv->id] ?? 0);
        }

        return $sum;
    }

    protected function saleAppSumDisplayTotals($orders, array $invoiceMap, array $invoiceTotals): float
    {
        $sum = 0.0;
        foreach ($orders as $order) {
            $sum += $this->saleAppDisplayTotal($order, $invoiceMap, $invoiceTotals);
        }

        return $sum;
    }

    /**
     * Completed only if this sales order was converted to an invoice.
     */
    protected function saleAppDisplayStatus(Transaction $order, array $invoiceMap): string
    {
        $converted = ! empty($invoiceMap[(int) $order->id]);

        return $converted ? 'invoiced' : 'sale';
    }

    protected function saleAppHasEditPermission(Transaction $order): bool
    {
        if ((int) $order->created_by !== (int) auth()->id()) {
            return false;
        }

        $user = auth()->user();

        return $user && ($user->can('so.update_own') || $user->can('so.update'));
    }

    protected function saleAppCanEdit(Transaction $order, ?array $invoiceMap = null): bool
    {
        if (! $this->saleAppHasEditPermission($order)) {
            return false;
        }

        $map = $invoiceMap ?? $this->saleAppInvoiceMap((int) $order->business_id);

        return empty($map[(int) $order->id]);
    }

    protected function applySaleAppOrderStatus($orders, array $invoiceMap, ?array $invoiceTotals = null): void
    {
        $invoiceTotals = $invoiceTotals ?? $this->saleAppInvoiceTotals($invoiceMap);
        foreach ($orders as $order) {
            $order->sale_status = $this->saleAppDisplayStatus($order, $invoiceMap);
            $order->can_show_edit = $this->saleAppHasEditPermission($order);
            $order->can_edit = $this->saleAppCanEdit($order, $invoiceMap);
            $order->sale_display_total = $this->saleAppDisplayTotal($order, $invoiceMap, $invoiceTotals);
            $invoices = $invoiceMap[(int) $order->id] ?? [];
            $order->converted_invoice_no = ! empty($invoices)
                ? ($invoices[count($invoices) - 1]->invoice_no ?? null)
                : null;
        }
    }

    /**
     * After admin converts (and may change price/qty), Sale App shows the invoice snapshot.
     */
    protected function applySaleAppInvoiceSnapshot(Transaction $order, array $invoiceMap): void
    {
        $this->applySaleAppOrderStatus(collect([$order]), $invoiceMap);

        $stubs = $invoiceMap[(int) $order->id] ?? [];
        if ($stubs === []) {
            return;
        }

        $ids = array_values(array_unique(array_map(fn ($inv) => (int) $inv->id, $stubs)));
        $invoices = Transaction::with(['contact', 'sell_lines.product', 'sell_lines.variations', 'payment_lines'])
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get();
        if ($invoices->isEmpty()) {
            return;
        }

        $lines = $invoices->flatMap(function ($invoice) {
            return $invoice->sell_lines->whereNull('parent_sell_line_id')->values();
        })->values();
        $order->setRelation('sell_lines', $lines);

        $latest = $invoices->last();
        $order->sale_display_total = (float) $invoices->sum('final_total');
        $order->total_before_tax = (float) $invoices->sum('total_before_tax');
        $order->discount_amount = (float) $invoices->sum('discount_amount');
        $order->discount_type = $latest->discount_type;
        $order->tax_amount = (float) $invoices->sum('tax_amount');
        $order->shipping_charges = (float) $invoices->sum('shipping_charges');
        $order->packing_charge = (float) $invoices->sum('packing_charge');
        $order->packing_charge_type = $latest->packing_charge_type;
        $order->round_off_amount = (float) $invoices->sum('round_off_amount');
        $order->additional_notes = $latest->additional_notes;
        $order->shipping_address = $latest->shipping_address;
        $order->shipping_details = $latest->shipping_details;
        $order->shipping_status = $latest->shipping_status;
        $order->converted_invoice_no = $latest->invoice_no;
        for ($i = 1; $i <= 4; $i++) {
            $order->{'additional_expense_key_'.$i} = $latest->{'additional_expense_key_'.$i};
            $order->{'additional_expense_value_'.$i} = (float) $invoices->sum('additional_expense_value_'.$i);
        }
        if ($latest->relationLoaded('contact') && $latest->contact) {
            $order->setRelation('contact', $latest->contact);
        }
        $payments = $invoices->flatMap(function ($invoice) {
            return $invoice->payment_lines ?? collect();
        })->values();
        $order->setRelation('payment_lines', $payments);
    }

    /**
     * Subtotal, discount, tax, shipping, extras, total (Sale App display).
     */
    protected function saleAppAmountBreakdown(Transaction $order): array
    {
        $subtotal = (float) ($order->total_before_tax ?? 0);
        if ($subtotal <= 0) {
            $subtotal = 0.0;
            foreach ($order->sell_lines ?? [] as $line) {
                if (! empty($line->parent_sell_line_id)) {
                    continue;
                }
                $subtotal += (float) $line->quantity * (float) $line->unit_price_inc_tax;
            }
        }

        $discountRaw = (float) ($order->discount_amount ?? 0);
        $discountType = $order->discount_type ?: 'fixed';
        $discountMoney = $discountType === 'percentage'
            ? round($subtotal * $discountRaw / 100, 2)
            : $discountRaw;

        $packRaw = (float) ($order->packing_charge ?? 0);
        $packType = $order->packing_charge_type;
        $packMoney = $packType === 'percent'
            ? round($subtotal * $packRaw / 100, 2)
            : $packRaw;

        $extras = [];
        for ($i = 1; $i <= 4; $i++) {
            $key = trim((string) ($order->{'additional_expense_key_'.$i} ?? ''));
            $val = (float) ($order->{'additional_expense_value_'.$i} ?? 0);
            if ($key !== '' || $val != 0.0) {
                $extras[] = [
                    'label' => $key !== '' ? $key : ('Extra charge '.$i),
                    'amount' => $val,
                ];
            }
        }

        $total = (float) ($order->sale_display_total ?? $order->final_total ?? 0);
        $paid = 0.0;
        foreach ($order->payment_lines ?? [] as $pay) {
            $amt = (float) ($pay->amount ?? 0);
            $paid += ! empty($pay->is_return) ? -$amt : $amt;
        }

        return [
            'subtotal' => $subtotal,
            'discount' => $discountMoney,
            'discount_label' => $discountType === 'percentage' && $discountRaw > 0
                ? 'Discount ('.rtrim(rtrim(number_format($discountRaw, 2), '0'), '.').'%)'
                : 'Discount',
            'tax' => (float) ($order->tax_amount ?? 0),
            'shipping' => (float) ($order->shipping_charges ?? 0),
            'packing' => $packMoney,
            'packing_label' => $packType === 'percent' && $packRaw > 0
                ? 'Packing ('.rtrim(rtrim(number_format($packRaw, 2), '0'), '.').'%)'
                : 'Packing charge',
            'round_off' => (float) ($order->round_off_amount ?? 0),
            'extras' => $extras,
            'total' => $total,
            'paid' => $paid,
            'due' => round($total - $paid, 2),
            'show_paid' => ($order->sale_status ?? '') === 'invoiced',
        ];
    }

    protected function saleAppPrintTransaction(Transaction $order, array $invoiceMap): Transaction
    {
        $stubs = $invoiceMap[(int) $order->id] ?? [];
        if ($stubs === []) {
            return $order;
        }

        $invoiceId = (int) ($stubs[count($stubs) - 1]->id ?? 0);
        if ($invoiceId < 1) {
            return $order;
        }

        $invoice = Transaction::find($invoiceId);

        return $invoice ?: $order;
    }

    /**
     * Product image for Sale App grids (real image or placeholder flag).
     *
     * @return array{url: string, has_image: bool}
     */
    protected function saleProductImage(?string $image): array
    {
        $default = asset('/img/default.png');
        if (empty($image)) {
            return ['url' => $default, 'has_image' => false];
        }

        $path = public_path('uploads/'.config('constants.product_img_path').'/'.$image);
        if (! is_file($path)) {
            return ['url' => $default, 'has_image' => false];
        }

        return [
            'url' => asset('/uploads/'.config('constants.product_img_path').'/'.rawurlencode($image)),
            'has_image' => true,
        ];
    }
}
