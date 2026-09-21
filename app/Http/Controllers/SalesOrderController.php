<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Contact;
use App\Transaction;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    protected $transactionUtil;

    protected $businessUtil;

    protected $commonUtil;

    protected $sales_order_statuses;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(TransactionUtil $transactionUtil, BusinessUtil $businessUtil, Util $commonUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->businessUtil = $businessUtil;
        $this->commonUtil = $commonUtil;
        $this->sales_order_statuses = [
            'ordered' => [
                'label' => __('lang_v1.ordered'),
                'class' => 'bg-info',
            ],
            'partial' => [
                'label' => __('lang_v1.partial'),
                'class' => 'bg-yellow',
            ],
            'completed' => [
                'label' => __('restaurant.completed'),
                'class' => 'bg-green',
            ],
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('so.view_own') && ! auth()->user()->can('so.view_all') && ! auth()->user()->can('so.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);

        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        $sales_order_statuses = [];
        foreach ($this->sales_order_statuses as $key => $value) {
            $sales_order_statuses[$key] = $value['label'];
        }

        $so_stats = $this->getSalesOrderListStats((int) $business_id);
        $sources = Transaction::salesOrderSourceOptions();

        return view('sales_order.index')
            ->with(compact('business_locations', 'customers', 'shipping_statuses', 'sales_order_statuses', 'so_stats', 'sources'));
    }

    /**
     * Full-page sales order details hub.
     */
    public function show($id)
    {
        if (! auth()->user()->can('so.view_own') && ! auth()->user()->can('so.view_all')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $query = Transaction::where('business_id', $business_id)
            ->where('type', 'sales_order')
            ->with([
                'contact',
                'payment_lines',
                'delivery_person_user',
                'sell_lines' => function ($q) {
                    $q->whereNull('parent_sell_line_id');
                },
                'sell_lines.product',
                'sell_lines.product.unit',
                'sell_lines.variations',
                'sell_lines.variations.product_variation',
            ]);

        if (! auth()->user()->can('so.view_all') && auth()->user()->can('so.view_own')) {
            $query->where('created_by', auth()->id());
        }

        $permitted = auth()->user()->permitted_locations();
        if ($permitted != 'all') {
            $query->whereIn('location_id', $permitted);
        }

        $order = $query->findOrFail($id);

        $meta = null;
        if (class_exists(\Modules\Ecommerce\Entities\EcomOrderMeta::class)) {
            $meta = \Modules\Ecommerce\Entities\EcomOrderMeta::where('transaction_id', $order->id)->first();
        }

        $payment_types = $this->transactionUtil->payment_types($order->location_id, true);
        $shipping_statuses = $this->transactionUtil->shipping_statuses();
        $sales_order_statuses = $this->sales_order_statuses;
        $converted_invoices = Transaction::invoicesBySalesOrderId((int) $business_id)[(int) $order->id] ?? [];
        $converted_invoice_html = $this->transactionUtil->salesOrderConvertedInvoiceHtml((int) $business_id, (int) $order->id);
        $users = User::forDropdown($business_id, false, false, false);
        $is_admin = $this->businessUtil->is_admin(auth()->user());
        $can_update_shipping = $is_admin || auth()->user()->hasAnyPermission([
            'access_shipping', 'access_own_shipping', 'access_commission_agent_shipping',
        ]);
        $can_update_status = $is_admin || \App\Utils\SalesOrderPermission::canUpdateAny(auth()->user());
        $can_edit_order = $is_admin || \App\Utils\SalesOrderPermission::canUpdate(auth()->user(), $order);

        return view('sales_order.show', compact(
            'order',
            'meta',
            'payment_types',
            'shipping_statuses',
            'sales_order_statuses',
            'converted_invoices',
            'converted_invoice_html',
            'users',
            'can_update_shipping',
            'can_update_status',
            'can_edit_order'
        ));
    }

    /**
     * Update sales order status from details page.
     */
    public function updateStatus(Request $request, $id)
    {
        $is_admin = $this->businessUtil->is_admin(auth()->user());
        if (! $is_admin && ! \App\Utils\SalesOrderPermission::canUpdateAny(auth()->user())) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $order = $this->findSalesOrderOrFail($business_id, $id);

        $status = $request->input('status');
        if (! array_key_exists($status, $this->sales_order_statuses)) {
            abort(422);
        }

        $transaction_before = $order->replicate();
        $order->status = $status;
        $order->save();

        $this->commonUtil->activityLog($order, 'status_updated', $transaction_before, [
            'from' => $transaction_before->status,
            'to' => $status,
        ]);

        return redirect()->back()->with('status', [
            'success' => 1,
            'msg' => __('lang_v1.updated_success'),
        ]);
    }

    /**
     * Update shipping fields from details page.
     */
    public function updateShipping(Request $request, $id)
    {
        $is_admin = $this->businessUtil->is_admin(auth()->user());
        if (! $is_admin && ! auth()->user()->hasAnyPermission([
            'access_shipping', 'access_own_shipping', 'access_commission_agent_shipping',
        ])) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');
            $order = $this->findSalesOrderOrFail($business_id, $id);

            $input = $request->only([
                'shipping_details',
                'shipping_address',
                'shipping_status',
                'delivered_to',
                'delivery_person',
            ]);

            $transaction_before = $order->replicate();

            $new_shipping_status = $input['shipping_status'] ?? null;
            app(\App\Services\CreditControlService::class)
                ->assertCanAdvanceShippingStatus($order, $new_shipping_status);

            $order->update($input);

            $this->transactionUtil->activityLog($order, 'shipping_edited', $transaction_before, [
                'update_note' => $request->input('shipping_note', ''),
            ]);

            return redirect()->back()->with('status', [
                'success' => 1,
                'msg' => __('lang_v1.updated_success'),
            ]);
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $msg = __('messages.something_went_wrong');
            if ($e instanceof \App\Exceptions\CreditShipmentHoldException) {
                $msg = $e->getMessage();
            }

            return redirect()->back()->with('status', [
                'success' => 0,
                'msg' => $msg,
            ]);
        }
    }

    /**
     * Update ecommerce fulfillment status when order meta exists.
     */
    public function updateFulfillment(Request $request, $id)
    {
        if (! auth()->user()->can('so.update') && ! \App\Utils\SalesOrderPermission::canUpdateAny(auth()->user()) && ! $this->businessUtil->is_admin(auth()->user())) {
            abort(403, 'Unauthorized action.');
        }

        if (! class_exists(\Modules\Ecommerce\Entities\EcomOrderMeta::class)) {
            abort(404);
        }

        $business_id = $request->session()->get('user.business_id');
        $order = $this->findSalesOrderOrFail($business_id, $id);

        $meta = \Modules\Ecommerce\Entities\EcomOrderMeta::firstOrCreate(
            ['transaction_id' => $order->id],
            [
                'business_id' => $business_id,
                'tracking_token' => \Illuminate\Support\Str::random(40),
                'fulfillment_status' => 'pending',
                'timeline' => [],
            ]
        );

        $status = $request->input('fulfillment_status');
        $allowed = ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'delivered', 'cancelled', 'returned'];
        if (! in_array($status, $allowed, true)) {
            abort(422);
        }

        $timeline = $meta->timeline ?? [];
        $timeline[] = [
            'status' => $status,
            'label' => ucfirst($status),
            'at' => now()->toDateTimeString(),
            'note' => $request->input('note'),
        ];
        $meta->fulfillment_status = $status;
        $meta->timeline = $timeline;
        $meta->save();

        $shipping_map = [
            'packed' => 'packed',
            'shipped' => 'shipped',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled',
        ];
        if (isset($shipping_map[$status])) {
            app(\App\Services\CreditControlService::class)
                ->assertCanAdvanceShippingStatus($order, $shipping_map[$status]);
            $order->shipping_status = $shipping_map[$status];
            $order->save();
        }

        $msg = class_exists(\Modules\Ecommerce\Providers\EcommerceServiceProvider::class)
            ? __('ecommerce::lang.status_updated')
            : __('lang_v1.updated_success');

        return redirect()->back()->with('status', [
            'success' => 1,
            'msg' => $msg,
        ]);
    }

    protected function findSalesOrderOrFail(int $business_id, $id): Transaction
    {
        $query = Transaction::where('business_id', $business_id)
            ->where('type', 'sales_order');

        $is_admin = $this->businessUtil->is_admin(auth()->user());
        if (! $is_admin && ! auth()->user()->can('so.view_all') && auth()->user()->can('so.view_own')) {
            $query->where('created_by', auth()->id());
        }

        $permitted = auth()->user()->permitted_locations();
        if ($permitted != 'all') {
            $query->whereIn('location_id', $permitted);
        }

        return $query->findOrFail($id);
    }

    /**
     * KPI stats for sales order list cards only.
     */
    protected function getSalesOrderListStats(int $businessId): array
    {
        $base = Transaction::where('business_id', $businessId)->where('type', 'sales_order');

        if (! auth()->user()->can('so.view_all') && auth()->user()->can('so.view_own')) {
            $base->where('created_by', auth()->id());
        }

        $permitted = auth()->user()->permitted_locations();
        if ($permitted != 'all') {
            $base->whereIn('location_id', $permitted);
        }

        $total = (int) (clone $base)->count();
        $ordered = (int) (clone $base)->where('status', 'ordered')->count();
        $partial = (int) (clone $base)->where('status', 'partial')->count();
        $completed = (int) (clone $base)->where('status', 'completed')->count();
        $thisMonth = (int) (clone $base)
            ->whereBetween('transaction_date', [
                \Carbon\Carbon::now()->startOfMonth()->toDateTimeString(),
                \Carbon\Carbon::now()->endOfMonth()->toDateTimeString(),
            ])
            ->count();
        $totalAmount = (float) (clone $base)->sum('final_total');

        return compact('total', 'ordered', 'partial', 'completed', 'thisMonth', 'totalAmount');
    }

    public function getSalesOrders($customer_id)
    {
        $business_id = request()->session()->get('user.business_id');
        $location_id = request()->input('location_id');

        $sales_orders = Transaction::where('business_id', $business_id)
                            ->where('location_id', $location_id)
                            ->where('type', 'sales_order')
                            ->whereIn('status', ['partial', 'ordered'])
                            ->where('contact_id', $customer_id)
                            ->select('invoice_no as text', 'id')
                            ->get();

        return $sales_orders;
    }

    /**
     * get required resources
     *
     * to edit sales order status
     *
     * @return \Illuminate\Http\Response
     */
    public function getEditSalesOrderStatus(Request $request, $id)
    {
        $is_admin = $this->businessUtil->is_admin(auth()->user());
        if (! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $transaction = Transaction::where('business_id', $business_id)
                                ->findOrFail($id);

            $status = $transaction->status;
            $statuses = $this->sales_order_statuses;

            return view('sales_order.edit_status_modal')
                ->with(compact('id', 'status', 'statuses'));
        }
    }

    /**
     * updare sales order status
     *
     * @return \Illuminate\Http\Response
     */
    public function postEditSalesOrderStatus(Request $request, $id)
    {
        $is_admin = $this->businessUtil->is_admin(auth()->user());
        if (! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');
                $transaction = Transaction::where('business_id', $business_id)
                                ->findOrFail($id);

                $transaction_before = $transaction->replicate();

                $transaction->status = $request->input('status');
                $transaction->save();

                $activity_property = ['from' => $transaction_before->status, 'to' => $request->input('status')];
                $this->commonUtil->activityLog($transaction, 'status_updated', $transaction_before, $activity_property);

                $output = [
                    'success' => 1,
                    'msg' => trans('lang_v1.success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
                $output = [
                    'success' => 0,
                    'msg' => trans('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }
}
