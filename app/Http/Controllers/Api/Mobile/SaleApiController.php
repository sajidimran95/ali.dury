<?php

namespace App\Http\Controllers\Api\Mobile;

use App\BusinessLocation;
use App\Contact;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Api\Mobile\Concerns\MobileApiResponses;
use App\Http\Controllers\Sale\SalePortalController;
use App\Transaction;
use App\Utils\SalesOrderPermission;
use App\Variation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Native Sales Representative app — JSON version of Sale\SalePortalController.
 *
 * Every endpoint runs the same PWA controller action (same queries, permissions,
 * totals, stock & invoice rules) and converts its view data / redirect result to JSON.
 */
class SaleApiController extends SalePortalController
{
    use MobileApiResponses;

    // ============================================================ Dashboard

    public function apiDashboard(Request $request)
    {
        $d = $this->viewData($this->home($request));
        $loc = $d['location'] ?? null;

        return $this->ok([
            'user_name' => $d['userName'],
            'role_name' => $d['role_name'],
            'current_location' => $loc ? $this->locationPayload($loc) : null,
            'current_location_id' => $d['current_location_id'] ? (int) $d['current_location_id'] : null,
            'current_location_name' => $d['current_location_name'],
            'locations' => $this->dropdownList($d['locations']),
            'stats' => array_map(fn ($v) => is_float($v) ? round($v, 2) : $v, $d['stats']),
            'recent_orders' => collect($d['recent'])->map(fn ($o) => $this->orderRow($o))->values(),
            'new_products' => collect($d['newProducts'])->values(),
            'top_products' => collect($d['topProducts'])->map(fn ($p) => [
                'id' => (int) $p->product_id,
                'variation_id' => $p->variation_id ? (int) $p->variation_id : null,
                'name' => $p->name,
                'sku' => $p->sku,
                'price' => (float) $p->price,
                'qty_sold' => (float) $p->qty_sold,
                'sales_amount' => round((float) $p->sales_amount, 2),
                'image' => $p->image_url,
                'has_image' => (bool) $p->has_image,
                'in_stock' => (bool) $p->in_stock,
            ])->values(),
        ]);
    }

    // ============================================================ Account / location

    public function apiAccount(Request $request)
    {
        $d = $this->viewData($this->account($request));
        $u = $d['user'];

        return $this->ok([
            'user' => [
                'id' => $u->id,
                'username' => $u->username,
                'first_name' => $u->first_name,
                'last_name' => $u->last_name,
                'email' => $u->email,
                'contact_number' => $u->contact_number,
            ],
            'current_location_id' => $d['current_location_id'] ? (int) $d['current_location_id'] : null,
            'locations' => $this->dropdownList($d['locations']),
        ]);
    }

    public function apiLocations(Request $request)
    {
        return $this->apiAccountLocations($request);
    }

    public function apiUpdateLocation(Request $request)
    {
        $response = $this->updateLocation($request);
        $outcome = $this->redirectOutcome($request, $response);
        if (! $outcome['success']) {
            return $this->fail($outcome['message'] ?: 'Invalid location.', 422, $outcome['errors']);
        }

        return $this->apiAccountLocations($request, $outcome['message']);
    }

    protected function apiAccountLocations(Request $request, ?string $message = null)
    {
        $d = $this->viewData($this->account($request));
        $currentId = $d['current_location_id'] ? (int) $d['current_location_id'] : null;
        $loc = $currentId ? BusinessLocation::find($currentId) : null;

        return $this->ok([
            'current_location_id' => $currentId,
            'current_location' => $loc ? $this->locationPayload($loc) : null,
            'locations' => $this->dropdownList($d['locations']),
        ], $message);
    }

    // ============================================================ Orders: report + list

    /**
     * PWA /sale/orders. Without "section" it returns the report (goals / totals).
     * section = orders | invoices | payments | visits | day | quotes | credit → also returns the list.
     */
    public function apiOrders(Request $request)
    {
        $d = $this->viewData($this->orders($request));

        $data = [
            'section' => $d['section'] !== '' ? $d['section'] : null,
            'list_title' => $d['listTitle'],
            'period' => $d['period'],
            'period_label' => $d['periodLabel'],
            'month' => $d['monthKey'],
            'day' => $d['dayKey'],
            'q' => $d['q'] !== '' ? $d['q'] : null,
            'status' => $d['status'] !== '' ? $d['status'] : null,
            'contact_id' => $d['contactFilter'] ?: null,
            'transactions' => $this->roundAll($d['tx']),
            'goals' => $this->roundAll($d['goals']),
            'report_stats' => $this->roundAll($d['reportStats']),
            'day_report' => $this->roundAll($d['dayReport']),
        ];

        if ($d['section'] === 'visits') {
            $page = $d['visitCustomers'];
            $data['visit_customers'] = collect($page->items())->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'business_name' => $c->supplier_business_name,
                'display_name' => trim((string) ($c->supplier_business_name ?: $c->name)),
                'mobile' => $c->mobile,
                'visit_orders' => (int) $c->visit_orders,
                'visit_total' => round((float) $c->visit_total, 2),
            ])->values();
            $data['orders'] = [];

            return $this->ok($data, null, 200, ['meta' => $this->pageMeta($page)]);
        }

        $page = $d['orders'];
        $section = $d['section'];
        $data['orders'] = collect($page->items())->map(function ($o) use ($section) {
            $row = $this->orderRow($o);
            // Invoices section shows the converted invoice number first (same as PWA)
            $row['display_no'] = $section === 'invoices' && ! empty($o->converted_invoice_no) ? $o->converted_invoice_no : $o->invoice_no;

            return $row;
        })->values();

        return $this->ok($data, null, 200, ['meta' => $this->pageMeta($page)]);
    }

    public function apiShowOrder(Request $request, $id)
    {
        $d = $this->viewData($this->show($request, $id));

        return $this->ok($this->orderDetail($d['order'], $d['amounts']));
    }

    /** Sales order / converted invoice: text/html (default), ?format=json, ?format=pdf. */
    public function apiOrderInvoice(Request $request, $id)
    {
        $response = $this->downloadInvoice($request, $id);
        if (! $this->isView($response, 'sale.orders.invoice')) {
            $outcome = $this->redirectOutcome($request, $response);

            return $this->fail($outcome['message'] ?: 'Could not open invoice.', 422);
        }
        $d = $this->viewData($response);

        return $this->invoiceResponse($request, (string) $d['html'], (string) $d['title']);
    }

    // ============================================================ Orders: create / edit

    /**
     * Data for the order screen for one customer (PWA /sale/orders/create?contact_id=&saved=).
     */
    public function apiCreateData(Request $request)
    {
        $request->validate(['contact_id' => 'required|integer', 'saved' => 'nullable|string']);
        $response = $this->create($request);
        if (! $this->isView($response, 'sale.orders.create')) {
            return $this->fail('Customer not found.', 404);
        }
        $d = $this->viewData($response);

        return $this->ok(array_merge($this->orderFormMeta(), [
            'default_location_id' => $d['default_location'] ? (int) $d['default_location'] : null,
            'locations' => $this->dropdownList($d['locations']),
            'customer' => $this->formCustomer($d['default_customer']),
            'saved_draft' => $d['order_draft'],
        ]));
    }

    public function apiStore(Request $request)
    {
        $response = $this->store($request);
        $outcome = $this->redirectOutcome($request, $response);
        if (! $outcome['success']) {
            return $this->fail($outcome['message'] ?: 'Could not create order.', 422, $outcome['errors']);
        }

        $order = $this->findCreatedOrder($request, (string) $outcome['message']);
        if (! $order) {
            return $this->ok(null, $outcome['message'], 201);
        }

        $d = $this->viewData($this->show($request, $order->id));

        return $this->ok($this->orderDetail($d['order'], $d['amounts']), $outcome['message'], 201);
    }

    public function apiEditData(Request $request, $id)
    {
        $order = $this->findOwnOrder($id);
        if (! $this->saleAppCanEdit($order)) {
            return $this->fail($this->cannotEditMessage($order), 403);
        }

        $d = $this->viewData($this->edit($request, $id));
        $o = $d['edit_order'];
        $this->applySaleAppOrderStatus(collect([$o]), $this->saleAppInvoiceMap((int) $o->business_id));

        return $this->ok(array_merge($this->orderFormMeta(), [
            'order' => $this->orderRow($o),
            'default_location_id' => $d['default_location'] ? (int) $d['default_location'] : null,
            'locations' => $this->dropdownList($d['locations']),
            'customer' => $this->formCustomer($d['default_customer']),
            'payment_type' => ($o->prefer_payment_method ?? '') === 'credit_sale' ? 'due' : 'cash',
            'shipping_method' => $o->shipping_method ?: 'Delivery',
            'shipping_details' => $o->shipping_details,
            'shipping_address' => $o->shipping_address,
            'shipping_status' => $o->shipping_status,
            'sale_note' => $o->additional_notes,
            'lines' => collect($d['edit_lines'])->values(),
        ]));
    }

    public function apiUpdate(Request $request, $id)
    {
        $order = $this->findOwnOrder($id);
        if (! $this->saleAppCanEdit($order)) {
            return $this->fail($this->cannotEditMessage($order), 403);
        }

        $response = $this->update($request, $id);
        $outcome = $this->redirectOutcome($request, $response);
        if (! $outcome['success']) {
            return $this->fail($outcome['message'] ?: 'Could not update order.', 422, $outcome['errors']);
        }

        // updateSellTransaction() does not carry shipping_method — keep the fulfillment the app sent.
        $request->validate(['shipping_method' => 'nullable|string|max:100']);
        if ($request->filled('shipping_method')) {
            Transaction::where('id', $order->id)->update(['shipping_method' => $request->input('shipping_method')]);
        }

        $d = $this->viewData($this->show($request, $id));

        return $this->ok($this->orderDetail($d['order'], $d['amounts']), $outcome['message']);
    }

    public function apiDestroy(Request $request, $id)
    {
        $response = $this->destroy($request, $id);
        $outcome = $this->redirectOutcome($request, $response);
        if (! $outcome['success']) {
            return $this->fail($outcome['message'] ?: 'Could not delete order.', 422);
        }

        return $this->ok(null, $outcome['message']);
    }

    // ============================================================ Saved carts / drafts

    public function apiSavedOrders(Request $request)
    {
        $d = $this->viewData($this->savedOrders());

        return $this->ok(['saved_orders' => array_values($d['saved_orders'])]);
    }

    public function apiDestroySavedOrder(Request $request, $id)
    {
        $outcome = $this->redirectOutcome($request, $this->destroySavedOrder($id));

        return $this->ok(null, $outcome['message'] ?: 'Saved order removed.');
    }

    public function apiSaveDraft(Request $request)
    {
        $result = $this->saveOrderDraft($request)->getData(true);

        return $this->ok(['id' => $result['id'] ?? null], 'Cart saved.');
    }

    public function apiClearDraft(Request $request)
    {
        $this->clearOrderDraft($request);

        return $this->ok(null, 'Cart cleared.');
    }

    public function apiSync(Request $request)
    {
        $result = $this->syncApp($request)->getData(true);

        return $this->ok(['synced_at' => $result['synced_at'] ?? now()->toIso8601String()], 'Synced.');
    }

    // ============================================================ Catalog

    /** Catalog screen meta: locations, default location and category tree. */
    public function apiCatalog(Request $request)
    {
        $d = $this->viewData($this->products($request));

        return $this->ok([
            'default_location_id' => $d['default_location'] ? (int) $d['default_location'] : null,
            'locations' => $this->dropdownList($d['locations']),
            'categories' => $d['categoriesJson'],
        ]);
    }

    /** q, location_id (defaults to current location), category_id, sub_category_id, variation_id, limit (max 100). */
    public function apiSearchProducts(Request $request)
    {
        $this->defaultLocationParam($request);

        return $this->ok($this->searchProducts($request)->getData(true));
    }

    public function apiCategories(Request $request)
    {
        return $this->ok($this->categoriesTree($request)->getData(true));
    }

    public function apiLastPurchases(Request $request)
    {
        $request->validate(['contact_id' => 'required|integer']);
        $this->defaultLocationParam($request);

        return $this->ok($this->lastPurchases($request)->getData(true));
    }

    public function apiProductHistory(Request $request)
    {
        $request->validate(['contact_id' => 'required|integer', 'variation_id' => 'required|integer']);

        return $this->ok($this->productOrderHistory($request)->getData(true));
    }

    // ============================================================ Customers

    public function apiCustomers(Request $request)
    {
        $response = $this->customers($request);
        $flags = [
            'can_list' => self::userCanListCustomers(),
            'can_create' => self::userCanCreateCustomers(),
        ];

        if ($this->isView($response, 'sale.customers.no_permission')) {
            return $this->fail('You do not have permission to view or add customers.', 403);
        }
        if (! $this->isView($response, 'sale.customers.index')) {
            // create-only users (PWA redirects to the add-customer form)
            return $this->ok(array_merge($flags, ['customers' => [], 'zero_sale_reasons' => $this->zeroSaleReasonList()]));
        }

        $d = $this->viewData($response);
        $page = $d['customers'];

        return $this->ok(array_merge($flags, [
            'q' => $d['term'] !== '' ? $d['term'] : null,
            'customers' => collect($page->items())->map(fn ($c) => [
                'id' => $c->id,
                'contact_id' => $c->contact_id,
                'name' => $c->name,
                'business_name' => $c->supplier_business_name,
                'display_name' => trim((string) ($c->supplier_business_name ?: $c->name)),
                'mobile' => $c->mobile,
                'email' => $c->email,
                'address_line_1' => $c->address_line_1,
                'city' => $c->city,
                'shipping_address' => $c->shipping_address,
                'credit_limit' => $c->credit_limit !== null ? (float) $c->credit_limit : null,
                'last_order_total' => round((float) $c->last_order_total, 2),
                'created_at' => $this->dt($c->created_at),
            ])->values(),
            'zero_sale_reasons' => $this->zeroSaleReasonList(),
        ]), null, 200, ['meta' => $this->pageMeta($page)]);
    }

    /** Quick customer picker (max 40, active customers). */
    public function apiSearchCustomers(Request $request)
    {
        return $this->ok($this->searchCustomers($request)->getData(true));
    }

    public function apiCustomerCreateData(Request $request)
    {
        $response = $this->createCustomer();
        if ($this->isView($response, 'sale.customers.no_permission')) {
            return $this->fail('You do not have permission to add customers.', 403);
        }
        $d = $this->viewData($response);

        return $this->ok([
            'can_list' => (bool) $d['canList'],
            'customer_groups' => $this->dropdownList($d['customer_groups']),
            'contact_types' => ['individual', 'business'],
        ]);
    }

    public function apiStoreCustomer(Request $request)
    {
        // The PWA form always posts these keys (empty → null); the native app may omit them.
        $this->mergeMissing($request, ['customer_group_id', 'contact_id', 'credit_limit']);
        if ((int) $request->input('customer_group_id') === 0) {
            $request->merge(['customer_group_id' => null]);
        }

        $response = $this->storeCustomer($request);
        if ($this->isView($response, 'sale.customers.no_permission')) {
            return $this->fail('You do not have permission to add customers.', 403);
        }
        $outcome = $this->redirectOutcome($request, $response);
        if (! $outcome['success']) {
            return $this->fail($outcome['message'] ?: 'Could not add customer.', 422, $outcome['errors']);
        }

        $contact = Contact::where('business_id', $request->session()->get('user.business_id'))
            ->where('created_by', auth()->id())
            ->orderByDesc('id')
            ->first();

        return $this->ok($contact ? $this->customerDetail($contact) : null, $outcome['message'], 201);
    }

    /** Customer details + aging + open invoices + history (PWA /sale/customers/{id}). */
    public function apiShowCustomer(Request $request, $id)
    {
        $d = $this->viewData($this->showCustomer($request, $id));

        return $this->ok(array_merge($this->bundlePayload($d), [
            'zero_sale_reasons' => $this->zeroSaleReasonList(),
        ]));
    }

    public function apiCustomerOpenInvoices(Request $request, $id)
    {
        $d = $this->viewData($this->customerOpenInvoices($request, $id));

        return $this->ok([
            'customer' => $this->customerDetail($d['customer']),
            'q' => $d['q'] !== '' ? $d['q'] : null,
            'aging' => $this->roundAll($d['aging']),
            'open_balance' => (float) $d['openBalance'],
            'open_invoices' => collect($d['openInvoices'])->values(),
        ]);
    }

    public function apiCustomerHistory(Request $request, $id)
    {
        $d = $this->viewData($this->customerHistory($request, $id));

        return $this->ok([
            'customer' => $this->customerDetail($d['customer']),
            'q' => $d['q'] !== '' ? $d['q'] : null,
            'history' => $this->historyRows($d['history']),
        ]);
    }

    /** Data for "Receive payment": active payment methods for current location + open invoices. */
    public function apiCustomerPaymentData(Request $request, $id)
    {
        $d = $this->viewData($this->customerPayment($request, $id));

        return $this->ok([
            'customer' => $this->customerDetail($d['customer']),
            'payment_methods' => collect($d['paymentTypes'])->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
            'preselect_invoice_id' => $d['preselect'] ?: null,
            'aging' => $this->roundAll($d['aging']),
            'open_balance' => (float) $d['openBalance'],
            'open_invoices' => collect($d['openInvoices'])->values(),
        ]);
    }

    public function apiStoreCustomerPayment(Request $request, $id)
    {
        $this->mergeMissing($request, ['paid_on', 'note', 'signature', 'pay_mode']);
        $response = $this->storeCustomerPayment($request, $id);
        $outcome = $this->redirectOutcome($request, $response);
        if (! $outcome['success']) {
            return $this->fail($outcome['message'] ?: 'Could not save payment.', 422, $outcome['errors']);
        }

        $d = $this->saleAppCustomerBundle($request, $id, true);

        return $this->ok([
            'open_balance' => (float) $d['openBalance'],
            'aging' => $this->roundAll($d['aging']),
            'open_invoices' => collect($d['openInvoices'])->values(),
        ], $outcome['message']);
    }

    public function apiCustomerZeroSale(Request $request, $id)
    {
        $outcome = $this->redirectOutcome($request, $this->storeCustomerZeroSale($request, $id));

        return $this->ok(null, $outcome['message'] ?: 'Zero sale saved.', 201);
    }

    public function apiZeroSaleReasons(Request $request)
    {
        return $this->ok($this->zeroSaleReasonList());
    }

    /** Print a customer's invoice / sales order (any rep). text/html | ?format=json | ?format=pdf */
    public function apiCustomerInvoicePrint(Request $request, $id, $invoiceId)
    {
        $response = $this->printCustomerInvoice($request, $id, $invoiceId);
        if (! $this->isView($response, 'sale.orders.invoice')) {
            $outcome = $this->redirectOutcome($request, $response);

            return $this->fail($outcome['message'] ?: 'Could not open invoice.', 422);
        }
        $d = $this->viewData($response);

        return $this->invoiceResponse($request, (string) $d['html'], (string) $d['title']);
    }

    // ============================================================ Expenses

    public function apiExpenses(Request $request)
    {
        $user = auth()->user();
        $canList = $user->can('view_own_expense') || $user->can('all_expense.access');
        $canAdd = $user->can('expense.add');
        if (! $canList) {
            if ($canAdd) {
                return $this->ok(['can_list' => false, 'can_add' => true, 'expenses' => []]);
            }

            return $this->fail('Unauthorized action.', 403);
        }

        $d = $this->viewData($this->expenses($request));
        $page = $d['expenses'];

        return $this->ok([
            'can_list' => true,
            'can_add' => (bool) $d['canAdd'],
            'expenses' => collect($page->items())->map(fn ($e) => [
                'id' => $e->id,
                'type' => $e->type,
                'ref_no' => $e->ref_no,
                'transaction_date' => $this->dt($e->transaction_date),
                'final_total' => (float) $e->final_total,
                'payment_status' => $e->payment_status,
                'additional_notes' => $e->additional_notes,
                'category' => $e->expense_category ? ['id' => $e->expense_category->id, 'name' => $e->expense_category->name] : null,
                'location' => $e->location ? ['id' => $e->location->id, 'name' => $e->location->name] : null,
                'contact' => $e->contact ? [
                    'id' => $e->contact->id,
                    'name' => $e->contact->name,
                    'business_name' => $e->contact->supplier_business_name,
                ] : null,
                'document' => $e->document ? asset('uploads/documents/'.$e->document) : null,
            ])->values(),
        ], null, 200, ['meta' => $this->pageMeta($page)]);
    }

    public function apiExpenseCreateData(Request $request)
    {
        $d = $this->viewData($this->createExpense($request));

        return $this->ok([
            'current_location_id' => $d['current_location_id'] ? (int) $d['current_location_id'] : null,
            'locations' => $this->dropdownList($d['locations']),
            'categories' => $this->dropdownList($d['categories']),
            'sub_categories' => collect($d['subCategories'])->map(fn ($rows, $parentId) => [
                'parent_id' => (int) $parentId,
                'items' => collect($rows)->values(),
            ])->values(),
            'payment_methods' => collect($d['payment_types'])->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
            'payment_methods_by_location' => collect($d['payment_types_by_location'])->map(fn ($types, $locId) => [
                'location_id' => (int) $locId,
                'methods' => collect($types)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
            ])->values(),
            'document_max_kb' => (int) (config('constants.document_size_limit') / 1000),
        ]);
    }

    public function apiStoreExpense(Request $request)
    {
        $response = $this->storeExpense($request);
        $outcome = $this->redirectOutcome($request, $response);
        if (! $outcome['success']) {
            return $this->fail($outcome['message'] ?: 'Could not add expense.', 422, $outcome['errors']);
        }

        $expense = Transaction::where('business_id', $request->session()->get('user.business_id'))
            ->where('type', 'expense')
            ->where('created_by', auth()->id())
            ->orderByDesc('id')
            ->first();

        return $this->ok($expense ? [
            'id' => $expense->id,
            'ref_no' => $expense->ref_no,
            'transaction_date' => $this->dt($expense->transaction_date),
            'final_total' => (float) $expense->final_total,
            'location_id' => $expense->location_id,
            'payment_status' => $expense->payment_status,
        ] : null, $outcome['message'], 201);
    }

    // ============================================================ Delivery (own orders route)

    public function apiDelivery(Request $request)
    {
        $d = $this->viewData($this->delivery($request));
        $routes = collect($d['routes'])->map(fn ($orders, $label) => [
            'label' => $label,
            'count' => $orders->count(),
            'total' => round((float) $orders->sum('final_total'), 2),
            'orders' => $orders->values()->map(fn ($o, $i) => [
                'stop_no' => $i + 1,
                'id' => $o->id,
                'invoice_no' => $o->invoice_no,
                'transaction_date' => $this->dt($o->transaction_date),
                'final_total' => (float) $o->final_total,
                'shipping_status' => $o->shipping_status,
                'shipping_address' => $o->shipping_address,
                'customer' => $o->contact ? [
                    'id' => $o->contact->id,
                    'name' => $o->contact->name,
                    'business_name' => $o->contact->supplier_business_name,
                    'mobile' => $o->contact->mobile,
                    'address_line_1' => $o->contact->address_line_1,
                    'city' => $o->contact->city,
                    'state' => $o->contact->state,
                    'zip_code' => $o->contact->zip_code,
                ] : null,
            ])->values(),
        ])->values();

        return $this->ok([
            'start_date' => $d['start'],
            'end_date' => $d['end'],
            'q' => $d['q'] !== '' ? $d['q'] : null,
            'origin_name' => $d['originName'],
            'origin_city' => $d['originCity'],
            'routes' => $routes,
        ]);
    }

    // ============================================================ Timesheet (POS time clock)

    public function apiTimesheet(Request $request)
    {
        $d = $this->viewData($this->timesheet($request));

        return $this->ok([
            'status' => $this->attendance()->status()->getData(true),
            'entries' => collect($d['entries'])->map(fn ($row) => [
                'id' => $row->id,
                'status' => $row->status,
                'clock_in_at' => optional($row->clock_in_at)->toIso8601String(),
                'clock_out_at' => optional($row->clock_out_at)->toIso8601String(),
                'worked_seconds' => (int) $row->workedSeconds(),
                'break_seconds' => (int) $row->totalBreakSeconds(),
                'notes' => $row->notes,
                'breaks' => $row->breaks->map(fn ($b) => [
                    'id' => $b->id,
                    'break_start_at' => optional($b->break_start_at)->toIso8601String(),
                    'break_end_at' => optional($b->break_end_at)->toIso8601String(),
                    'reason' => $b->reason,
                ])->values(),
            ])->values(),
        ]);
    }

    public function apiClockStatus(Request $request)
    {
        return $this->ok($this->attendance()->status()->getData(true));
    }

    public function apiClockIn(Request $request)
    {
        return $this->attendanceResult($this->attendance()->clockIn($request));
    }

    public function apiStartBreak(Request $request)
    {
        return $this->attendanceResult($this->attendance()->startBreak($request));
    }

    public function apiEndBreak(Request $request)
    {
        return $this->attendanceResult($this->attendance()->endBreak($request));
    }

    public function apiClockOut(Request $request)
    {
        return $this->attendanceResult($this->attendance()->clockOut($request));
    }

    // ============================================================ Stock transfers

    public function apiTransfers(Request $request)
    {
        $d = $this->viewData($this->stockTransfers($request));
        $page = $d['transfers'];

        return $this->ok([
            'transfers' => collect($page->items())->map(fn ($t) => $this->transferRow($t))->values(),
        ], null, 200, ['meta' => $this->pageMeta($page)]);
    }

    public function apiShowTransfer(Request $request, $id)
    {
        $d = $this->viewData($this->showStockTransfer($request, $id));
        $t = $d['transfer'];
        $t->load(['sell_lines.product:id,name,sku', 'sell_lines.variations:id,name,sub_sku', 'sell_lines.sub_unit']);

        return $this->ok(array_merge($this->transferRow($t), [
            'shipping_charges' => (float) $t->shipping_charges,
            'lines' => $t->sell_lines->map(function ($line) {
                $mult = ($line->sub_unit_id && $line->sub_unit) ? (float) ($line->sub_unit->base_unit_multiplier ?: 1) : 1.0;

                return [
                    'id' => $line->id,
                    'product_id' => $line->product_id,
                    'variation_id' => $line->variation_id,
                    'product_name' => optional($line->product)->name,
                    'variation_name' => optional($line->variations)->name !== 'DUMMY' ? optional($line->variations)->name : null,
                    'sku' => optional($line->variations)->sub_sku ?: optional($line->product)->sku,
                    'quantity' => (float) $line->quantity,
                    'unit_name' => $line->sub_unit ? $line->sub_unit->actual_name : null,
                    'quantity_in_unit' => $mult > 0 ? (float) $line->quantity / $mult : (float) $line->quantity,
                    'unit_price' => (float) $line->unit_price_inc_tax,
                    'line_total' => round((float) $line->quantity * (float) $line->unit_price_inc_tax, 2),
                ];
            })->values(),
        ]));
    }

    public function apiTransferCreateData(Request $request)
    {
        $d = $this->viewData($this->createStockTransfer($request));

        return $this->ok([
            'can_create' => auth()->user()->can('stock_transfer.create'),
            'locations' => $this->dropdownList($d['business_locations']),
            'statuses' => collect($d['statuses'])->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
        ]);
    }

    /**
     * Products available at the source location with default purchase price (transfer price).
     * Params: location_id (required), q, limit (max 50).
     */
    public function apiTransferProducts(Request $request)
    {
        $request->validate(['location_id' => 'required|integer', 'q' => 'nullable|string|max:191']);
        $business_id = (int) $request->session()->get('user.business_id');
        $location_id = (int) $request->input('location_id');
        $term = trim((string) $request->input('q', ''));
        $limit = min(50, max(1, (int) $request->input('limit', 30)));

        $rows = Variation::join('products as p', 'variations.product_id', '=', 'p.id')
            ->leftJoin('variation_location_details as vld', function ($join) use ($location_id) {
                $join->on('variations.id', '=', 'vld.variation_id')->where('vld.location_id', '=', $location_id);
            })
            ->leftJoin('units as u', 'p.unit_id', '=', 'u.id')
            ->where('p.business_id', $business_id)
            ->whereIn('p.type', ['single', 'variable'])
            ->where('p.enable_stock', 1)
            ->where('p.is_inactive', 0)
            ->whereNull('variations.deleted_at')
            ->when($term !== '', function ($q) use ($term) {
                $q->where(function ($w) use ($term) {
                    $w->where('p.name', 'like', "%{$term}%")
                        ->orWhere('p.sku', 'like', "%{$term}%")
                        ->orWhere('variations.sub_sku', 'like', "%{$term}%");
                });
            })
            ->select(
                'p.id as product_id',
                'p.name as product_name',
                'p.unit_id',
                'variations.id as variation_id',
                'variations.name as variation_name',
                'variations.sub_sku',
                'variations.dpp_inc_tax',
                'variations.default_purchase_price',
                'u.actual_name as unit_name',
                'u.allow_decimal',
                DB::raw('COALESCE(vld.qty_available, 0) as stock')
            )
            ->orderBy('p.name')
            ->limit($limit)
            ->get();

        return $this->ok($rows->map(function ($r) use ($business_id) {
            $name = $r->product_name;
            if (! empty($r->variation_name) && $r->variation_name !== 'DUMMY') {
                $name .= ' - '.$r->variation_name;
            }

            return [
                'product_id' => (int) $r->product_id,
                'variation_id' => (int) $r->variation_id,
                'name' => $name,
                'sku' => $r->sub_sku,
                'unit_price' => (float) ($r->dpp_inc_tax ?: $r->default_purchase_price ?: 0),
                'stock' => (float) $r->stock,
                'unit_id' => (int) $r->unit_id,
                'unit_name' => $r->unit_name ?: 'Pc',
                'allow_decimal' => (int) $r->allow_decimal,
                'units' => $this->saleAppUnits($business_id, (int) $r->product_id, (int) $r->unit_id),
            ];
        })->values());
    }

    public function apiStoreTransfer(Request $request)
    {
        if (! auth()->user()->can('stock_transfer.create')) {
            return $this->fail('Unauthorized action.', 403);
        }

        $business_id = (int) $request->session()->get('user.business_id');
        $locations = BusinessLocation::forDropdown($business_id, false)->toArray();
        $data = $request->validate([
            'location_id' => 'required|integer',
            'transfer_location_id' => 'required|integer|different:location_id',
            'transaction_date' => 'nullable|string',
            'ref_no' => 'nullable|string|max:191',
            'status' => 'required|in:pending,in_transit,completed',
            'shipping_charges' => 'nullable|numeric|min:0',
            'additional_notes' => 'nullable|string|max:2000',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer',
            'products.*.variation_id' => 'required|integer',
            'products.*.quantity' => 'required|numeric|min:0.0001',
            'products.*.unit_price' => 'nullable|numeric|min:0',
            'products.*.sub_unit_id' => 'nullable|integer',
        ]);
        foreach (['location_id', 'transfer_location_id'] as $key) {
            if (! array_key_exists((int) $data[$key], $locations)) {
                return $this->fail('Invalid location.', 422, [$key => ['Invalid location.']]);
            }
        }

        // Normalise lines exactly as the admin Stock Transfer form posts them
        $products = [];
        $total = 0.0;
        foreach ($data['products'] as $line) {
            $variation = Variation::join('products as p', 'variations.product_id', '=', 'p.id')
                ->where('p.business_id', $business_id)
                ->where('variations.id', (int) $line['variation_id'])
                ->where('p.id', (int) $line['product_id'])
                ->select('variations.*', 'p.unit_id as product_unit_id')
                ->first();
            if (! $variation) {
                return $this->fail('Invalid product in transfer.', 422);
            }
            $units = $this->saleAppUnits($business_id, (int) $line['product_id'], (int) $variation->product_unit_id);
            $subId = ! empty($line['sub_unit_id']) ? (int) $line['sub_unit_id'] : (int) $variation->product_unit_id;
            $mult = $this->saleAppUnitMultiplier($units, $subId, (int) $variation->product_unit_id);
            $basePrice = (float) ($variation->dpp_inc_tax ?: $variation->default_purchase_price ?: 0);
            $unitPrice = array_key_exists('unit_price', $line) && $line['unit_price'] !== null
                ? (float) $line['unit_price']
                : round($basePrice * $mult, 4);

            $row = [
                'product_id' => (int) $line['product_id'],
                'variation_id' => (int) $line['variation_id'],
                'quantity' => (float) $line['quantity'],
                'unit_price' => $unitPrice,
                'product_unit_id' => (int) $variation->product_unit_id,
            ];
            if ($subId && $subId !== (int) $variation->product_unit_id) {
                $row['sub_unit_id'] = $subId;
                $row['base_unit_multiplier'] = $mult;
            }
            $products[] = $row;
            $total += (float) $line['quantity'] * $unitPrice;
        }

        $shipping = (float) ($data['shipping_charges'] ?? 0);
        $request->merge([
            'products' => $products,
            'transaction_date' => $data['transaction_date'] ?? now()->format('Y-m-d H:i'),
            'shipping_charges' => $shipping,
            'final_total' => round($total + $shipping, 4),
        ]);

        $response = $this->storeStockTransfer($request);
        $outcome = $this->redirectOutcome($request, $response);
        if (! $outcome['success']) {
            return $this->fail($outcome['message'] ?: 'Could not create stock transfer.', 422, $outcome['errors']);
        }

        $transfer = Transaction::where('business_id', $business_id)
            ->where('type', 'sell_transfer')
            ->where('created_by', auth()->id())
            ->with(['location:id,name', 'transfer_child' => fn ($q) => $q->with('location:id,name')])
            ->orderByDesc('id')
            ->first();

        return $this->ok($transfer ? $this->transferRow($transfer) : null, $outcome['message'], 201);
    }

    // ============================================================ Serializers / helpers

    protected function orderFormMeta(): array
    {
        return [
            'can_edit_price' => auth()->user()->can('edit_product_price_from_sales_app'),
            'payment_types' => [
                ['key' => 'cash', 'label' => 'Cash'],
                ['key' => 'due', 'label' => 'Credit (due)'],
            ],
            'order_modes' => [
                ['key' => 'new_order', 'label' => 'New order'],
                ['key' => 'estimate', 'label' => 'Estimate'],
                ['key' => 'back_order', 'label' => 'Back order'],
            ],
            'fulfillment_options' => ['Pickup', 'Delivery', 'Shipping'],
            'shipping_statuses' => ['ordered', 'packed', 'shipped', 'delivered', 'cancelled'],
        ];
    }

    protected function orderRow(Transaction $o): array
    {
        $saleStatus = $o->sale_status ?? null;
        $isEstimate = (int) $o->is_quotation === 1;

        return [
            'id' => $o->id,
            'invoice_no' => $o->invoice_no,
            'doc_type' => $isEstimate ? 'estimate' : 'sales_order',
            'type' => $o->type,
            'status' => $o->status,
            'sale_status' => $saleStatus,
            'sale_status_label' => match ($saleStatus) {
                'invoiced' => 'Invoiced',
                'estimate' => 'Estimate',
                'back_order' => 'Back order',
                default => 'Sale',
            },
            'is_back_order' => $o->type === 'sales_order' && trim((string) $o->staff_note) === 'back_order',
            'transaction_date' => $this->dt($o->transaction_date),
            'final_total' => (float) $o->final_total,
            'display_total' => round((float) ($o->sale_display_total ?? $o->final_total), 2),
            'payment_status' => $o->payment_status,
            'prefer_payment_method' => $o->prefer_payment_method,
            'is_credit_sale' => (bool) ($o->is_credit_sale ?? (($o->prefer_payment_method ?? '') === 'credit_sale')),
            'terms' => (($o->prefer_payment_method ?? '') === 'credit_sale') ? 'Credit' : 'Cash',
            'converted_invoice_no' => $o->converted_invoice_no ?? null,
            'shipping_status' => $o->shipping_status,
            'shipping_method' => $o->shipping_method,
            'location_id' => $o->location_id,
            'can_edit' => (bool) ($o->can_edit ?? false),
            'can_show_edit' => (bool) ($o->can_show_edit ?? false),
            'can_delete' => SalesOrderPermission::canDelete(auth()->user(), $o),
            'customer' => $o->relationLoaded('contact') && $o->contact ? [
                'id' => $o->contact->id,
                'name' => $o->contact->name,
                'business_name' => $o->contact->supplier_business_name,
                'display_name' => trim((string) ($o->contact->supplier_business_name ?: $o->contact->name)),
                'mobile' => $o->contact->mobile,
            ] : null,
        ];
    }

    protected function orderDetail(Transaction $order, array $amounts): array
    {
        $lines = collect($order->sell_lines ?? [])->filter(fn ($l) => empty($l->parent_sell_line_id))->values();

        $lineRows = $lines->map(function ($line) {
            $product = $line->product;
            $mult = 1.0;
            $unitName = optional(optional($product)->unit)->actual_name ?: 'Pc';
            if (! empty($line->sub_unit_id) && $line->sub_unit) {
                $mult = (float) ($line->sub_unit->base_unit_multiplier ?: 1);
                $unitName = $line->sub_unit->actual_name ?: $unitName;
            }
            $sold = (float) $line->unit_price_inc_tax;
            $list = max((float) ($line->unit_price_before_discount ?: $sold), $sold);
            $vname = optional($line->variations)->name;
            $qtyBase = (float) $line->quantity;

            return [
                'id' => $line->id,
                'product_id' => (int) $line->product_id,
                'variation_id' => (int) $line->variation_id,
                'product_name' => optional($product)->name,
                'variation_name' => ($vname && $vname !== 'DUMMY') ? $vname : null,
                'sku' => optional($line->variations)->sub_sku ?: optional($product)->sku,
                'image' => $this->saleProductImage(optional($product)->image)['url'],
                'sub_unit_id' => $line->sub_unit_id ? (int) $line->sub_unit_id : null,
                'unit_name' => $unitName,
                'unit_multiplier' => $mult,
                'quantity' => $mult > 0 ? $qtyBase / $mult : $qtyBase,
                'quantity_base' => $qtyBase,
                'unit_price' => round($sold * $mult, 4),
                'list_price' => round($list * $mult, 4),
                'unit_price_base' => $sold,
                'line_total' => round($sold * $qtyBase, 2),
                'line_list_total' => round($list * $qtyBase, 2),
                'line_discount' => round(max(0, ($list - $sold) * $qtyBase), 2),
                'sell_line_note' => $line->sell_line_note,
            ];
        });

        // Same grouping as the PWA order screen (one row per variation, mixed units)
        $items = $lineRows->groupBy(fn ($r) => (string) ($r['variation_id'] ?: $r['product_id']))->map(function ($group) {
            $first = $group->first();
            $total = (float) $group->sum('line_total');
            $listTotal = (float) $group->sum('line_list_total');
            $disc = max(0, $listTotal - $total);
            $mix = $group->map(fn ($r) => rtrim(rtrim(number_format($r['quantity'], 3), '0'), '.').' '.$r['unit_name'])->implode(' + ');
            if ($group->contains(fn ($r) => $r['unit_multiplier'] > 1)) {
                $mix .= '  =  '.rtrim(rtrim(number_format($group->sum('quantity_base'), 3), '0'), '.').' Pc';
            }

            return [
                'product_id' => $first['product_id'],
                'variation_id' => $first['variation_id'],
                'name' => $first['product_name'],
                'variation_name' => $first['variation_name'],
                'quantity_text' => $mix,
                'total' => round($total, 2),
                'list_total' => round($listTotal, 2),
                'discount' => round($disc, 2),
                'discount_percent' => $listTotal > 0 ? round(($disc / $listTotal) * 100, 1) : 0,
            ];
        })->values();

        $payments = collect($order->payment_lines ?? [])->map(fn ($p) => [
            'id' => $p->id,
            'amount' => (float) $p->amount,
            'method' => $p->method,
            'paid_on' => $this->dt($p->paid_on),
            'payment_ref_no' => $p->payment_ref_no,
            'is_return' => (bool) $p->is_return,
            'note' => $p->note,
        ])->values();

        $c = $order->contact;

        return array_merge($this->orderRow($order), [
            'shipping_details' => $order->shipping_details,
            'shipping_address' => $order->shipping_address,
            'sale_note' => $order->additional_notes,
            'customer' => $c ? [
                'id' => $c->id,
                'contact_id' => $c->contact_id,
                'name' => $c->name,
                'business_name' => $c->supplier_business_name,
                'display_name' => trim((string) ($c->supplier_business_name ?: $c->name)),
                'mobile' => $c->mobile,
                'email' => $c->email,
            ] : null,
            'amounts' => $this->roundAll($amounts),
            'items' => $items,
            'lines' => $lineRows->values(),
            'payments' => $payments,
        ]);
    }

    protected function customerDetail(Contact $c): array
    {
        return [
            'id' => $c->id,
            'contact_id' => $c->contact_id,
            'type' => $c->type,
            'contact_type' => $c->contact_type,
            'name' => $c->name,
            'prefix' => $c->prefix,
            'first_name' => $c->first_name,
            'middle_name' => $c->middle_name,
            'last_name' => $c->last_name,
            'business_name' => $c->supplier_business_name,
            'display_name' => trim((string) ($c->supplier_business_name ?: $c->name)),
            'email' => $c->email,
            'mobile' => $c->mobile,
            'alternate_number' => $c->alternate_number,
            'landline' => $c->landline,
            'tax_number' => $c->tax_number,
            'address_line_1' => $c->address_line_1,
            'address_line_2' => $c->address_line_2,
            'city' => $c->city,
            'state' => $c->state,
            'country' => $c->country,
            'zip_code' => $c->zip_code,
            'shipping_address' => $c->shipping_address,
            'address' => $this->saleAppCustomerAddress($c),
            'customer_group_id' => $c->customer_group_id,
            'credit_limit' => $c->credit_limit !== null ? (float) $c->credit_limit : null,
            'pay_term_number' => $c->pay_term_number,
            'pay_term_type' => $c->pay_term_type,
            'dob' => $c->dob,
            'contact_status' => $c->contact_status,
            'custom_field1' => $c->custom_field1,
            'custom_field2' => $c->custom_field2,
            'custom_field3' => $c->custom_field3,
            'custom_field4' => $c->custom_field4,
            'allow_customer_app' => (int) ($c->allow_customer_app ?? 0) === 1,
            'created_at' => $this->dt($c->created_at),
        ];
    }

    protected function bundlePayload(array $d): array
    {
        return [
            'customer' => $this->customerDetail($d['customer']),
            'group_name' => $d['groupName'] !== '' ? $d['groupName'] : null,
            'address' => $d['custAddr'],
            'aging' => $this->roundAll($d['aging']),
            'open_balance' => (float) $d['openBalance'],
            'open_invoices' => collect($d['openInvoices'])->values(),
            'history' => $this->historyRows($d['history']),
        ];
    }

    protected function historyRows($rows): array
    {
        return collect($rows)->map(function ($r) {
            $own = isset($r['show_url']) && str_contains((string) $r['show_url'], '/sale/orders/');
            unset($r['show_url']);
            $r['is_own_order'] = $own;

            return $r;
        })->values()->all();
    }

    protected function transferRow(Transaction $t): array
    {
        $child = $t->transfer_child;
        $statusLabel = match ($t->status) {
            'final' => 'Completed',
            'in_transit' => 'In transit',
            'pending' => 'Pending',
            default => ucfirst((string) $t->status),
        };

        return [
            'id' => $t->id,
            'ref_no' => $t->ref_no,
            'transaction_date' => $this->dt($t->transaction_date),
            'status' => $t->status === 'final' ? 'completed' : $t->status,
            'status_label' => $statusLabel,
            'final_total' => (float) $t->final_total,
            'additional_notes' => $t->additional_notes,
            'from_location' => $t->location ? ['id' => $t->location->id, 'name' => $t->location->name] : null,
            'to_location' => $child && $child->location ? ['id' => $child->location->id, 'name' => $child->location->name] : null,
            'receive_status' => $child?->status,
        ];
    }

    protected function locationPayload(BusinessLocation $loc): array
    {
        return [
            'id' => $loc->id,
            'location_id' => $loc->location_id,
            'name' => $loc->name,
            'landmark' => $loc->landmark,
            'city' => $loc->city,
            'state' => $loc->state,
            'zip_code' => $loc->zip_code,
            'country' => $loc->country,
            'mobile' => $loc->mobile,
            'email' => $loc->email,
            'latitude' => $loc->latitude !== null ? (float) $loc->latitude : null,
            'longitude' => $loc->longitude !== null ? (float) $loc->longitude : null,
        ];
    }

    protected function mergeMissing(Request $request, array $keys): void
    {
        $missing = [];
        foreach ($keys as $key) {
            if (! $request->exists($key)) {
                $missing[$key] = null;
            }
        }
        if ($missing !== []) {
            $request->merge($missing);
        }
    }

    protected function formCustomer(?array $c): ?array
    {
        if (! $c) {
            return null;
        }
        $c['id'] = (int) $c['id'];
        $c['credit_limit'] = isset($c['credit_limit']) && $c['credit_limit'] !== '' ? (float) $c['credit_limit'] : null;

        return $c;
    }

    protected function dropdownList($items): array
    {
        return collect($items)->map(fn ($label, $id) => ['id' => (int) $id, 'name' => $label])->values()->all();
    }

    protected function roundAll(array $values): array
    {
        return array_map(function ($v) {
            if (is_float($v)) {
                return round($v, 2);
            }
            if (is_array($v)) {
                return $this->roundAll($v);
            }

            return $v;
        }, $values);
    }

    protected function zeroSaleReasonList(): array
    {
        return collect($this->saleAppZeroSaleReasons())->map(fn ($r) => ['id' => $r->id, 'name' => $r->name])->values()->all();
    }

    protected function defaultLocationParam(Request $request): void
    {
        if (! $request->filled('location_id') && $request->session()->get('user.default_location_id')) {
            $request->query->set('location_id', (string) $request->session()->get('user.default_location_id'));
        }
    }

    protected function findCreatedOrder(Request $request, string $message): ?Transaction
    {
        $base = Transaction::where('business_id', $request->session()->get('user.business_id'))
            ->where('created_by', auth()->id())
            ->where('source', 'sale_app');

        $invoiceNo = trim(Str::afterLast($message, ': '));
        if ($invoiceNo !== '' && $invoiceNo !== $message) {
            $found = (clone $base)->where('invoice_no', $invoiceNo)->orderByDesc('id')->first();
            if ($found) {
                return $found;
            }
        }

        return $base->orderByDesc('id')->first();
    }

    protected function cannotEditMessage(Transaction $order): string
    {
        return $this->saleAppHasEditPermission($order)
            ? 'This order is invoiced and cannot be edited.'
            : 'You do not have permission to edit this order.';
    }

    protected function attendance(): AttendanceController
    {
        return app(AttendanceController::class);
    }

    protected function attendanceResult($result)
    {
        $result = is_array($result) ? $result : (method_exists($result, 'getData') ? $result->getData(true) : []);
        $ok = ! empty($result['success']);
        $status = $result['status'] ?? $this->attendance()->status()->getData(true);

        return $ok
            ? $this->ok($status, $result['msg'] ?? null)
            : response()->json([
                'success' => false,
                'message' => $result['msg'] ?? __('messages.something_went_wrong'),
                'errors' => null,
                'data' => $status,
            ], 422);
    }
}
