<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Charts\CommonChart;
use App\Currency;
use App\Media;
use App\Transaction;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\RestaurantUtil;
use App\Utils\TransactionUtil;
use App\Utils\ProductUtil;
use App\Utils\Util;
use App\VariationLocationDetails;
use Datatables;
use DB;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $businessUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    protected $commonUtil;

    protected $restUtil;
    protected $productUtil;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        ModuleUtil $moduleUtil,
        Util $commonUtil,
        RestaurantUtil $restUtil,
        ProductUtil $productUtil,
    ) {
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->commonUtil = $commonUtil;
        $this->restUtil = $restUtil;
        $this->productUtil = $productUtil;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        if ($user->user_type == 'user_customer') {
            return redirect()->action([\Modules\Crm\Http\Controllers\DashboardController::class, 'index']);
        }

        $business_id = request()->session()->get('user.business_id');

        $is_admin = $this->businessUtil->is_admin(auth()->user());

        if (! auth()->user()->can('dashboard.data')) {
            return view('home.index');
        }

        $fy = $this->businessUtil->getCurrentFinancialYear($business_id);

        $currency = Currency::where('id', request()->session()->get('business.currency_id'))->first();
        //ensure start date starts from at least 30 days before to get sells last 30 days
        $least_30_days = \Carbon::parse($fy['start'])->subDays(30)->format('Y-m-d');

        //get all sells (cached 3 min — heavy FY aggregate used only for dashboard charts)
        $sells_cache_key = 'home_sells_fy_'.$business_id.'_'.auth()->id().'_'.$least_30_days.'_'.$fy['end'];
        $sells_this_fy = Cache::remember($sells_cache_key, 180, function () use ($business_id, $least_30_days, $fy) {
            return $this->transactionUtil->getSellsCurrentFy($business_id, $least_30_days, $fy['end']);
        });

        $all_locations = BusinessLocation::forDropdown($business_id)->toArray();

        //Chart for sells last 30 days
        $labels = [];
        $all_sell_values = [];
        $dates = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = \Carbon::now()->subDays($i)->format('Y-m-d');
            $dates[] = $date;

            $labels[] = date('j M Y', strtotime($date));

            $total_sell_on_date = $sells_this_fy->where('date', $date)->sum('total_sells');

            if (! empty($total_sell_on_date)) {
                $all_sell_values[] = (float) $total_sell_on_date;
            } else {
                $all_sell_values[] = 0;
            }
        }

        //Group sells by location
        $location_sells = [];
        foreach ($all_locations as $loc_id => $loc_name) {
            $values = [];
            foreach ($dates as $date) {
                $total_sell_on_date_location = $sells_this_fy->where('date', $date)->where('location_id', $loc_id)->sum('total_sells');

                if (! empty($total_sell_on_date_location)) {
                    $values[] = (float) $total_sell_on_date_location;
                } else {
                    $values[] = 0;
                }
            }
            $location_sells[$loc_id]['loc_label'] = $loc_name;
            $location_sells[$loc_id]['values'] = $values;
        }

        $sells_chart_1 = new CommonChart;
        $sells_chart_1_options = $this->__chartOptions(__(
            'home.total_sells',
            ['currency' => $currency->code]
        ));

        // Sales chart: vertical column (bar) chart
        $sells_chart_1_options['chart']['type'] = 'column';
        $sells_chart_1_options['colors'] = ['#3b82f6'];
        $sells_chart_1_options['yAxis']['title']['text'] = null;
        $sells_chart_1_options['yAxis']['gridLineColor'] = '#e5e7eb';
        $sells_chart_1_options['yAxis']['gridLineDashStyle'] = 'Solid';
        $sells_chart_1_options['yAxis']['gridLineWidth'] = 1;
        $sells_chart_1_options['yAxis']['min'] = 0;
        $sells_chart_1_options['xAxis']['labels']['rotation'] = -45;
        $sells_chart_1_options['legend']['enabled'] = false;
        $sells_chart_1_options['plotOptions'] = [
            'column' => [
                'borderWidth' => 0,
                'borderRadius' => 3,
                'color' => '#3b82f6',
                'pointPadding' => 0.15,
                'groupPadding' => 0.1,
            ],
            'series' => [
                'type' => 'column',
                'borderWidth' => 0,
            ],
        ];

        // Shorter x-axis labels like screenshot (e.g. Jul 12)
        $short_labels = [];
        foreach ($dates as $date) {
            $short_labels[] = date('M j', strtotime($date));
        }

        $sells_chart_1->labels($short_labels)
                        ->options($sells_chart_1_options);

        // Single combined sales series (column)
        $sells_chart_1->dataset(__('home.total_sell'), 'column', $all_sell_values);

        $labels = [];
        $values = [];
        $date = strtotime($fy['start']);
        $last = date('m-Y', strtotime($fy['end']));
        $fy_months = [];
        do {
            $month_year = date('m-Y', $date);
            $fy_months[] = $month_year;

            $labels[] = \Carbon::createFromFormat('m-Y', $month_year)
                            ->format('M-Y');
            $date = strtotime('+1 month', $date);

            $total_sell_in_month_year = $sells_this_fy->where('yearmonth', $month_year)->sum('total_sells');

            if (! empty($total_sell_in_month_year)) {
                $values[] = (float) $total_sell_in_month_year;
            } else {
                $values[] = 0;
            }
        } while ($month_year != $last);

        $fy_sells_by_location_data = [];

        foreach ($all_locations as $loc_id => $loc_name) {
            $values_data = [];
            foreach ($fy_months as $month) {
                $total_sell_in_month_year_location = $sells_this_fy->where('yearmonth', $month)->where('location_id', $loc_id)->sum('total_sells');

                if (! empty($total_sell_in_month_year_location)) {
                    $values_data[] = (float) $total_sell_in_month_year_location;
                } else {
                    $values_data[] = 0;
                }
            }
            $fy_sells_by_location_data[$loc_id]['loc_label'] = $loc_name;
            $fy_sells_by_location_data[$loc_id]['values'] = $values_data;
        }

        $sells_chart_2 = new CommonChart;
        $sells_chart_2_options = $this->__chartOptions(__(
            'home.total_sells',
            ['currency' => $currency->code]
        ));
        $sells_chart_2_options['chart']['type'] = 'column';
        $sells_chart_2_options['colors'] = ['#3b82f6'];
        $sells_chart_2_options['yAxis']['title']['text'] = null;
        $sells_chart_2_options['yAxis']['gridLineColor'] = '#e5e7eb';
        $sells_chart_2_options['yAxis']['gridLineDashStyle'] = 'Solid';
        $sells_chart_2_options['yAxis']['gridLineWidth'] = 1;
        $sells_chart_2_options['yAxis']['min'] = 0;
        $sells_chart_2_options['legend']['enabled'] = false;
        $sells_chart_2_options['plotOptions'] = [
            'column' => [
                'borderWidth' => 0,
                'borderRadius' => 3,
                'color' => '#3b82f6',
                'pointPadding' => 0.15,
                'groupPadding' => 0.1,
            ],
            'series' => [
                'type' => 'column',
                'borderWidth' => 0,
            ],
        ];
        $sells_chart_2->labels($labels)
                    ->options($sells_chart_2_options);
        $sells_chart_2->dataset(__('home.total_sell'), 'column', $values);

        //Get Dashboard widgets from module
        $module_widgets = $this->moduleUtil->getModuleData('dashboard_widget');

        $widgets = [];

        foreach ($module_widgets as $widget_array) {
            if (! empty($widget_array['position'])) {
                $widgets[$widget_array['position']][] = $widget_array['widget'];
            }
        }

        $common_settings = ! empty(session('business.common_settings')) ? session('business.common_settings') : [];


        return view('home.index', compact('sells_chart_1', 'sells_chart_2', 'widgets', 'all_locations', 'common_settings', 'is_admin'));
    }

    /**
     * Retrieves purchase and sell details for a given time period.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTotals()
    {
        if (request()->ajax()) {
            $start = request()->start;
            $end = request()->end;
            $location_id = request()->location_id;
            $business_id = request()->session()->get('user.business_id');

            // get user id parameter
            $created_by = request()->user_id;

            $purchase_details = $this->transactionUtil->getPurchaseTotals($business_id, $start, $end, $location_id, $created_by);

            $sell_details = $this->transactionUtil->getSellTotals($business_id, $start, $end, $location_id, $created_by);

            $total_ledger_discount = $this->transactionUtil->getTotalLedgerDiscount($business_id, $start, $end);

            $purchase_details['purchase_due'] = $purchase_details['purchase_due'] - $total_ledger_discount['total_purchase_discount'];

            $transaction_types = [
                'purchase_return', 'sell_return', 'expense',
            ];

            $transaction_totals = $this->transactionUtil->getTransactionTotals(
                $business_id,
                $transaction_types,
                $start,
                $end,
                $location_id,
                $created_by
            );

            $total_purchase_inc_tax = ! empty($purchase_details['total_purchase_inc_tax']) ? $purchase_details['total_purchase_inc_tax'] : 0;
            $total_purchase_return_inc_tax = $transaction_totals['total_purchase_return_inc_tax'];

            $output = $purchase_details;
            $output['total_purchase'] = $total_purchase_inc_tax;
            $output['total_purchase_return'] = $total_purchase_return_inc_tax;
            $output['total_purchase_return_paid'] = $this->transactionUtil->getTotalPurchaseReturnPaid($business_id, $start, $end, $location_id);

            $total_sell_inc_tax = ! empty($sell_details['total_sell_inc_tax']) ? $sell_details['total_sell_inc_tax'] : 0;
            $total_sell_return_inc_tax = ! empty($transaction_totals['total_sell_return_inc_tax']) ? $transaction_totals['total_sell_return_inc_tax'] : 0;
            $output['total_sell_return_paid'] = $this->transactionUtil->getTotalSellReturnPaid($business_id, $start, $end, $location_id);

            $output['total_sell'] = $total_sell_inc_tax;
            $output['total_sell_return'] = $total_sell_return_inc_tax;

            $output['invoice_due'] = $sell_details['invoice_due'] - $total_ledger_discount['total_sell_discount'];
            $output['total_expense'] = $transaction_totals['total_expense'];

            //NET = TOTAL SALES - INVOICE DUE - EXPENSE
            $output['net'] = $output['total_sell'] - $output['invoice_due'] - $output['total_expense'];

            // Online / ecommerce orders in selected period
            $onlineOrdersQuery = \App\Transaction::where('business_id', $business_id)
                ->where('type', 'sell')
                ->where('source', 'ecommerce')
                ->whereBetween(\DB::raw('date(transaction_date)'), [$start, $end]);

            if (! empty($location_id)) {
                $onlineOrdersQuery->where('location_id', $location_id);
            }
            if (! empty($created_by)) {
                $onlineOrdersQuery->where('created_by', $created_by);
            }

            $output['online_orders'] = (int) (clone $onlineOrdersQuery)->count();
            $output['online_orders_amount'] = (float) (clone $onlineOrdersQuery)
                ->where('status', 'final')
                ->sum('final_total');

            return $output;
        }
    }

    /**
     * Retrieves sell products whose available quntity is less than alert quntity.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProductStockAlert()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $permitted_locations = auth()->user()->permitted_locations();
            $products = $this->productUtil->getProductAlert($business_id, $permitted_locations);

            return Datatables::of($products)
                ->editColumn('product', function ($row) {
                    if ($row->type == 'single') {
                        return $row->product.' ('.$row->sku.')';
                    } else {
                        return $row->product.' - '.$row->product_variation.' - '.$row->variation.' ('.$row->sub_sku.')';
                    }
                })
                ->editColumn('stock', function ($row) {
                    $stock = $row->stock ? $row->stock : 0;

                    return '<span data-is_quantity="true" data-orig-value="'.(float) $stock.'" class="display_currency" data-currency_symbol=false>'.(float) $stock.'</span> '.$row->unit;
                })
                ->removeColumn('sku')
                ->removeColumn('sub_sku')
                ->removeColumn('unit')
                ->removeColumn('type')
                ->removeColumn('product_variation')
                ->removeColumn('variation')
                ->rawColumns([2])
                ->make(false);
        }
    }

    /**
     * Retrieves payment dues for the purchases.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPurchasePaymentDues()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $today = \Carbon::now()->format('Y-m-d H:i:s');

            $query = Transaction::join(
                'contacts as c',
                'transactions.contact_id',
                '=',
                'c.id'
            )
                    ->leftJoin(
                        'transaction_payments as tp',
                        'transactions.id',
                        '=',
                        'tp.transaction_id'
                    )
                    ->where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'purchase')
                    ->where('transactions.payment_status', '!=', 'paid')
                    ->whereRaw("DATEDIFF( DATE_ADD( transaction_date, INTERVAL IF(transactions.pay_term_type = 'days', transactions.pay_term_number, 30 * transactions.pay_term_number) DAY), '$today') <= 7");

            //Check for permitted locations of a user
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('transactions.location_id', $permitted_locations);
            }

            if (! empty(request()->input('location_id'))) {
                $query->where('transactions.location_id', request()->input('location_id'));
            }

            $dues = $query->select(
                'transactions.id as id',
                'c.name as supplier',
                'c.supplier_business_name',
                'ref_no',
                'final_total',
                DB::raw('SUM(tp.amount) as total_paid')
            )
                        ->groupBy('transactions.id');

            return Datatables::of($dues)
                ->addColumn('due', function ($row) {
                    $total_paid = ! empty($row->total_paid) ? $row->total_paid : 0;
                    $due = $row->final_total - $total_paid;

                    return '<span class="display_currency" data-currency_symbol="true">'.
                    $due.'</span>';
                })
                ->addColumn('action', '@can("purchase.create") <a href="{{action([\App\Http\Controllers\TransactionPaymentController::class, \'addPayment\'], [$id])}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-accent add_payment_modal"><i class="fas fa-money-bill-alt"></i> @lang("purchase.add_payment")</a> @endcan')
                ->removeColumn('supplier_business_name')
                ->editColumn('supplier', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br> @endif {{$supplier}}')
                ->editColumn('ref_no', function ($row) {
                    if (auth()->user()->can('purchase.view')) {
                        return  '<a href="#" data-href="'.action([\App\Http\Controllers\PurchaseController::class, 'show'], [$row->id]).'"
                                    class="btn-modal" data-container=".view_modal">'.$row->ref_no.'</a>';
                    }

                    return $row->ref_no;
                })
                ->removeColumn('id')
                ->removeColumn('final_total')
                ->removeColumn('total_paid')
                ->rawColumns([0, 1, 2, 3])
                ->make(false);
        }
    }

    /**
     * Retrieves payment dues for the purchases.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSalesPaymentDues()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $today = \Carbon::now()->format('Y-m-d H:i:s');

            $query = Transaction::join(
                'contacts as c',
                'transactions.contact_id',
                '=',
                'c.id'
            )
                    ->leftJoin(
                        'transaction_payments as tp',
                        'transactions.id',
                        '=',
                        'tp.transaction_id'
                    )
                    ->where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'sell')
                    ->where('transactions.payment_status', '!=', 'paid')
                    ->whereNotNull('transactions.pay_term_number')
                    ->whereNotNull('transactions.pay_term_type')
                    ->whereRaw("DATEDIFF( DATE_ADD( transaction_date, INTERVAL IF(transactions.pay_term_type = 'days', transactions.pay_term_number, 30 * transactions.pay_term_number) DAY), '$today') <= 7");

            //Check for permitted locations of a user
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('transactions.location_id', $permitted_locations);
            }

            if (! empty(request()->input('location_id'))) {
                $query->where('transactions.location_id', request()->input('location_id'));
            }

            $dues = $query->select(
                'transactions.id as id',
                'c.name as customer',
                'c.supplier_business_name',
                'transactions.invoice_no',
                'final_total',
                DB::raw('SUM(tp.amount) as total_paid')
            )
                        ->groupBy('transactions.id');

            return Datatables::of($dues)
                ->addColumn('due', function ($row) {
                    $total_paid = ! empty($row->total_paid) ? $row->total_paid : 0;
                    $due = $row->final_total - $total_paid;

                    return '<span class="display_currency" data-currency_symbol="true">'.
                    $due.'</span>';
                })
                ->editColumn('invoice_no', function ($row) {
                    if (auth()->user()->can('sell.view')) {
                        return  '<a href="#" data-href="'.action([\App\Http\Controllers\SellController::class, 'show'], [$row->id]).'"
                                    class="btn-modal" data-container=".view_modal">'.$row->invoice_no.'</a>';
                    }

                    return $row->invoice_no;
                })
                ->addColumn('action', '@if(auth()->user()->can("sell.create") || auth()->user()->can("direct_sell.access")) <a href="{{action([\App\Http\Controllers\TransactionPaymentController::class, \'addPayment\'], [$id])}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-accent add_payment_modal"><i class="fas fa-money-bill-alt"></i> @lang("purchase.add_payment")</a> @endif')
                ->editColumn('customer', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br> @endif {{$customer}}')
                ->removeColumn('supplier_business_name')
                ->removeColumn('id')
                ->removeColumn('final_total')
                ->removeColumn('total_paid')
                ->rawColumns([0, 1, 2, 3])
                ->make(false);
        }
    }

    /**
     * Zoho-style dashboard right panel: pending actions + recent activities.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDashboardSidePanel()
    {
        if (! request()->ajax()) {
            abort(404);
        }

        $business_id = request()->session()->get('user.business_id');
        $location_id = request()->input('location_id');
        $permitted_locations = auth()->user()->permitted_locations();
        $enabled_modules = ! empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];

        $apply_location = function ($query) use ($permitted_locations, $location_id) {
            if ($permitted_locations != 'all') {
                $query->whereIn('transactions.location_id', $permitted_locations);
            }
            if (! empty($location_id)) {
                $query->where('transactions.location_id', $location_id);
            }

            return $query;
        };

        $groups = [];

        // ── PURCHASES ──
        if (auth()->user()->can('purchase.view') || auth()->user()->can('view_own_purchase')) {
            $items = [];

            if (request()->session()->get('business.enable_purchase_status') == 1) {
                $to_receive = $apply_location(
                    Transaction::where('transactions.business_id', $business_id)
                        ->where('transactions.type', 'purchase')
                        ->whereIn('transactions.status', ['ordered', 'pending'])
                )->count();

                $items[] = [
                    'label' => __('lang_v1.to_be_received'),
                    'count' => $to_receive,
                    'url' => action([\App\Http\Controllers\PurchaseController::class, 'index']),
                ];
            }

            $purchase_due = $apply_location(
                Transaction::where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'purchase')
                    ->whereIn('transactions.payment_status', ['due', 'partial'])
            )->count();

            $items[] = [
                'label' => __('home.purchase_due'),
                'count' => $purchase_due,
                'url' => action([\App\Http\Controllers\PurchaseController::class, 'index']),
            ];

            if (! empty($items)) {
                $groups[] = [
                    'title' => __('lang_v1.menu_purchases'),
                    'icon' => 'purchase',
                    'items' => $items,
                ];
            }
        }

        // ── SALES ──
        if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view') || auth()->user()->can('view_own_sell_only')) {
            $items = [];

            $invoice_due = $apply_location(
                Transaction::where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'sell')
                    ->where('transactions.status', 'final')
                    ->whereIn('transactions.payment_status', ['due', 'partial'])
            )->count();

            $items[] = [
                'label' => __('home.invoice_due'),
                'count' => $invoice_due,
                'url' => action([\App\Http\Controllers\SellController::class, 'index']),
            ];

            $to_ship = $apply_location(
                Transaction::where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'sell')
                    ->where('transactions.status', 'final')
                    ->whereNotNull('transactions.shipping_status')
                    ->whereNotIn('transactions.shipping_status', ['delivered', 'cancelled'])
            )->count();

            $items[] = [
                'label' => __('lang_v1.to_be_shipped'),
                'count' => $to_ship,
                'url' => action([\App\Http\Controllers\SellController::class, 'shipments']),
            ];

            $groups[] = [
                'title' => __('lang_v1.menu_sales'),
                'icon' => 'sale',
                'items' => $items,
            ];
        }

        // ── INVENTORY ──
        $inventory_items = [];

        if (auth()->user()->can('product.view') || auth()->user()->can('product.view_own')) {
            // Count via subquery — avoid loading all alert rows into PHP
            $alert_sub = $this->productUtil->getProductAlert($business_id, $permitted_locations);
            $alert_count = DB::query()->fromSub($alert_sub->toBase(), 'product_alerts')->count();

            $inventory_items[] = [
                'label' => __('lang_v1.below_alert_quantity'),
                'count' => $alert_count,
                'url' => action([\App\Http\Controllers\ReportController::class, 'getStockReport']),
            ];
        }

        if (session('business.enable_product_expiry') == 1 && auth()->user()->can('stock_report.view')) {
            $exp_date_filter = \Carbon::now()
                ->addDays(carbon_numeric(session('business.stock_expiry_alert_days', 30)))
                ->format('Y-m-d');

            $expiry_query = \App\PurchaseLine::leftjoin('transactions as t', 'purchase_lines.transaction_id', '=', 't.id')
                ->leftjoin('products as p', 'purchase_lines.product_id', '=', 'p.id')
                ->where('t.business_id', $business_id)
                ->where('p.enable_stock', 1)
                ->whereNotNull('purchase_lines.exp_date')
                ->where('purchase_lines.exp_date', '<=', $exp_date_filter);

            if ($permitted_locations != 'all') {
                $expiry_query->whereIn('t.location_id', $permitted_locations);
            }
            if (! empty($location_id)) {
                $expiry_query->where('t.location_id', $location_id);
            }

            $expiry_sub = $expiry_query->select(
                DB::raw('SUM(COALESCE(quantity, 0) - COALESCE(quantity_sold, 0) - COALESCE(quantity_adjusted, 0) - COALESCE(quantity_returned, 0)) as stock_left')
            )
                ->having('stock_left', '>', 0)
                ->groupBy('purchase_lines.variation_id')
                ->groupBy('purchase_lines.exp_date')
                ->groupBy('purchase_lines.lot_number');

            $expiry_count = DB::query()->fromSub($expiry_sub->toBase(), 'expiry_rows')->count();

            $inventory_items[] = [
                'label' => __('home.stock_expiry_alert'),
                'count' => $expiry_count,
                'url' => action([\App\Http\Controllers\ReportController::class, 'getStockExpiryReport']),
            ];
        }

        if (in_array('stock_transfers', $enabled_modules) && (auth()->user()->can('stock_transfer.view') || auth()->user()->can('stock_transfer.view_own'))) {
            $transfer_progress = Transaction::where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell_transfer')
                ->whereIn('transactions.status', ['pending', 'in_transit']);

            if ($permitted_locations != 'all') {
                $transfer_progress->whereIn('transactions.location_id', $permitted_locations);
            }
            if (! empty($location_id)) {
                $transfer_progress->where('transactions.location_id', $location_id);
            }

            $inventory_items[] = [
                'label' => __('lang_v1.transfer_in_progress'),
                'count' => $transfer_progress->count(),
                'url' => action([\App\Http\Controllers\StockTransferController::class, 'index']),
            ];
        }

        if (! empty($inventory_items)) {
            $groups[] = [
                'title' => __('lang_v1.menu_inventory'),
                'icon' => 'inventory',
                'items' => $inventory_items,
            ];
        }

        // ── Recent activities ──
        $activities = \Spatie\Activitylog\Models\Activity::with(['causer', 'subject'])
            ->where('business_id', $business_id)
            ->latest()
            ->limit(8)
            ->get();

        $pending_html = view('home.partials.pending_actions', compact('groups'))->render();
        $activities_html = view('home.partials.recent_activities', compact('activities'))->render();

        $attention_items = [];
        foreach ($groups as $group) {
            foreach ($group['items'] as $item) {
                $attention_items[] = $item;
            }
        }

        return response()->json([
            'pending_html' => $pending_html,
            'activities_html' => $activities_html,
            'attention_items' => $attention_items,
        ]);
    }

    /**
     * Payment method breakdown for dashboard donut (sales only).
     * Includes paid methods + unpaid credit-sale dues.
     */
    public function getPaymentMethodMix()
    {
        if (! request()->ajax()) {
            abort(404);
        }

        $start = request()->input('start', \Carbon::now()->subDays(29)->format('Y-m-d'));
        $end = request()->input('end', \Carbon::now()->format('Y-m-d'));
        $location_id = request()->input('location_id');
        $business_id = request()->session()->get('user.business_id');
        $permitted_locations = auth()->user()->permitted_locations();

        $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);

        $paymentsQuery = \App\TransactionPayment::join('transactions as t', 'transaction_payments.transaction_id', '=', 't.id')
            ->where('transaction_payments.business_id', $business_id)
            ->whereNull('transaction_payments.parent_id')
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->whereBetween(\DB::raw('date(t.transaction_date)'), [$start, $end]);

        if ($permitted_locations != 'all') {
            $paymentsQuery->whereIn('t.location_id', $permitted_locations);
        }
        if (! empty($location_id)) {
            $paymentsQuery->where('t.location_id', $location_id);
        }

        $rows = $paymentsQuery
            ->select('transaction_payments.method', \DB::raw('SUM(transaction_payments.amount) as total'))
            ->groupBy('transaction_payments.method')
            ->orderByDesc('total')
            ->get();

        $series = [];
        $grand_total = 0;
        foreach ($rows as $row) {
            $amount = (float) $row->total;
            if ($amount <= 0) {
                continue;
            }
            $grand_total += $amount;
            $series[] = [
                'method' => $row->method,
                'label' => $payment_types[$row->method] ?? ucfirst(str_replace('_', ' ', $row->method)),
                'amount' => $amount,
            ];
        }

        // Credit sales / credit line: unpaid (due/partial) amount has no payment row
        $creditQuery = \App\Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->whereIn('payment_status', ['due', 'partial'])
            ->whereBetween(\DB::raw('date(transaction_date)'), [$start, $end]);

        if ($permitted_locations != 'all') {
            $creditQuery->whereIn('location_id', $permitted_locations);
        }
        if (! empty($location_id)) {
            $creditQuery->where('location_id', $location_id);
        }

        $credit_due = (float) $creditQuery
            ->selectRaw('COALESCE(SUM(final_total - COALESCE((
                SELECT SUM(tp.amount)
                FROM transaction_payments tp
                WHERE tp.transaction_id = transactions.id
                  AND tp.parent_id IS NULL
            ), 0)), 0) as credit_due')
            ->value('credit_due');

        if ($credit_due > 0) {
            $grand_total += $credit_due;
            $series[] = [
                'method' => 'credit_sale',
                'label' => __('home.credit_sale_short'),
                'amount' => $credit_due,
            ];
        }

        usort($series, function ($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });

        return response()->json([
            'series' => $series,
            'total' => $grand_total,
            'has_data' => $grand_total > 0,
        ]);
    }

    public function loadMoreNotifications()
    {
        $notifications = auth()->user()->notifications()->orderBy('created_at', 'DESC')->paginate(10);

        if (request()->input('page') == 1) {
            auth()->user()->unreadNotifications->markAsRead();
        }
        $notifications_data = $this->commonUtil->parseNotifications($notifications);

        return view('layouts.partials.notification_list', compact('notifications_data'));
    }

    /**
     * Function to count total number of unread notifications
     *
     * @return json
     */
    public function getTotalUnreadNotifications()
    {
        $unread_notifications = auth()->user()->unreadNotifications;
        $total_unread = $unread_notifications->count();

        $notification_html = '';
        $modal_notifications = [];
        foreach ($unread_notifications as $unread_notification) {
            if (isset($data['show_popup'])) {
                $modal_notifications[] = $unread_notification;
                $unread_notification->markAsRead();
            }
        }
        if (! empty($modal_notifications)) {
            $notification_html = view('home.notification_modal')->with(['notifications' => $modal_notifications])->render();
        }

        return [
            'total_unread' => $total_unread,
            'notification_html' => $notification_html,
        ];
    }

    private function __chartOptions($title)
    {
        return [
            'chart' => [
                'backgroundColor' => 'transparent',
                'spacingTop' => 20,
                'spacingRight' => 20,
                'spacingBottom' => 14,
                'spacingLeft' => 10,
                'style' => [
                    'fontFamily' => 'Inter, "Segoe UI Variable", "Segoe UI", Roboto, Helvetica, Arial, sans-serif',
                ],
            ],
            'colors' => [
                '#4f8fce',
                '#4db39e',
                '#d5aa35',
                '#d9597b',
                '#7c72c9',
                '#4b9ab3',
            ],
            'title' => [
                'text' => null,
            ],
            'credits' => [
                'enabled' => false,
            ],
            'xAxis' => [
                'lineColor' => '#dfe7f1',
                'tickColor' => '#dfe7f1',
                'labels' => [
                    'style' => [
                        'color' => '#64748b',
                        'fontSize' => '11px',
                        'fontWeight' => '500',
                    ],
                ],
            ],
            'yAxis' => [
                'title' => [
                    'text' => $title,
                    'style' => [
                        'color' => '#475569',
                        'fontSize' => '12px',
                        'fontWeight' => '600',
                    ],
                ],
                'gridLineColor' => '#e5e7eb',
                'gridLineDashStyle' => 'Solid',
                'gridLineWidth' => 1,
                'labels' => [
                    'style' => [
                        'color' => '#64748b',
                        'fontSize' => '11px',
                        'fontWeight' => '500',
                    ],
                ],
            ],
            'legend' => [
                'align' => 'right',
                'verticalAlign' => 'top',
                'floating' => false,
                'layout' => 'horizontal',
                'padding' => 8,
                'itemStyle' => [
                    'color' => '#334155',
                    'fontSize' => '12px',
                    'fontWeight' => '600',
                ],
                'itemHoverStyle' => [
                    'color' => '#386fa8',
                ],
            ],
            'tooltip' => [
                'backgroundColor' => '#0f172a',
                'borderColor' => '#0f172a',
                'borderRadius' => 10,
                'shadow' => true,
                'style' => [
                    'color' => '#f8fafc',
                    'fontSize' => '12px',
                ],
            ],
            'plotOptions' => [
                'series' => [
                    'lineWidth' => 3,
                    'marker' => [
                        'enabled' => false,
                        'radius' => 4,
                        'states' => [
                            'hover' => [
                                'enabled' => true,
                                'radius' => 5,
                            ],
                        ],
                    ],
                    'states' => [
                        'hover' => [
                            'lineWidthPlus' => 1,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getCalendar()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->restUtil->is_admin(auth()->user(), $business_id);
        $is_superadmin = auth()->user()->can('superadmin');
        if (request()->ajax()) {
            $data = [
                'start_date' => request()->start,
                'end_date' => request()->end,
                'user_id' => ($is_admin || $is_superadmin) && ! empty(request()->user_id) ? request()->user_id : auth()->user()->id,
                'location_id' => ! empty(request()->location_id) ? request()->location_id : null,
                'business_id' => $business_id,
                'events' => request()->events ?? [],
                'color' => '#007FFF',
            ];
            $events = [];

            if (in_array('bookings', $data['events'])) {
                $events = $this->restUtil->getBookingsForCalendar($data);
            }

            $module_events = $this->moduleUtil->getModuleData('calendarEvents', $data);

            foreach ($module_events as $module_event) {
                $events = array_merge($events, $module_event);
            }

            return $events;
        }

        $all_locations = BusinessLocation::forDropdown($business_id)->toArray();
        $users = [];
        if ($is_admin) {
            $users = User::forDropdown($business_id, false);
        }

        $event_types = [
            'bookings' => [
                'label' => __('restaurant.bookings'),
                'color' => '#007FFF',
            ],
        ];
        $module_event_types = $this->moduleUtil->getModuleData('eventTypes');
        foreach ($module_event_types as $module_event_type) {
            $event_types = array_merge($event_types, $module_event_type);
        }

        return view('home.calendar')->with(compact('all_locations', 'users', 'event_types'));
    }

    public function showNotification($id)
    {
        $notification = DatabaseNotification::find($id);

        $data = $notification->data;

        $notification->markAsRead();

        return view('home.notification_modal')->with([
            'notifications' => [$notification],
        ]);
    }

    public function attachMediasToGivenModel(Request $request)
    {
        if ($request->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $model_id = $request->input('model_id');
                $model = $request->input('model_type');
                $model_media_type = $request->input('model_media_type');

                DB::beginTransaction();

                //find model to which medias are to be attached
                $model_to_be_attached = $model::where('business_id', $business_id)
                                        ->findOrFail($model_id);

                Media::uploadMedia($business_id, $model_to_be_attached, $request, 'file', false, $model_media_type);

                DB::commit();

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.success'),
                ];
            } catch (Exception $e) {
                DB::rollBack();

                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    public function getUserLocation($latlng)
    {
        $latlng_array = explode(',', $latlng);

        $response = $this->moduleUtil->getLocationFromCoordinates($latlng_array[0], $latlng_array[1]);

        return ['address' => $response];
    }

    /**
     * Sales order summary chart data for dashboard (quantity or value by date).
     *
     * @return \Illuminate\Http\Response
     */
    public function getSalesOrderSummary(Request $request)
    {
        if (! auth()->user()->can('so.view_all') && ! auth()->user()->can('so.view_own')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $period = $request->input('period', 'this_month');
        $metric = $request->input('metric', 'quantity');
        $location_id = $request->input('location_id');

        [$start, $end] = $this->__salesOrderSummaryDateRange($period);

        $query = Transaction::where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sales_order')
            ->whereBetween('transactions.transaction_date', [
                $start->copy()->startOfDay()->toDateTimeString(),
                $end->copy()->endOfDay()->toDateTimeString(),
            ]);

        if (! empty($location_id)) {
            $query->where('transactions.location_id', $location_id);
        }

        if (! auth()->user()->can('so.view_all') && auth()->user()->can('so.view_own')) {
            $query->where('transactions.created_by', auth()->user()->id);
        }

        if ($metric === 'value') {
            $daily = (clone $query)
                ->selectRaw('DATE(transactions.transaction_date) as order_date, SUM(transactions.final_total) as total')
                ->groupBy('order_date')
                ->pluck('total', 'order_date');
        } else {
            $daily = (clone $query)
                ->join('transaction_sell_lines as tsl', 'tsl.transaction_id', '=', 'transactions.id')
                ->selectRaw('DATE(transactions.transaction_date) as order_date, SUM(tsl.quantity) as total')
                ->groupBy('order_date')
                ->pluck('total', 'order_date');
        }

        $labels = [];
        $values = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $date_key = $cursor->format('Y-m-d');
            $labels[] = $cursor->format('d M');
            $values[] = (float) ($daily[$date_key] ?? 0);
            $cursor->addDay();
        }

        $has_data = array_sum($values) > 0;
        $label_step = in_array($period, ['this_month', 'last_month', 'last_30_days', 'this_year'], true) ? 2 : 1;

        $currency = Currency::where('id', $request->session()->get('business.currency_id'))->first();

        return response()->json([
            'labels' => $labels,
            'values' => $values,
            'has_data' => $has_data,
            'label_step' => $label_step,
            'metric' => $metric,
            'currency_symbol' => $currency->symbol ?? '',
            'empty_message' => __('lang_v1.no_sales_orders_in_period'),
        ]);
    }

    /**
     * Resolve date range for sales order summary chart.
     *
     * @return array
     */
    private function __salesOrderSummaryDateRange($period)
    {
        $now = \Carbon::now();

        switch ($period) {
            case 'last_month':
                $start = $now->copy()->subMonth()->startOfMonth();
                $end = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'this_week':
                $start = $now->copy()->startOfWeek();
                $end = $now->copy()->endOfDay();
                break;
            case 'last_30_days':
                $start = $now->copy()->subDays(29)->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'this_year':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfDay();
                break;
            case 'this_month':
            default:
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;
        }

        return [$start, $end];
    }
}
