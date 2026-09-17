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
     * Home operations dashboard (store overview).
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
        $this->businessUtil->createDefaultSalesRole((int) $business_id);

        $is_admin = $this->businessUtil->is_admin($user);

        if (! $user->can('dashboard.data')) {
            return view('home.index', [
                'is_admin' => $is_admin,
                'all_locations' => [],
                'display_name' => $this->homeDisplayName($user),
            ]);
        }

        $all_locations = BusinessLocation::forDropdown($business_id)->toArray();

        return view('home.index', [
            'is_admin' => $is_admin,
            'all_locations' => $all_locations,
            'display_name' => $this->homeDisplayName($user),
        ]);
    }

    /**
     * Sales menu — Sales & Fulfillment Analytics dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function salesDashboard()
    {
        $user = auth()->user();
        if ($user->user_type == 'user_customer') {
            return redirect()->action([\Modules\Crm\Http\Controllers\DashboardController::class, 'index']);
        }

        $business_id = request()->session()->get('user.business_id');

        $is_admin = $this->businessUtil->is_admin(auth()->user());

        // Cloned / reset sites: always keep Sales Representative in the role list
        $this->businessUtil->createDefaultSalesRole((int) $business_id);

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
        $permitted_locations = auth()->user()->permitted_locations();
        $widgets_start = \Carbon::now()->subDays(29)->format('Y-m-d');
        $widgets_end = \Carbon::now()->format('Y-m-d');
        $daily_orders_revenue = $this->getDailyOrdersAndRevenue($business_id, $widgets_start, $widgets_end, $permitted_locations);

        //Chart for sells last 30 days (POS + Online Store + Field Sales)
        $labels = [];
        $all_sell_values = [];
        $dates = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = \Carbon::now()->subDays($i)->format('Y-m-d');
            $dates[] = $date;

            $labels[] = date('j M Y', strtotime($date));

            $row = $daily_orders_revenue->get($date);
            $all_sell_values[] = $row ? (float) $row->revenue : 0;
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

        // Sales chart: soft blue area trend (matches analytics mock)
        $sells_chart_1_options['chart']['type'] = 'areaspline';
        $sells_chart_1_options['colors'] = ['#3b82f6'];
        $sells_chart_1_options['yAxis']['title']['text'] = null;
        $sells_chart_1_options['yAxis']['gridLineColor'] = '#eef2f7';
        $sells_chart_1_options['yAxis']['gridLineDashStyle'] = 'Solid';
        $sells_chart_1_options['yAxis']['gridLineWidth'] = 1;
        $sells_chart_1_options['yAxis']['min'] = 0;
        $sells_chart_1_options['xAxis']['labels']['rotation'] = -30;
        $sells_chart_1_options['legend']['enabled'] = false;
        $sells_chart_1_options['plotOptions'] = [
            'areaspline' => [
                'fillOpacity' => 0.22,
                'lineWidth' => 2.75,
                'marker' => [
                    'enabled' => true,
                    'radius' => 3.5,
                    'symbol' => 'circle',
                    'lineWidth' => 2,
                    'lineColor' => '#ffffff',
                    'states' => ['hover' => ['enabled' => true, 'radius' => 5]],
                ],
            ],
            'series' => [
                'type' => 'areaspline',
                'borderWidth' => 0,
                'color' => '#3b82f6',
                'fillColor' => [
                    'linearGradient' => ['x1' => 0, 'y1' => 0, 'x2' => 0, 'y2' => 1],
                    'stops' => [
                        [0, 'rgba(59, 130, 246, 0.28)'],
                        [1, 'rgba(59, 130, 246, 0.02)'],
                    ],
                ],
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
        $sells_chart_1->dataset(__('home.total_sell'), 'areaspline', $all_sell_values);

        $labels = [];
        $values = [];
        $date = strtotime($fy['start']);
        $last = date('m-Y', strtotime($fy['end']));
        $fy_months = [];
        do {
            $month_year = date('m-Y', $date);
            $fy_months[] = $month_year;

            $labels[] = \Carbon::createFromFormat('m-Y', $month_year)
                            ->format('M');
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
        $sells_chart_2_options['yAxis']['gridLineColor'] = '#eef2f7';
        $sells_chart_2_options['yAxis']['gridLineDashStyle'] = 'Solid';
        $sells_chart_2_options['yAxis']['gridLineWidth'] = 1;
        $sells_chart_2_options['yAxis']['min'] = 0;
        $sells_chart_2_options['xAxis']['labels']['rotation'] = 0;
        $sells_chart_2_options['legend']['enabled'] = false;
        $sells_chart_2_options['plotOptions'] = [
            'column' => [
                'borderWidth' => 0,
                'borderRadius' => 14,
                'color' => '#3b82f6',
                'pointPadding' => 0.01,
                'groupPadding' => 0.02,
                'maxPointWidth' => 58,
            ],
            'series' => [
                'type' => 'column',
                'borderWidth' => 0,
            ],
        ];
        $sells_chart_2->labels($labels)
                    ->options($sells_chart_2_options);
        $sells_chart_2->dataset(__('home.total_sell'), 'column', $values);

        //── Orders / Fulfillment analytics widgets (fixed last-30-days window) ──
        $orders_by_channel_cache_key = 'home_orders_by_channel_v2_'.$business_id.'_'.auth()->id().'_'.$widgets_start.'_'.$widgets_end;
        $orders_rows = Cache::remember($orders_by_channel_cache_key, 180, function () use ($business_id, $widgets_start, $widgets_end, $permitted_locations) {
            return $this->getOrdersCountByChannel($business_id, $widgets_start, $widgets_end, $permitted_locations);
        });

        // Orders Over Time — daily order count by channel, last 30 days
        $orders_dates = [];
        for ($i = 29; $i >= 0; $i--) {
            $orders_dates[] = \Carbon::now()->subDays($i)->format('Y-m-d');
        }

        $channel_meta = [
            'pos' => ['label' => __('home.source_pos'), 'color' => '#3b82f6', 'fill' => 'rgba(59,130,246,0.12)'],
            'online' => ['label' => __('home.source_online'), 'color' => '#8b5cf6', 'fill' => 'rgba(139,92,246,0.12)'],
            'field' => ['label' => __('home.source_field'), 'color' => '#14b8a6', 'fill' => 'rgba(20,184,166,0.12)'],
        ];

        $orders_series_by_channel = [];
        foreach ($channel_meta as $channel => $meta) {
            $values = [];
            $channel_total = 0;
            foreach ($orders_dates as $date) {
                $cnt = (int) $orders_rows->where('channel', $channel)->where('date', $date)->sum('total_orders');
                $values[] = $cnt;
                $channel_total += $cnt;
            }
            $orders_series_by_channel[$channel] = [
                'label' => $meta['label'],
                'values' => $values,
                'total' => $channel_total,
                'color' => $meta['color'],
                'fill' => $meta['fill'],
            ];
        }

        $orders_short_labels = array_map(function ($d) {
            return date('M j', strtotime($d));
        }, $orders_dates);

        $orders_chart = new CommonChart;
        $orders_chart_options = $this->__chartOptions(null);
        $orders_chart_options['chart']['type'] = 'spline';
        $orders_chart_options['yAxis']['title']['text'] = null;
        $orders_chart_options['yAxis']['gridLineColor'] = '#eef2f7';
        $orders_chart_options['yAxis']['min'] = 0;
        $orders_chart_options['xAxis']['labels']['rotation'] = 0;
        $orders_chart_options['xAxis']['tickInterval'] = 7;
        $orders_chart_options['legend']['enabled'] = true;
        $orders_chart_options['legend']['align'] = 'left';
        $orders_chart_options['legend']['verticalAlign'] = 'top';
        $orders_chart_options['legend']['symbolRadius'] = 6;
        $orders_chart_options['plotOptions'] = [
            'spline' => [
                'lineWidth' => 2.5,
                'marker' => [
                    'enabled' => true,
                    'radius' => 3.5,
                    'symbol' => 'circle',
                    'lineWidth' => 2,
                    'lineColor' => '#ffffff',
                    'states' => ['hover' => ['enabled' => true, 'radius' => 5]],
                ],
            ],
            'series' => ['borderWidth' => 0, 'animation' => false],
        ];
        $orders_chart->height(260);
        $orders_chart->labels($orders_short_labels)->options($orders_chart_options);
        foreach ($orders_series_by_channel as $series) {
            $orders_chart->dataset($series['label'], 'spline', $series['values'])
                ->options([
                    'color' => $series['color'],
                    'marker' => ['fillColor' => $series['color']],
                ]);
        }

        // Orders by Source (donut) — always show POS / Online Store / Field Sales
        $orders_by_location = [];
        $orders_by_location_total = 0;
        foreach ($orders_series_by_channel as $channel => $series) {
            $orders_by_location[] = [
                'label' => $series['label'],
                'count' => $series['total'],
                'color' => $series['color'],
                'channel' => $channel,
            ];
            $orders_by_location_total += $series['total'];
        }
        foreach ($orders_by_location as &$loc_row) {
            $loc_row['pct'] = $orders_by_location_total > 0 ? round(($loc_row['count'] / $orders_by_location_total) * 100, 1) : 0;
        }
        unset($loc_row);

        $location_donut = new CommonChart;
        $location_donut_options = $this->__chartOptions(null);
        $location_donut_options['chart']['type'] = 'pie';
        $location_donut_options['chart']['spacing'] = [4, 4, 4, 4];
        $location_donut_options['chart']['margin'] = [0, 0, 0, 0];
        $location_donut_options['legend']['enabled'] = false;
        $location_donut_options['plotOptions'] = [
            'pie' => [
                'size' => '100%',
                'innerSize' => '70%',
                'borderWidth' => 2,
                'borderColor' => '#ffffff',
                'dataLabels' => ['enabled' => false],
                'states' => ['hover' => ['brightness' => 0.06]],
                'slicedOffset' => 0,
            ],
        ];
        $location_donut->height(140);
        $location_donut->options($location_donut_options);
        if ($orders_by_location_total > 0) {
            $location_donut->labels(array_column($orders_by_location, 'label'))
                ->dataset(__('home.total_orders'), 'pie', array_column($orders_by_location, 'count'))
                ->options(['color' => array_column($orders_by_location, 'color')]);
        } else {
            $location_donut->labels([__('lang_v1.no_data')])
                ->dataset(__('home.total_orders'), 'pie', [1])
                ->options(['color' => ['#e5e7eb']]);
        }

        // Fulfillment Pipeline (last 30 days snapshot)
        $fulfillment_pipeline = $this->buildFulfillmentPipeline($business_id, $permitted_locations, $widgets_start, $widgets_end);
        $fulfillment_pipeline_max = max(1, max($fulfillment_pipeline));

        // Top Products (last 30 days)
        $top_products = $this->productUtil->getTrendingProducts($business_id, [
            'limit' => 8,
            'start_date' => $widgets_start,
            'end_date' => $widgets_end,
        ]);
        $top_products_max = $top_products->max('total_unit_sold') ?: 1;

        // Location Performance mini stats (last 30 days)
        $location_performance = $this->buildLocationPerformance($business_id, $permitted_locations, $all_locations, $widgets_start, $widgets_end);

        // KPI sparklines (last 14 days from the same 30-day channel series)
        $daily_payment_hours = $this->getDailyAvgPaymentHours($business_id, \Carbon::now()->subDays(13)->format('Y-m-d'), $widgets_end, $permitted_locations);

        $spark_orders = [];
        $spark_revenue = [];
        $spark_aov = [];
        $spark_payment_hours = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = \Carbon::now()->subDays($i)->format('Y-m-d');
            $row = $daily_orders_revenue->get($date);
            $orders_cnt = $row ? (int) $row->orders_count : 0;
            $revenue_val = $row ? (float) $row->revenue : 0.0;
            $spark_orders[] = $orders_cnt;
            $spark_revenue[] = $revenue_val;
            $spark_aov[] = $orders_cnt > 0 ? round($revenue_val / $orders_cnt, 2) : 0;
            $payment_row = $daily_payment_hours->get($date);
            $spark_payment_hours[] = $payment_row ? round(((float) $payment_row->avg_minutes) / 60, 1) : 0;
        }

        $kpi_sparkline_orders = $this->buildSparkline($spark_orders, '#3b82f6');
        $kpi_sparkline_revenue = $this->buildSparkline($spark_revenue, '#8b5cf6');
        $kpi_sparkline_aov = $this->buildSparkline($spark_aov, '#14b8a6');
        $kpi_sparkline_payment_hours = $this->buildSparkline($spark_payment_hours, '#f97316');

        //Get Dashboard widgets from module
        $module_widgets = $this->moduleUtil->getModuleData('dashboard_widget');

        $widgets = [];

        foreach ($module_widgets as $widget_array) {
            if (! empty($widget_array['position'])) {
                $widgets[$widget_array['position']][] = $widget_array['widget'];
            }
        }

        $common_settings = ! empty(session('business.common_settings')) ? session('business.common_settings') : [];


        return view('home.sales_analytics', compact(
            'sells_chart_1',
            'sells_chart_2',
            'widgets',
            'all_locations',
            'common_settings',
            'is_admin',
            'orders_chart',
            'location_donut',
            'orders_by_location',
            'fulfillment_pipeline',
            'fulfillment_pipeline_max',
            'top_products',
            'top_products_max',
            'location_performance',
            'kpi_sparkline_orders',
            'kpi_sparkline_revenue',
            'kpi_sparkline_aov',
            'kpi_sparkline_payment_hours'
        ));
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

            // Orders / AOV / avg time-to-payment KPIs (POS + Online Store + Field Sales)
            $permitted_locations = auth()->user()->permitted_locations();
            $pos_orders = $this->getOrdersCount($business_id, $start, $end, $location_id, $created_by, $permitted_locations);
            $output['avg_payment_hours'] = $this->getAvgPaymentHours($business_id, $start, $end, $location_id, $permitted_locations);

            $channel_totals = $this->getOnlineAndFieldTotals($business_id, $start, $end, $location_id, $created_by, $permitted_locations);
            $output['online_orders'] = $channel_totals['online_orders'];
            $output['online_orders_amount'] = $channel_totals['online_amount'];
            $field_orders = $channel_totals['field_orders'];
            $field_amount = $channel_totals['field_amount'];

            $output['total_orders'] = $pos_orders + $output['online_orders'] + $field_orders;
            $output['total_sell'] = $total_sell_inc_tax + $output['online_orders_amount'] + $field_amount;
            $output['avg_order_value'] = $output['total_orders'] > 0 ? round($output['total_sell'] / $output['total_orders'], 2) : 0;
            $output['net'] = $output['total_sell'] - $output['invoice_due'] - $output['total_expense'];

            // Period-over-period change for dashboard KPI deltas
            $start_date = \Carbon::parse($start);
            $end_date = \Carbon::parse($end);
            $days = $start_date->diffInDays($end_date) + 1;
            $prev_end = $start_date->copy()->subDay();
            $prev_start = $prev_end->copy()->subDays($days - 1)->format('Y-m-d');
            $prev_end_str = $prev_end->format('Y-m-d');

            $prev_purchase = $this->transactionUtil->getPurchaseTotals($business_id, $prev_start, $prev_end_str, $location_id, $created_by);
            $prev_sell = $this->transactionUtil->getSellTotals($business_id, $prev_start, $prev_end_str, $location_id, $created_by);
            $prev_txn = $this->transactionUtil->getTransactionTotals($business_id, $transaction_types, $prev_start, $prev_end_str, $location_id, $created_by);
            $prev_invoice_due = (float) ($prev_sell['invoice_due'] ?? 0);
            $prev_total_sell = (float) ($prev_sell['total_sell_inc_tax'] ?? 0);
            $prev_total_purchase = (float) ($prev_purchase['total_purchase_inc_tax'] ?? 0);
            $prev_net = $prev_total_sell - $prev_invoice_due - (float) ($prev_txn['total_expense'] ?? 0);

            $prev_total_orders = $this->getOrdersCount($business_id, $prev_start, $prev_end_str, $location_id, $created_by, $permitted_locations);
            $prev_channel = $this->getOnlineAndFieldTotals($business_id, $prev_start, $prev_end_str, $location_id, $created_by, $permitted_locations);
            $prev_total_orders += $prev_channel['online_orders'] + $prev_channel['field_orders'];
            $prev_total_sell += $prev_channel['online_amount'] + $prev_channel['field_amount'];
            $prev_avg_order_value = $prev_total_orders > 0 ? $prev_total_sell / $prev_total_orders : 0;
            $prev_avg_payment_hours = $this->getAvgPaymentHours($business_id, $prev_start, $prev_end_str, $location_id, $permitted_locations);
            $prev_net = $prev_total_sell - $prev_invoice_due - (float) ($prev_txn['total_expense'] ?? 0);

            $output['change_pct'] = [
                'total_sell' => $this->percentChange($output['total_sell'], $prev_total_sell),
                'total_purchase' => $this->percentChange($output['total_purchase'], $prev_total_purchase),
                'invoice_due' => $this->percentChange($output['invoice_due'], $prev_invoice_due),
                'net' => $this->percentChange($output['net'], $prev_net),
                'total_orders' => $this->percentChange($output['total_orders'], $prev_total_orders),
                'avg_order_value' => $this->percentChange($output['avg_order_value'], $prev_avg_order_value),
                'avg_payment_hours' => $this->percentChange($output['avg_payment_hours'], $prev_avg_payment_hours),
            ];
            $output['prev_period_label'] = \Carbon::parse($prev_start)->format('M j').' – '.\Carbon::parse($prev_end_str)->format('M j');

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
                    ->notEcommerce()
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
                    ->notEcommerce()
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
            ->where(function ($q) {
                $q->whereNull('t.source')->orWhere('t.source', '!=', 'ecommerce');
            })
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
            ->notEcommerce()
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

    /**
     * Revenue Insights chart data.
     * Monthly tab = last 30 days (daily bars).
     * Yearly tab = current financial year (month bars).
     */
    public function getRevenueInsights()
    {
        if (! request()->ajax()) {
            abort(404);
        }

        if (! auth()->user()->can('dashboard.data')) {
            abort(403, 'Unauthorized action.');
        }

        $location_id = request()->input('location_id');
        $business_id = request()->session()->get('user.business_id');
        $fy = $this->businessUtil->getCurrentFinancialYear($business_id);

        $dailyEnd = \Carbon::now()->endOfDay();
        $dailyStart = \Carbon::now()->subDays(29)->startOfDay();
        $fyStart = \Carbon::parse($fy['start'])->startOfDay();
        $fyEnd = \Carbon::parse($fy['end'])->endOfDay();

        // Pull enough sell rows for both views in one query
        $rangeStart = $fyStart->lt($dailyStart) ? $fyStart->copy() : $dailyStart->copy();
        $rangeEnd = $fyEnd->gt($dailyEnd) ? $fyEnd->copy() : $dailyEnd->copy();

        $sells = $this->transactionUtil->getSellsCurrentFy(
            $business_id,
            $rangeStart->format('Y-m-d').' 00:00:00',
            $rangeEnd->format('Y-m-d').' 23:59:59'
        );

        if (! empty($location_id)) {
            $sells = $sells->where('location_id', (int) $location_id)->values();
        }

        $byDate = [];
        $byMonth = [];
        foreach ($sells as $row) {
            $d = ! empty($row->date) ? \Carbon::parse($row->date)->format('Y-m-d') : null;
            $ym = $row->yearmonth ?? (! empty($d) ? \Carbon::parse($d)->format('m-Y') : null);
            $amount = (float) ($row->total_sells ?? 0);
            if ($d) {
                $byDate[$d] = ($byDate[$d] ?? 0) + $amount;
            }
            if ($ym) {
                $byMonth[$ym] = ($byMonth[$ym] ?? 0) + $amount;
            }
        }

        // Monthly tab: last 30 days
        $daily_labels = [];
        $daily_values = [];
        $cursor = $dailyStart->copy();
        while ($cursor->lte($dailyEnd->copy()->startOfDay())) {
            $date = $cursor->format('Y-m-d');
            $daily_labels[] = $cursor->format('M j');
            $daily_values[] = (float) ($byDate[$date] ?? 0);
            $cursor->addDay();
        }

        // Yearly tab: financial year months
        $monthly_labels = [];
        $monthly_values = [];
        $monthCursor = $fyStart->copy()->startOfMonth();
        $monthEnd = $fyEnd->copy()->startOfMonth();
        while ($monthCursor->lte($monthEnd)) {
            $ym = $monthCursor->format('m-Y');
            $monthly_labels[] = $monthCursor->format('M');
            $monthly_values[] = (float) ($byMonth[$ym] ?? 0);
            $monthCursor->addMonth();
        }

        return response()->json([
            'daily_labels' => $daily_labels,
            'daily_values' => $daily_values,
            'monthly_labels' => $monthly_labels,
            'monthly_values' => $monthly_values,
            'has_data' => (array_sum($daily_values) > 0) || (array_sum($monthly_values) > 0),
        ]);
    }

    /**
     * Orders by Status: this-calendar-week vs last-calendar-week counts,
     * bucketed into the same 6 pipeline stages as buildFulfillmentPipeline().
     */
    public function getOrdersByStatusWeekly()
    {
        if (! request()->ajax()) {
            abort(404);
        }

        $business_id = request()->session()->get('user.business_id');
        $location_id = request()->input('location_id');
        $permitted_locations = auth()->user()->permitted_locations();

        $this_week_start = \Carbon::now()->startOfWeek()->format('Y-m-d');
        $this_week_end = \Carbon::now()->endOfWeek()->format('Y-m-d');
        $last_week_start = \Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d');
        $last_week_end = \Carbon::now()->subWeek()->endOfWeek()->format('Y-m-d');

        $this_week = $this->buildFulfillmentPipeline($business_id, $permitted_locations, $this_week_start, $this_week_end, $location_id);
        $last_week = $this->buildFulfillmentPipeline($business_id, $permitted_locations, $last_week_start, $last_week_end, $location_id);

        $stage_labels = [
            'draft' => __('sale.draft'),
            'quotation' => __('lang_v1.quotation'),
            'proforma' => __('lang_v1.proforma'),
            'final_due' => __('lang_v1.due'),
            'final_partial' => __('lang_v1.partial'),
            'final_paid' => __('lang_v1.paid'),
        ];

        $this_week_values = array_values($this_week);
        $last_week_values = array_values($last_week);

        return response()->json([
            'categories' => array_values($stage_labels),
            'this_week' => $this_week_values,
            'last_week' => $last_week_values,
            'has_data' => array_sum($this_week_values) + array_sum($last_week_values) > 0,
        ]);
    }

    /**
     * Recent Orders widget for the dashboard — dedicated lightweight datatable
     * (does not touch SellController::index(), which also powers the main Sales list).
     */
    public function getRecentOrders()
    {
        if (! request()->ajax()) {
            abort(404);
        }

        $business_id = request()->session()->get('user.business_id');
        $location_id = request()->input('location_id');
        $permitted_locations = auth()->user()->permitted_locations();

        $start = request()->input('start');
        $end = request()->input('end');

        $sells = $this->dashboardOrdersQuery($business_id, $start, $end, $permitted_locations, $location_id, null, 'transactions')
            ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
            ->select(
                'transactions.id',
                'transactions.invoice_no',
                'transactions.source',
                'transactions.type',
                'transactions.status',
                'transactions.payment_status',
                'transactions.shipping_status',
                'transactions.final_total',
                'transactions.transaction_date',
                'contacts.name',
                'contacts.supplier_business_name'
            )
            ->orderBy('transactions.transaction_date', 'desc');

        return Datatables::of($sells)
            ->addColumn('action', function ($row) {
                return $this->recentOrderLink($row, true);
            })
            ->editColumn('invoice_no', function ($row) {
                $link = $this->recentOrderLink($row, false);

                return $link !== '' ? $link : e($row->invoice_no);
            })
            ->editColumn('name', function ($row) {
                $name = ! empty($row->supplier_business_name) ? $row->supplier_business_name.', '.$row->name : $row->name;

                return e($name);
            })
            ->addColumn('fulfillment_stage', function ($row) {
                $stage = $this->fulfillmentStageForRow($row);

                return '<span class="japs-ops-stage japs-ops-stage--'.$stage['key'].'">'
                    .'<i class="'.$stage['icon'].'"></i> '.e($stage['label'])
                    .'</span>';
            })
            ->addColumn('business_location_badge', function ($row) {
                $source = $row->source ?? '';
                if ($source === 'ecommerce') {
                    return '<span class="japs-dash-source-badge is-online">'.e(__('home.source_online')).'</span>';
                }
                if ($source === 'sale_app') {
                    return '<span class="japs-dash-source-badge is-field">'.e(__('home.source_field')).'</span>';
                }

                return '<span class="japs-dash-source-badge is-pos">'.e(__('home.source_pos')).'</span>';
            })
            ->editColumn('payment_status', function ($row) {
                $payment_status = Transaction::getPaymentStatus($row);
                $cls = 'is-due';
                if ($payment_status === 'paid') {
                    $cls = 'is-paid';
                } elseif (strpos((string) $payment_status, 'partial') !== false) {
                    $cls = 'is-partial';
                }
                $label = trans('lang_v1.'.$payment_status);
                if ($label === 'lang_v1.'.$payment_status) {
                    $label = ucfirst(str_replace('-', ' ', (string) $payment_status));
                }

                return '<span class="japs-dash-status-pill '.$cls.'">'.e($label).'</span>';
            })
            ->editColumn('final_total', function ($row) {
                return '<span class="display_currency" data-currency_symbol="true">'.$row->final_total.'</span>';
            })
            ->editColumn('transaction_date', function ($row) {
                return $this->commonUtil->format_date($row->transaction_date, true);
            })
            ->rawColumns(['action', 'invoice_no', 'business_location_badge', 'payment_status', 'final_total', 'fulfillment_stage'])
            ->make(true);
    }

    /**
     * Home operations dashboard payload (KPIs, attention, fulfillment, trends, low stock).
     */
    public function getHomeOpsOverview()
    {
        $start = request()->input('start', \Carbon::now()->subDays(6)->format('Y-m-d'));
        $end = request()->input('end', \Carbon::now()->format('Y-m-d'));
        $location_id = request()->input('location_id');
        $business_id = request()->session()->get('user.business_id');
        $permitted_locations = auth()->user()->permitted_locations();

        $awaiting = 0;
        $delayed = 0;
        $invoice_due_count = 0;
        $low_stock_count = 0;
        $low_stock = collect();
        $fulfillment = [
            'picking' => 0,
            'packing' => 0,
            'shipment' => 0,
            'by_source' => [
                'pos' => ['picking' => 0, 'packing' => 0, 'shipment' => 0],
                'online' => ['picking' => 0, 'packing' => 0, 'shipment' => 0],
                'field' => ['picking' => 0, 'packing' => 0, 'shipment' => 0],
            ],
        ];
        $labels = [];
        $orders = [];
        $revenue = [];

        try {
            $awaiting = $this->countAwaitingFulfillment($business_id, $permitted_locations, $location_id);
            $delayed = $this->countDelayedShipments($business_id, $permitted_locations, $location_id);
            $invoice_due_count = $this->countInvoiceDue($business_id, $permitted_locations, $location_id);
            $fulfillment = $this->buildOpsFulfillment($business_id, $permitted_locations, $location_id, $start, $end);
        } catch (\Throwable $e) {
            \Log::warning('Home ops overview counts failed: '.$e->getMessage());
        }

        try {
            $low_stock_q = $this->lowStockQuery($business_id, $permitted_locations, $location_id);
            $low_stock_count = (int) (clone $low_stock_q)->count();
            $low_stock = $low_stock_q
                ->orderBy('variation_location_details.qty_available', 'asc')
                ->limit(8)
                ->get()
                ->map(function ($row) {
                    return [
                        'product' => $row->product,
                        'sku' => $row->sub_sku ?: $row->sku,
                        'stock' => (float) $row->stock,
                        'alert_quantity' => (float) ($row->alert_quantity ?? 0),
                    ];
                });
        } catch (\Throwable $e) {
            \Log::warning('Home ops low stock failed: '.$e->getMessage());
        }

        try {
            $daily = $this->getDailyOrdersAndRevenue($business_id, $start, $end, $permitted_locations);
            $cursor = \Carbon::parse($start);
            $last = \Carbon::parse($end);
            while ($cursor->lte($last)) {
                $date = $cursor->format('Y-m-d');
                $labels[] = $cursor->format('M j');
                $row = $daily->get($date);
                $orders[] = $row ? (int) $row->orders_count : 0;
                $revenue[] = $row ? round((float) $row->revenue, 2) : 0;
                $cursor->addDay();
            }
        } catch (\Throwable $e) {
            \Log::warning('Home ops trends failed: '.$e->getMessage());
        }

        $open_issues = $delayed + $invoice_due_count + (int) $low_stock_count;

        $start_date = \Carbon::parse($start);
        $end_date = \Carbon::parse($end);
        $days = max(1, $start_date->diffInDays($end_date) + 1);
        $prev_end = $start_date->copy()->subDay();
        $prev_start = $prev_end->copy()->subDays($days - 1)->format('Y-m-d');
        $prev_end_str = $prev_end->format('Y-m-d');

        $prev_awaiting = 0;
        $prev_open = 0;
        $prev_orders_sum = 0;
        $prev_revenue_sum = 0;
        try {
            $prev_awaiting = $this->countAwaitingFulfillment($business_id, $permitted_locations, $location_id, $prev_start, $prev_end_str);
            $prev_open = $this->countDelayedShipments($business_id, $permitted_locations, $location_id, $prev_start, $prev_end_str)
                + $this->countInvoiceDue($business_id, $permitted_locations, $location_id);
            $prev_daily = $this->getDailyOrdersAndRevenue($business_id, $prev_start, $prev_end_str, $permitted_locations);
            foreach ($prev_daily as $row) {
                $prev_orders_sum += (int) $row->orders_count;
                $prev_revenue_sum += (float) $row->revenue;
            }
        } catch (\Throwable $e) {
            // ignore prior-period comparison errors
        }

        $orders_sum = (int) array_sum($orders);
        $revenue_sum = (float) array_sum($revenue);

        $attention = [
            [
                'label' => __('home.online_awaiting_picking'),
                'count' => $this->safeCount(fn () => $this->countChannelAwaiting($business_id, $permitted_locations, $location_id, 'online')),
                'url' => action([\App\Http\Controllers\SalesOrderController::class, 'index']).'?source=ecommerce',
                'icon' => 'cart',
            ],
            [
                'label' => __('home.shipments_delayed'),
                'count' => $delayed,
                'url' => action([\App\Http\Controllers\SellController::class, 'shipments']),
                'icon' => 'truck',
            ],
            [
                'label' => __('home.low_stock_items'),
                'count' => (int) $low_stock_count,
                'url' => action([\App\Http\Controllers\ReportController::class, 'getStockReport']),
                'icon' => 'box',
            ],
            [
                'label' => __('home.invoice_due'),
                'count' => $invoice_due_count,
                'url' => action([\App\Http\Controllers\SellController::class, 'index']),
                'icon' => 'alert',
            ],
        ];

        return response()->json([
            'kpis' => [
                'awaiting_fulfillment' => $awaiting,
                'open_issues' => $open_issues,
                'change_pct' => [
                    'awaiting_fulfillment' => $this->percentChange($awaiting, $prev_awaiting),
                    'open_issues' => $this->percentChange($open_issues, $prev_open),
                    'orders' => $this->percentChange($orders_sum, $prev_orders_sum),
                    'revenue' => $this->percentChange($revenue_sum, $prev_revenue_sum),
                ],
                'prev_period_label' => \Carbon::parse($prev_start)->format('M j').' – '.\Carbon::parse($prev_end_str)->format('M j'),
            ],
            'attention' => $attention,
            'fulfillment' => $fulfillment,
            'trends' => [
                'labels' => $labels,
                'orders' => $orders,
                'revenue' => $revenue,
            ],
            'low_stock' => $low_stock,
        ]);
    }

    private function safeCount(callable $fn): int
    {
        try {
            return (int) $fn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * View link for a dashboard recent-order row (POS modal vs Online Store page).
     */
    private function recentOrderLink($row, $icon_only = false)
    {
        $source = $row->source ?? '';
        $label = $icon_only
            ? '<i class="fas fa-eye"></i>'
            : e($row->invoice_no);
        $btn_class = $icon_only
            ? 'tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline'
            : 'btn-modal';

        if ($source === 'ecommerce' && \Illuminate\Support\Facades\Route::has('ecommerce.admin.orders.show')) {
            return '<a href="'.e(route('ecommerce.admin.orders.show', [$row->id])).'" class="'.($icon_only ? 'tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline' : '').'" title="'.e(__('messages.view')).'">'.$label.'</a>';
        }

        if (! auth()->user()->can('sell.view')) {
            return $icon_only ? '' : e($row->invoice_no);
        }

        return '<a href="#" data-href="'.action([\App\Http\Controllers\SellController::class, 'show'], [$row->id]).'" class="'.$btn_class.' btn-modal" data-container=".view_modal" title="'.e(__('messages.view')).'">'.$label.'</a>';
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

    private function homeDisplayName($user): string
    {
        $name = trim(($user->first_name ?? '').' '.($user->surname ?? ''));
        if ($name === '') {
            $name = trim(($user->first_name ?? '').' '.($user->last_name ?? ''));
        }

        return $name !== '' ? $name : (string) ($user->username ?? __('home.home'));
    }

    private function fulfillmentStageForRow($row): array
    {
        $ship = (string) ($row->shipping_status ?? '');
        if ($ship === 'shipped') {
            return ['key' => 'shipment', 'label' => __('home.stage_shipment'), 'icon' => 'fas fa-truck'];
        }
        if ($ship === 'packed') {
            return ['key' => 'packing', 'label' => __('home.stage_packing'), 'icon' => 'fas fa-box'];
        }
        if (in_array($ship, ['ordered', ''], true) || $ship === '') {
            if (in_array((string) $row->status, ['final', 'completed'], true) && empty($ship) && ($row->source ?? '') !== 'ecommerce') {
                return ['key' => 'complete', 'label' => __('home.stage_complete'), 'icon' => 'fas fa-check'];
            }
        }
        if ($ship === 'delivered') {
            return ['key' => 'complete', 'label' => __('lang_v1.delivered'), 'icon' => 'fas fa-check'];
        }

        return ['key' => 'picking', 'label' => __('home.stage_picking'), 'icon' => 'fas fa-clipboard-list'];
    }

    private function applyLocationScope($query, $permitted_locations, $location_id)
    {
        if ($permitted_locations != 'all') {
            $query->whereIn('transactions.location_id', $permitted_locations);
        }
        if (! empty($location_id)) {
            $query->where('transactions.location_id', $location_id);
        }

        return $query;
    }

    private function awaitingFulfillmentQuery($business_id, $permitted_locations, $location_id, $start = null, $end = null)
    {
        $query = Transaction::where('transactions.business_id', $business_id)
            ->where('transactions.status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->where(function ($ship) {
                    $ship->whereNotNull('transactions.shipping_status')
                        ->whereNotIn('transactions.shipping_status', ['delivered', 'cancelled']);
                })->orWhere(function ($ecom) {
                    $ecom->where('transactions.source', 'ecommerce')
                        ->where('transactions.type', 'sales_order')
                        ->whereNotIn('transactions.status', ['completed', 'cancelled']);
                })->orWhere(function ($due) {
                    $due->where('transactions.type', 'sell')
                        ->where('transactions.status', 'final')
                        ->whereIn('transactions.payment_status', ['due', 'partial']);
                });
            });

        $this->applyLocationScope($query, $permitted_locations, $location_id);

        if (! empty($start) && ! empty($end)) {
            $query->whereBetween('transactions.transaction_date', [$start.' 00:00:00', $end.' 23:59:59']);
        }

        return $query;
    }

    private function countAwaitingFulfillment($business_id, $permitted_locations, $location_id, $start = null, $end = null): int
    {
        return (int) $this->awaitingFulfillmentQuery($business_id, $permitted_locations, $location_id, $start, $end)->count();
    }

    private function countDelayedShipments($business_id, $permitted_locations, $location_id, $start = null, $end = null): int
    {
        $cutoff = \Carbon::now()->subDays(2)->format('Y-m-d H:i:s');
        $query = Transaction::where('transactions.business_id', $business_id)
            ->whereNotNull('transactions.shipping_status')
            ->whereNotIn('transactions.shipping_status', ['delivered', 'cancelled'])
            ->where('transactions.transaction_date', '<=', $cutoff);

        $this->applyLocationScope($query, $permitted_locations, $location_id);
        if (! empty($start) && ! empty($end)) {
            $query->whereBetween('transactions.transaction_date', [$start.' 00:00:00', $end.' 23:59:59']);
        }

        return (int) $query->count();
    }

    private function countInvoiceDue($business_id, $permitted_locations, $location_id): int
    {
        $query = Transaction::where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.status', 'final')
            ->whereIn('transactions.payment_status', ['due', 'partial']);
        $this->applyLocationScope($query, $permitted_locations, $location_id);

        return (int) $query->count();
    }

    private function countChannelAwaiting($business_id, $permitted_locations, $location_id, string $channel): int
    {
        $query = $this->awaitingFulfillmentQuery($business_id, $permitted_locations, $location_id);
        if ($channel === 'online') {
            $query->where('transactions.source', 'ecommerce');
        } elseif ($channel === 'field') {
            $query->where('transactions.source', 'sale_app');
        } else {
            $query->where(function ($s) {
                $s->whereNull('transactions.source')->orWhereNotIn('transactions.source', ['ecommerce', 'sale_app']);
            });
        }

        return (int) $query->count();
    }

    private function channelKey(?string $source): string
    {
        if ($source === 'ecommerce') {
            return 'online';
        }
        if ($source === 'sale_app') {
            return 'field';
        }

        return 'pos';
    }

    private function stageKeyForShipping(?string $status, ?string $txnStatus, ?string $source): string
    {
        if ($status === 'shipped') {
            return 'shipment';
        }
        if ($status === 'packed') {
            return 'packing';
        }
        if ($status === 'delivered' || ($txnStatus === 'final' && empty($status) && $source !== 'ecommerce')) {
            return 'complete';
        }

        return 'picking';
    }

    private function buildOpsFulfillment($business_id, $permitted_locations, $location_id, $start, $end): array
    {
        $empty = ['picking' => 0, 'packing' => 0, 'shipment' => 0];
        $by_source = [
            'pos' => $empty,
            'online' => $empty,
            'field' => $empty,
        ];

        $query = $this->dashboardOrdersQuery($business_id, $start, $end, $permitted_locations, $location_id)
            ->select('source', 'status', 'shipping_status', DB::raw('COUNT(*) as cnt'))
            ->groupBy('source', 'status', 'shipping_status');

        foreach ($query->get() as $row) {
            $channel = $this->channelKey($row->source);
            $stage = $this->stageKeyForShipping($row->shipping_status, $row->status, $row->source);
            if ($stage === 'complete') {
                continue;
            }
            $by_source[$channel][$stage] += (int) $row->cnt;
        }

        $totals = $empty;
        foreach ($by_source as $row) {
            $totals['picking'] += $row['picking'];
            $totals['packing'] += $row['packing'];
            $totals['shipment'] += $row['shipment'];
        }

        return [
            'picking' => $totals['picking'],
            'packing' => $totals['packing'],
            'shipment' => $totals['shipment'],
            'by_source' => $by_source,
        ];
    }

    private function lowStockQuery($business_id, $permitted_locations, $location_id)
    {
        $query = VariationLocationDetails::join('products as p', 'variation_location_details.product_id', '=', 'p.id')
            ->join('variations as v', 'variation_location_details.variation_id', '=', 'v.id')
            ->where('p.business_id', $business_id)
            ->where('p.enable_stock', 1)
            ->where('p.is_inactive', 0)
            ->whereNull('v.deleted_at')
            ->whereNotNull('p.alert_quantity')
            ->whereRaw('variation_location_details.qty_available <= p.alert_quantity')
            ->select(
                'p.name as product',
                'p.sku',
                'p.alert_quantity',
                'v.sub_sku',
                'variation_location_details.qty_available as stock'
            );

        if (! empty($permitted_locations) && $permitted_locations != 'all') {
            $query->whereIn('variation_location_details.location_id', $permitted_locations);
        }
        if (! empty($location_id)) {
            $query->where('variation_location_details.location_id', $location_id);
        }

        return $query;
    }

    /**
     * @param  float|int  $current
     * @param  float|int  $previous
     */
    private function percentChange($current, $previous)
    {
        $current = (float) $current;
        $previous = (float) $previous;

        if ($previous == 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / abs($previous)) * 100, 1);
    }

    /**
     * Count of finalized, non-ecommerce sells in a date range — companion to
     * TransactionUtil::getSellTotals() (same population, used for Average Order Value).
     */
    private function getOrdersCount($business_id, $start, $end, $location_id = null, $created_by = null, $permitted_locations = null)
    {
        $query = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->notEcommerce();

        if (! empty($start) && ! empty($end)) {
            $query->whereBetween('transaction_date', [$start.' 00:00:00', $end.' 23:59:59']);
        }
        if (! empty($permitted_locations) && $permitted_locations != 'all') {
            $query->whereIn('location_id', $permitted_locations);
        }
        if (! empty($location_id)) {
            $query->where('location_id', $location_id);
        }
        if (! empty($created_by)) {
            $query->where('created_by', $created_by);
        }

        return (int) $query->count();
    }

    /**
     * Average hours from sale date to the last payment, for fully-paid sells in range.
     * No existing helper for this in TransactionUtil — new query.
     */
    private function getAvgPaymentHours($business_id, $start, $end, $location_id = null, $permitted_locations = null)
    {
        $sub = DB::table('transaction_payments')
            ->select('transaction_id', DB::raw('MAX(paid_on) as last_paid_on'))
            ->whereNull('parent_id')
            ->groupBy('transaction_id');

        $query = Transaction::query()
            ->joinSub($sub, 'tp_last', function ($join) {
                $join->on('tp_last.transaction_id', '=', 'transactions.id');
            })
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.status', 'final')
            ->where('transactions.payment_status', 'paid')
            ->notEcommerce()
            ->whereNotNull('tp_last.last_paid_on');

        if (! empty($start) && ! empty($end)) {
            $query->whereBetween('transactions.transaction_date', [$start.' 00:00:00', $end.' 23:59:59']);
        }
        if (! empty($permitted_locations) && $permitted_locations != 'all') {
            $query->whereIn('transactions.location_id', $permitted_locations);
        }
        if (! empty($location_id)) {
            $query->where('transactions.location_id', $location_id);
        }

        $avg_minutes = (float) $query
            ->selectRaw('AVG(GREATEST(TIMESTAMPDIFF(MINUTE, transactions.transaction_date, tp_last.last_paid_on), 0)) as avg_minutes')
            ->value('avg_minutes');

        return $avg_minutes > 0 ? round($avg_minutes / 60, 1) : 0.0;
    }

    /**
     * Daily order count by sales channel (POS / Online Store / Field Sales).
     */
    private function getOrdersCountByChannel($business_id, $start, $end, $permitted_locations = null)
    {
        $query = $this->dashboardOrdersQuery($business_id, $start, $end, $permitted_locations);

        $channel_case = "CASE
                WHEN source = 'ecommerce' THEN 'online'
                WHEN source = 'sale_app' THEN 'field'
                ELSE 'pos'
            END";

        return $query->select(
            DB::raw('DATE(transaction_date) as date'),
            DB::raw($channel_case.' as channel'),
            DB::raw('COUNT(*) as total_orders')
        )
            ->groupBy(DB::raw($channel_case))
            ->groupBy(DB::raw('DATE(transaction_date)'))
            ->get()
            ->map(function ($row) {
                $row->date = \Carbon::parse($row->date)->format('Y-m-d');
                $row->channel = (string) $row->channel;

                return $row;
            });
    }

    /**
     * Online Store + Field Sales counts/amounts for a date range (KPI totals).
     */
    private function getOnlineAndFieldTotals($business_id, $start, $end, $location_id = null, $created_by = null, $permitted_locations = null)
    {
        $online = $this->dashboardOrdersQuery($business_id, $start, $end, $permitted_locations, $location_id, $created_by)
            ->where('source', 'ecommerce');
        $field = $this->dashboardOrdersQuery($business_id, $start, $end, $permitted_locations, $location_id, $created_by)
            ->where('source', 'sale_app');

        return [
            'online_orders' => (int) (clone $online)->count(),
            'online_amount' => (float) (clone $online)->sum('final_total'),
            'field_orders' => (int) (clone $field)->count(),
            'field_amount' => (float) (clone $field)->sum('final_total'),
        ];
    }

    /**
     * POS finals + Online Store + Field Sales for dashboard widgets.
     */
    private function dashboardOrdersQuery($business_id, $start = null, $end = null, $permitted_locations = null, $location_id = null, $created_by = null, $table = '')
    {
        $c = $table !== '' ? $table.'.' : '';
        $query = Transaction::query()->where($c.'business_id', $business_id);
        $query->where(function ($q) use ($c) {
            $q->where(function ($pos) use ($c) {
                $pos->where($c.'type', 'sell')
                    ->where($c.'status', 'final')
                    ->where(function ($s) use ($c) {
                        $s->whereNull($c.'source')->orWhereNotIn($c.'source', ['ecommerce', 'sale_app']);
                    });
            })->orWhere(function ($ecom) use ($c) {
                $ecom->where($c.'source', 'ecommerce')
                    ->where($c.'type', 'sales_order')
                    ->where($c.'status', '!=', 'cancelled');
            })->orWhere(function ($field) use ($c) {
                $field->where($c.'source', 'sale_app')->whereIn($c.'type', ['sales_order', 'sell']);
            });
        });

        if (! empty($start) && ! empty($end)) {
            $query->whereBetween($c.'transaction_date', [$start.' 00:00:00', $end.' 23:59:59']);
        }
        if (! empty($permitted_locations) && $permitted_locations != 'all') {
            $query->whereIn($c.'location_id', $permitted_locations);
        }
        if (! empty($location_id)) {
            $query->where($c.'location_id', $location_id);
        }
        if (! empty($created_by)) {
            $query->where($c.'created_by', $created_by);
        }

        return $query;
    }

    /**
     * Daily order count per location — mirrors TransactionUtil::getSellsCurrentFy()'s
     * grouping (COUNT instead of SUM), for the Orders Over Time / Orders by Location widgets.
     */
    private function getOrdersCountByLocation($business_id, $start, $end, $permitted_locations = null)
    {
        $query = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->notEcommerce()
            ->whereBetween('transaction_date', [$start.' 00:00:00', $end.' 23:59:59']);

        if (! empty($permitted_locations) && $permitted_locations != 'all') {
            $query->whereIn('location_id', $permitted_locations);
        }

        return $query->select(
            DB::raw('DATE(transaction_date) as date'),
            'location_id',
            DB::raw('COUNT(*) as total_orders')
        )
            ->groupBy('location_id')
            ->groupBy(DB::raw('date(transaction_date)'))
            ->get();
    }

    /**
     * Daily order count + revenue (business-wide) — feeds the KPI sparklines.
     */
    private function getDailyOrdersAndRevenue($business_id, $start, $end, $permitted_locations = null)
    {
        $query = $this->dashboardOrdersQuery($business_id, $start, $end, $permitted_locations);

        return $query->select(
            DB::raw('DATE(transaction_date) as date'),
            DB::raw('COUNT(*) as orders_count'),
            DB::raw('SUM(final_total) as revenue')
        )
            ->groupBy(DB::raw('DATE(transaction_date)'))
            ->get()
            ->mapWithKeys(function ($row) {
                $date = \Carbon::parse($row->date)->format('Y-m-d');

                return [$date => $row];
            });
    }

    /**
     * Daily avg time-to-payment (business-wide) — feeds the Avg. Time to Payment sparkline.
     */
    private function getDailyAvgPaymentHours($business_id, $start, $end, $permitted_locations = null)
    {
        $sub = DB::table('transaction_payments')
            ->select('transaction_id', DB::raw('MAX(paid_on) as last_paid_on'))
            ->whereNull('parent_id')
            ->groupBy('transaction_id');

        $query = Transaction::query()
            ->joinSub($sub, 'tp_last', function ($join) {
                $join->on('tp_last.transaction_id', '=', 'transactions.id');
            })
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.status', 'final')
            ->where('transactions.payment_status', 'paid')
            ->notEcommerce()
            ->whereNotNull('tp_last.last_paid_on')
            ->whereBetween('transactions.transaction_date', [$start.' 00:00:00', $end.' 23:59:59']);

        if (! empty($permitted_locations) && $permitted_locations != 'all') {
            $query->whereIn('transactions.location_id', $permitted_locations);
        }

        return $query->select(
            DB::raw('DATE(transactions.transaction_date) as date'),
            DB::raw('AVG(GREATEST(TIMESTAMPDIFF(MINUTE, transactions.transaction_date, tp_last.last_paid_on), 0)) as avg_minutes')
        )
            ->groupBy(DB::raw('date(transactions.transaction_date)'))
            ->get()
            ->keyBy('date');
    }

    /**
     * Snapshot count of sells per pipeline stage (draft/quotation/proforma/final×payment_status)
     * within a date range. Shared by the Fulfillment Pipeline widget and the
     * Orders by Status week-over-week comparison.
     */
    private function buildFulfillmentPipeline($business_id, $permitted_locations, $start, $end, $location_id = null)
    {
        $query = Transaction::where('business_id', $business_id)
            ->where(function ($q) {
                $q->where('type', 'sell')
                    ->orWhere(function ($other) {
                        $other->whereIn('source', ['ecommerce', 'sale_app'])
                            ->whereIn('type', ['sales_order', 'sell'])
                            ->where('status', '!=', 'cancelled');
                    });
            })
            ->whereBetween('transaction_date', [$start.' 00:00:00', $end.' 23:59:59']);

        if (! empty($permitted_locations) && $permitted_locations != 'all') {
            $query->whereIn('location_id', $permitted_locations);
        }
        if (! empty($location_id)) {
            $query->where('location_id', $location_id);
        }

        $rows = $query->select('status', 'payment_status', DB::raw('COUNT(*) as cnt'))
            ->groupBy('status', 'payment_status')
            ->get();

        $stages = [
            'draft' => 0,
            'quotation' => 0,
            'proforma' => 0,
            'final_due' => 0,
            'final_partial' => 0,
            'final_paid' => 0,
        ];

        foreach ($rows as $row) {
            if ($row->status === 'final') {
                if ($row->payment_status === 'paid') {
                    $stages['final_paid'] += (int) $row->cnt;
                } elseif ($row->payment_status === 'partial') {
                    $stages['final_partial'] += (int) $row->cnt;
                } else {
                    $stages['final_due'] += (int) $row->cnt;
                }
            } elseif (array_key_exists($row->status, $stages)) {
                $stages[$row->status] += (int) $row->cnt;
            } elseif (in_array($row->status, ['ordered', 'received', 'partial', 'completed'], true)) {
                if ($row->payment_status === 'paid') {
                    $stages['final_paid'] += (int) $row->cnt;
                } elseif ($row->payment_status === 'partial') {
                    $stages['final_partial'] += (int) $row->cnt;
                } else {
                    $stages['final_due'] += (int) $row->cnt;
                }
            }
        }

        return $stages;
    }

    /**
     * 4 Location Performance mini-stats (last-30-days window, "Sales Today" excepted).
     */
    private function buildLocationPerformance($business_id, $permitted_locations, $all_locations, $start, $end)
    {
        $active_locations = (int) $this->dashboardOrdersQuery($business_id, $start, $end, $permitted_locations)
            ->distinct('location_id')->count('location_id');

        $today = \Carbon::now()->format('Y-m-d');
        $sales_today = (int) $this->dashboardOrdersQuery($business_id, $today, $today, $permitted_locations)->count();

        $avg_payment_hours = $this->getAvgPaymentHours($business_id, $start, $end, null, $permitted_locations);

        $sums = $this->dashboardOrdersQuery($business_id, $start, $end, $permitted_locations)
            ->selectRaw('SUM(final_total) as total_final, SUM(final_total - (SELECT COALESCE(SUM(IF(tp.is_return = 1, -1*tp.amount, tp.amount)), 0) FROM transaction_payments tp WHERE tp.transaction_id = transactions.id)) as total_due')
            ->first();

        $total_final = (float) ($sums->total_final ?? 0);
        $total_due = (float) ($sums->total_due ?? 0);
        $total_paid = $total_final - $total_due;
        $collection_rate = $total_final > 0 ? round(($total_paid / $total_final) * 100, 1) : 0.0;

        // Prior-window deltas for warehouse-style trend lines under each tile
        $start_c = \Carbon::parse($start);
        $end_c = \Carbon::parse($end);
        $days = max(1, $start_c->diffInDays($end_c) + 1);
        $prev_end = $start_c->copy()->subDay();
        $prev_start = $prev_end->copy()->subDays($days - 1)->format('Y-m-d');
        $prev_end_str = $prev_end->format('Y-m-d');

        $prev_active = (int) $this->dashboardOrdersQuery($business_id, $prev_start, $prev_end_str, $permitted_locations)
            ->distinct('location_id')->count('location_id');

        $yesterday = \Carbon::now()->subDay()->format('Y-m-d');
        $sales_yesterday = (int) $this->dashboardOrdersQuery($business_id, $yesterday, $yesterday, $permitted_locations)->count();

        $prev_avg_payment = $this->getAvgPaymentHours($business_id, $prev_start, $prev_end_str, null, $permitted_locations);

        $prev_sums = $this->dashboardOrdersQuery($business_id, $prev_start, $prev_end_str, $permitted_locations)
            ->selectRaw('SUM(final_total) as total_final, SUM(final_total - (SELECT COALESCE(SUM(IF(tp.is_return = 1, -1*tp.amount, tp.amount)), 0) FROM transaction_payments tp WHERE tp.transaction_id = transactions.id)) as total_due')
            ->first();
        $prev_final = (float) ($prev_sums->total_final ?? 0);
        $prev_due = (float) ($prev_sums->total_due ?? 0);
        $prev_collection = $prev_final > 0 ? round((($prev_final - $prev_due) / $prev_final) * 100, 1) : 0.0;

        return [
            'active_locations' => $active_locations,
            'total_locations' => count($all_locations),
            'sales_today' => $sales_today,
            'avg_payment_hours' => $avg_payment_hours,
            'collection_rate' => $collection_rate,
            'change_pct' => [
                'active_locations' => $this->percentChange($active_locations, $prev_active),
                'sales_today' => $this->percentChange($sales_today, $sales_yesterday),
                'avg_payment_hours' => $this->percentChange($avg_payment_hours, $prev_avg_payment),
                'collection_rate' => $this->percentChange($collection_rate, $prev_collection),
            ],
        ];
    }

    /**
     * Soft filled area sparkline for KPI cards (matches analytics mock).
     */
    private function buildSparkline($values, $color)
    {
        $chart = new CommonChart;
        // Package defaults height:400px (inline !important) — must match CSS mount height.
        $chart->height(44);

        // Soft pastel fill under the line (mock style)
        $fill_map = [
            '#3b82f6' => 'rgba(59, 130, 246, 0.22)',
            '#8b5cf6' => 'rgba(139, 92, 246, 0.22)',
            '#14b8a6' => 'rgba(20, 184, 166, 0.22)',
            '#f97316' => 'rgba(249, 115, 22, 0.22)',
        ];
        $fill = $fill_map[$color] ?? 'rgba(59, 130, 246, 0.22)';

        $options = [
            'chart' => [
                'type' => 'areaspline',
                'backgroundColor' => 'transparent',
                'margin' => [4, 4, 2, 4],
                'spacing' => [0, 0, 0, 0],
            ],
            'credits' => ['enabled' => false],
            'exporting' => ['enabled' => false],
            'title' => ['text' => null],
            'legend' => ['enabled' => false],
            'xAxis' => ['visible' => false, 'minPadding' => 0, 'maxPadding' => 0],
            'yAxis' => ['visible' => false, 'min' => 0, 'startOnTick' => false, 'endOnTick' => false],
            'tooltip' => ['enabled' => false],
            'plotOptions' => [
                'areaspline' => [
                    'fillOpacity' => 0.35,
                    'lineWidth' => 2.25,
                    'marker' => ['enabled' => false],
                ],
                'series' => [
                    'animation' => false,
                    'enableMouseTracking' => false,
                    'states' => ['hover' => ['enabled' => false]],
                ],
            ],
        ];
        $chart->options($options, true);
        $chart->dataset('', 'areaspline', $values)->options([
            'color' => $color,
            'fillColor' => [
                'linearGradient' => ['x1' => 0, 'y1' => 0, 'x2' => 0, 'y2' => 1],
                'stops' => [
                    [0, $fill],
                    [1, 'rgba(255,255,255,0)'],
                ],
            ],
        ]);

        return $chart;
    }

    private function __chartOptions($title)
    {
        return [
            // Highcharts' built-in export/hamburger button renders unstyled (and, in tightly
            // clipped cards, visibly broken) — every chart on this dashboard is chrome-free,
            // matching the reference design.
            'exporting' => ['enabled' => false],
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
                '#3b82f6',
                '#8b5cf6',
                '#14b8a6',
                '#f97316',
                '#f59e0b',
                '#22c55e',
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
