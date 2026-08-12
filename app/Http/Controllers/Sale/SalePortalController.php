<?php

namespace App\Http\Controllers\Sale;

use App\BusinessLocation;
use App\Contact;
use App\Http\Controllers\Controller;
use App\Product;
use App\Transaction;
use App\Utils\BusinessUtil;
use App\Utils\ContactUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Mobile sales-rep portal: list own orders, create order for customer.
 * Writes same transactions as Add Sale (is_direct_sale=1) for admin management.
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

        $base = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('created_by', $user_id)
            ->where('status', 'final');

        $todayStart = Carbon::now()->startOfDay();
        $monthStart = Carbon::now()->startOfMonth();

        $stats = [
            'total_orders' => (clone $base)->count(),
            'today_orders' => (clone $base)->where('transaction_date', '>=', $todayStart)->count(),
            'today_total' => (float) (clone $base)->where('transaction_date', '>=', $todayStart)->sum('final_total'),
            'month_orders' => (clone $base)->where('transaction_date', '>=', $monthStart)->count(),
            'month_total' => (float) (clone $base)->where('transaction_date', '>=', $monthStart)->sum('final_total'),
            'paid_orders' => (clone $base)->where('payment_status', 'paid')->count(),
            'due_orders' => (clone $base)->whereIn('payment_status', ['due', 'partial'])->count(),
        ];

        $recent = (clone $base)
            ->with(['contact:id,name,mobile,supplier_business_name'])
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('sale.dashboard', compact('stats', 'recent'));
    }

    public function orders(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user_id = auth()->id();

        $orders = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('created_by', $user_id)
            ->with(['contact:id,name,mobile,supplier_business_name'])
            ->orderByDesc('id')
            ->paginate(20);

        return view('sale.orders.index', compact('orders'));
    }

    public function show(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');

        $order = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('created_by', auth()->id())
            ->with(['contact', 'sell_lines.product', 'sell_lines.variations', 'payment_lines'])
            ->findOrFail($id);

        return view('sale.orders.show', compact('order'));
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

        return view('sale.orders.create', compact('locations', 'default_location'));
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

        $rows = $query->orderBy('name')->limit(25)->get(['id', 'name', 'mobile', 'supplier_business_name']);

        return response()->json($rows->map(function ($c) {
            return [
                'id' => $c->id,
                'text' => trim(($c->supplier_business_name ? $c->supplier_business_name.' — ' : '').$c->name.
                    ($c->mobile ? ' ('.$c->mobile.')' : '')),
                'name' => $c->name,
                'mobile' => $c->mobile,
            ];
        }));
    }

    public function searchProducts(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $location_id = (int) $request->get('location_id');
        $term = trim((string) $request->get('q', ''));

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

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('p.name', 'like', "%{$term}%")
                    ->orWhere('p.sku', 'like', "%{$term}%")
                    ->orWhere('variations.sub_sku', 'like', "%{$term}%")
                    ->orWhere('variations.name', 'like', "%{$term}%");
            });
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
            DB::raw('COALESCE(vld.qty_available, 0) as stock')
        )
            ->orderBy('p.name')
            ->limit(30)
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
            ];
        }));
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

                $cg = $this->contactUtil->getCustomerGroup($business_id, $contact->id);

                $input = [
                    'type' => 'sell',
                    'status' => 'final',
                    'contact_id' => $contact->id,
                    'customer_group_id' => (empty($cg) || empty($cg->id)) ? null : $cg->id,
                    'transaction_date' => Carbon::now(),
                    'location_id' => $data['location_id'],
                    'final_total' => $invoice_total['final_total'],
                    'discount_type' => 'fixed',
                    'discount_amount' => 0,
                    'tax_rate_id' => null,
                    'sale_note' => $data['sale_note'] ?? null,
                    'is_direct_sale' => 1,
                    'source' => 'sale_app',
                    'commission_agent' => null,
                    'exchange_rate' => 1,
                    'products' => $products,
                ];

                $cmsn = $request->session()->get('business.sales_cmsn_agnt');
                if ($cmsn === 'logged_in_user') {
                    $input['commission_agent'] = $user_id;
                }

                $transaction = $this->transactionUtil->createSellTransaction(
                    $business_id,
                    $input,
                    $invoice_total,
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

                // Stock decrease
                foreach ($products as $product) {
                    if (! empty($product['enable_stock'])) {
                        $this->productUtil->decreaseProductQuantity(
                            $product['product_id'],
                            $product['variation_id'],
                            $data['location_id'],
                            $product['quantity']
                        );
                    }
                }

                // Map FIFO / average cost
                $business_details = $this->businessUtil->getDetails($business_id);
                $business = [
                    'id' => $business_id,
                    'accounting_method' => $request->session()->get('business.accounting_method'),
                    'location_id' => $data['location_id'],
                    'pos_settings' => ! empty($business_details->pos_settings)
                        ? json_decode($business_details->pos_settings, true)
                        : [],
                ];
                $this->transactionUtil->mapPurchaseSell($business, $transaction->sell_lines, 'purchase');

                // Payment: cash full or leave due
                if ($data['payment_type'] === 'cash') {
                    $this->transactionUtil->createOrUpdatePaymentLines($transaction, [
                        [
                            'amount' => $invoice_total['final_total'],
                            'method' => 'cash',
                            'paid_on' => Carbon::now()->toDateTimeString(),
                            'note' => 'Sale app payment',
                        ],
                    ], $business_id, $user_id, false);
                }

                $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

                return $transaction;
            });

            return redirect()
                ->route('sale.orders.show', $transaction->id)
                ->with('status', ['success' => 1, 'msg' => 'Order created: '.$transaction->invoice_no]);
        } catch (\Throwable $e) {
            \Log::error('Sale portal create failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()->withInput()->with('status', [
                'success' => 0,
                'msg' => 'Could not create order. '.$e->getMessage(),
            ]);
        }
    }

    public function account()
    {
        $user = auth()->user();

        return view('sale.account', compact('user'));
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
}
