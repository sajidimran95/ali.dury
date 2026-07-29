@extends('layouts.app')
@section('title', __('home.home'))

@section('content')

    @if (auth()->user()->can('dashboard.data'))
        <div class="japs-dashboard-page">
        <div class="zoho-dashboard__layout">
            <div class="zoho-dashboard__main">
    @endif

    <div class="zoho-dashboard">
        <div class="zoho-dashboard__header japs-dashboard-hero">
            <div class="zoho-dashboard__header-inner">
                <div class="zoho-dashboard__title-row">
                    <div class="zoho-dashboard__greeting">
                        <div class="zoho-dashboard__avatar">{{ strtoupper(substr(Session::get('business.name', 'J'), 0, 1)) }}</div>
                        <div>
                            <h1 class="japs-dashboard-welcome zoho-dashboard__welcome">
                                {{ __('home.welcome_message', ['name' => Session::get('user.first_name')]) }}
                            </h1>
                            <p class="japs-dashboard-welcome-sub zoho-dashboard__subtitle">
                                {{ Session::get('business.name') }} · {{ @format_date(\Carbon::now()) }}
                            </p>
                        </div>
                    </div>

                    <div class="zoho-dashboard__toolbar japs-dash-toolbar">
                        @if (auth()->user()->can('dashboard.data') && $is_admin)
                            @if (count($all_locations) > 1)
                                <div class="zoho-dashboard__toolbar-item">
                                    {!! Form::select('dashboard_location', $all_locations, null, [
                                        'class' => 'form-control select2 zoho-dashboard__select',
                                        'placeholder' => __('lang_v1.select_location'),
                                        'id' => 'dashboard_location',
                                    ]) !!}
                                </div>
                            @endif
                            <button type="button" id="dashboard_date_filter"
                                class="japs-dashboard-filter-btn zoho-dashboard__filter-btn">
                                <svg aria-hidden="true" class="tw-size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"/><path d="M16 3v4M8 3v4M4 11h16"/></svg>
                                <span>{{ __('messages.filter_by_date') }}</span>
                                <svg aria-hidden="true" class="tw-size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M6 9l6 6l6 -6"/></svg>
                            </button>
                        @endif
                        <div class="japs-dash-toolbar-attendance @if(!auth()->user()->can('dashboard.data')) japs-dash-toolbar-attendance--solo @endif">
                            @include('home.partials.attendance_clock')
                        </div>
                    </div>
                </div>

                @if (auth()->user()->can('dashboard.data') && $is_admin)
                    @php
                        $card_sells = action([\App\Http\Controllers\SellController::class, 'index']);
                        $card_sells_due = action([\App\Http\Controllers\SellController::class, 'index'], ['payment_status' => 'due']);
                        $card_sell_return = action([\App\Http\Controllers\SellReturnController::class, 'index']);
                        $card_purchases = action([\App\Http\Controllers\PurchaseController::class, 'index']);
                        $card_purchases_due = action([\App\Http\Controllers\PurchaseController::class, 'index'], ['payment_status' => 'due']);
                        $card_purchase_return = action([\App\Http\Controllers\PurchaseReturnController::class, 'index']);
                        $card_expenses = action([\App\Http\Controllers\ExpenseController::class, 'index']);
                        $card_profit_loss = action([\App\Http\Controllers\ReportController::class, 'getProfitLoss']);
                        $card_online_orders = route('ecommerce.admin.orders');
                    @endphp

                    <div class="japs-dash-kpi-row">
                        <a href="{{ $card_sells }}" class="japs-dash-kpi-card japs-dash-kpi-card--blue" title="{{ __('home.total_sell') }}">
                            <div class="japs-dash-kpi-card__top">
                                <span class="japs-dash-kpi-card__icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                                </span>
                            </div>
                            <p class="japs-dash-kpi-card__label">{{ __('home.total_sell') }}</p>
                            <p class="japs-dash-kpi-card__value total_sell"></p>
                            <div class="japs-dash-kpi-card__footer">
                                <span class="japs-dash-kpi-card__delta" data-kpi="total_sell">—</span>
                                <span class="japs-dash-kpi-card__period">{{ __('lang_v1.from_previous_period') }}</span>
                            </div>
                        </a>
                        <a href="{{ $card_profit_loss }}" class="japs-dash-kpi-card japs-dash-kpi-card--teal japs-dash-kpi-card--profit" title="{{ __('home.net_profit') }}">
                            <div class="japs-dash-kpi-card__top">
                                <span class="japs-dash-kpi-card__icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
                                </span>
                            </div>
                            <p class="japs-dash-kpi-card__label">{{ __('home.net_profit') }} @show_tooltip(__('lang_v1.net_home_tooltip'))</p>
                            <p class="japs-dash-kpi-card__value net"></p>
                            <div class="japs-dash-kpi-card__footer">
                                <span class="japs-dash-kpi-card__delta" data-kpi="net">—</span>
                                <span class="japs-dash-kpi-card__period">{{ __('lang_v1.from_previous_period') }}</span>
                            </div>
                        </a>
                        <a href="{{ $card_sells_due }}" class="japs-dash-kpi-card japs-dash-kpi-card--amber" title="{{ __('home.invoice_due') }}">
                            <div class="japs-dash-kpi-card__top">
                                <span class="japs-dash-kpi-card__icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
                                </span>
                            </div>
                            <p class="japs-dash-kpi-card__label">{{ __('home.invoice_due') }}</p>
                            <p class="japs-dash-kpi-card__value invoice_due"></p>
                            <div class="japs-dash-kpi-card__footer">
                                <span class="japs-dash-kpi-card__delta" data-kpi="invoice_due">—</span>
                                <span class="japs-dash-kpi-card__period">{{ __('lang_v1.from_previous_period') }}</span>
                            </div>
                        </a>
                        <a href="{{ $card_purchases }}" class="japs-dash-kpi-card japs-dash-kpi-card--violet" title="{{ __('home.total_purchase') }}">
                            <div class="japs-dash-kpi-card__top">
                                <span class="japs-dash-kpi-card__icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05"/><path d="M12 22.08V12"/></svg>
                                </span>
                            </div>
                            <p class="japs-dash-kpi-card__label">{{ __('home.total_purchase') }}</p>
                            <p class="japs-dash-kpi-card__value total_purchase"></p>
                            <div class="japs-dash-kpi-card__footer">
                                <span class="japs-dash-kpi-card__delta" data-kpi="total_purchase">—</span>
                                <span class="japs-dash-kpi-card__period">{{ __('lang_v1.from_previous_period') }}</span>
                            </div>
                        </a>
                    </div>

                    <div class="japs-dash-charts-row">
                        @if ((auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view')) && !empty($all_locations))
                            <div class="japs-dash-chart-card japs-dash-chart-card--sales japs-dash-chart-card--revenue zoho-dashboard__main-chart">
                                <div class="japs-dash-revenue-head">
                                    <div class="japs-dash-revenue-head__left">
                                        <h3 class="japs-dash-chart-card__title">{{ __('home.revenue_insights') }}</h3>
                                        <div class="japs-dash-revenue-hero">
                                            <span class="japs-dash-revenue-hero__value japs-chart-total-sell">—</span>
                                            <span class="japs-dash-growth-pill japs-chart-sell-growth">—</span>
                                        </div>
                                    </div>
                                    <div class="japs-dash-period-switch" role="tablist" aria-label="Chart period">
                                        <button type="button" class="japs-dash-period-switch__btn" data-period="30d">{{ __('home.monthly') }}</button>
                                        <button type="button" class="japs-dash-period-switch__btn is-active" data-period="fy">{{ __('home.yearly') }}</button>
                                    </div>
                                </div>
                                <div class="japs-dash-revenue-legend" aria-hidden="true">
                                    <span class="japs-dash-revenue-legend__item"><i class="japs-dash-revenue-legend__dot japs-dash-revenue-legend__dot--earn"></i>{{ __('home.total_sell') }}</span>
                                </div>
                                <div class="japs-dash-chart-panes">
                                    <div class="japs-dash-chart-pane" id="japs_dash_main_chart_wrap" data-period="30d" hidden>
                                        {!! $sells_chart_1->container() !!}
                                    </div>
                                    @if (!empty($sells_chart_2))
                                    <div class="japs-dash-chart-pane is-active" id="japs_dash_fy_chart_wrap" data-period="fy">
                                        {!! $sells_chart_2->container() !!}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        <div class="japs-dash-chart-card japs-dash-chart-card--payment japs-dash-chart-card--overview">
                            <div class="japs-dash-chart-card__header">
                                <h3 class="japs-dash-chart-card__title">{{ __('home.sales_overview') }}</h3>
                                <button type="button" class="japs-dash-chart-card__menu-btn" id="payment_method_mix_menu_btn" aria-label="Chart menu" title="Chart menu" aria-expanded="false">
                                    <span class="japs-dash-chart-card__menu-icon" aria-hidden="true"></span>
                                </button>
                            </div>
                            <div class="japs-dash-payment-body">
                                <div class="japs-dash-payment-mix">
                                    <div class="japs-dash-payment-chart-wrap japs-dash-gauge-wrap">
                                        <div id="payment_method_mix_chart" class="japs-dash-payment-chart japs-dash-gauge-chart"></div>
                                        <div id="payment_method_mix_center" class="japs-dash-donut-center japs-dash-gauge-center" aria-hidden="true" style="display:none;">
                                            <div class="japs-dash-donut-center__amount"></div>
                                            <div class="japs-dash-donut-center__label" data-default-label="{{ __('home.sales_growth') }}">{{ __('home.sales_growth') }}</div>
                                        </div>
                                    </div>
                                </div>
                                <p id="payment_method_mix_empty" class="japs-dash-payment-empty" style="display:none;">{{ __('lang_v1.no_data') }}</p>
                            </div>
                            <div class="japs-dash-overview-footer japs-dash-overview-footer--split" id="payment_overview_footer" style="display:none;">
                                <div class="japs-dash-overview-footer__col">
                                    <span class="japs-dash-overview-footer__label">{{ __('home.total_sell') }}</span>
                                    <span class="japs-dash-overview-footer__value japs-overview-sales-total">—</span>
                                    <div class="japs-dash-overview-progress">
                                        <div class="japs-dash-overview-progress__bar" id="payment_overview_progress" style="width:0%"></div>
                                    </div>
                                </div>
                                <div class="japs-dash-overview-footer__col">
                                    <span class="japs-dash-overview-footer__label">{{ __('home.invoice_due') }}</span>
                                    <span class="japs-dash-overview-footer__value japs-overview-target-total">—</span>
                                    <div class="japs-dash-overview-progress japs-dash-overview-progress--muted">
                                        <div class="japs-dash-overview-progress__bar japs-dash-overview-progress__bar--muted" id="payment_overview_target_progress" style="width:0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if (auth()->user()->can('dashboard.data'))
        </div>{{-- /.zoho-dashboard__main --}}
        </div>{{-- /.zoho-dashboard__layout --}}

        <div class="japs-dashboard-content zoho-dashboard__body">
            @if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view'))
                <div class="japs-dash-recent-card">
                    <div class="japs-dash-recent-card__header">
                        <h3 class="japs-dash-recent-card__title">{{ __('home.recent_sales') }}</h3>
                        <div class="japs-dash-recent-card__actions">
                            @if (count($all_locations) > 1)
                                {!! Form::select('recent_sales_location', $all_locations, null, [
                                    'class' => 'form-control select2 japs-dash-recent-filter',
                                    'placeholder' => __('lang_v1.all_locations'),
                                    'id' => 'recent_sales_location',
                                ]) !!}
                            @endif
                            <a href="{{ action([\App\Http\Controllers\SellController::class, 'index']) }}" class="japs-dash-recent-viewall">
                                {{ __('lang_v1.view_all_sale') }}
                            </a>
                        </div>
                    </div>
                    <div class="japs-dash-full-table">
                        <table class="table table-bordered table-striped ajax_view" id="recent_sales_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="not-export">@lang('messages.action')</th>
                                    <th>@lang('sale.invoice_no')</th>
                                    <th>@lang('messages.date')</th>
                                    <th>@lang('contact.customer')</th>
                                    <th>@lang('sale.payment_status')</th>
                                    <th>@lang('sale.total')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            @endif
        </div>{{-- /.japs-dashboard-content --}}
        </div>{{-- /.japs-dashboard-page --}}
    @endif

@endsection

<div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade edit_pso_status_modal" tabindex="-1" role="dialog"></div>
<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
</div>

@section('css')
    <link href="{{ asset('css/japs-ui.css') }}" rel="stylesheet">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection

@section('javascript')
    <script src="{{ asset('js/home.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
    @if (!empty($all_locations))
        {!! $sells_chart_1->script() !!}
        {!! $sells_chart_2->script() !!}
        <script type="text/javascript">
            $(function () {
                if (!$('body').hasClass('japs-dashboard-home') || typeof Highcharts === 'undefined') {
                    return;
                }
                setTimeout(function () {
                    function ensureHatchPattern(chart) {
                        if (!chart || !chart.renderer || !chart.renderer.defs) {
                            return null;
                        }
                        var patternId = 'japs-bar-hatch-' + (chart.index != null ? chart.index : Math.random().toString(36).slice(2, 7));
                        if (chart.renderer.box && chart.renderer.box.querySelector('#' + patternId)) {
                            return patternId;
                        }
                        var pattern = chart.renderer.createElement('pattern').attr({
                            id: patternId,
                            patternUnits: 'userSpaceOnUse',
                            width: 7,
                            height: 7
                        }).add(chart.renderer.defs);
                        chart.renderer.rect(0, 0, 7, 7).attr({ fill: '#eef2f7' }).add(pattern);
                        chart.renderer.path({
                            d: 'M0,7 l7,-7 M-1.5,1.5 l3,-3 M5.5,8.5 l3,-3',
                            stroke: '#c8d0dc',
                            'stroke-width': 1.25
                        }).add(pattern);
                        chart.japsHatchPatternId = patternId;
                        return patternId;
                    }

                    function isRevenueChart(chart) {
                        if (!chart || !chart.renderTo) return false;
                        var id = chart.renderTo.id || '';
                        if (id === 'payment_method_mix_chart') return false;
                        return $(chart.renderTo).closest('.japs-dash-chart-card--revenue, .japs-dash-chart-panes').length > 0;
                    }

                    function styleRevenueChart(chart) {
                        if (!isRevenueChart(chart)) return;

                        var isYearly = $(chart.renderTo).closest('.japs-dash-chart-pane').data('period') === 'fy';
                        var hatchId = ensureHatchPattern(chart) || chart.japsHatchPatternId;
                        var inactiveColor = hatchId ? ('url(#' + hatchId + ')') : '#e8edf5';

                        chart.update({
                            chart: {
                                backgroundColor: 'transparent',
                                type: 'column',
                                spacingTop: 28,
                                spacingRight: 12,
                                spacingBottom: isYearly ? 8 : 18,
                                spacingLeft: 4
                            },
                            legend: { enabled: false },
                            tooltip: {
                                backgroundColor: '#3b82f6',
                                borderColor: '#3b82f6',
                                borderRadius: 8,
                                borderWidth: 0,
                                shadow: false,
                                style: { color: '#fff', fontSize: '12px', fontWeight: '700' },
                                padding: 10,
                                useHTML: true,
                                headerFormat: '',
                                pointFormatter: function () {
                                    return '<span style="white-space:nowrap">' +
                                        (typeof __currency_trans_from_en === 'function'
                                            ? __currency_trans_from_en(this.y, true)
                                            : this.y) +
                                        '</span>';
                                }
                            },
                            xAxis: {
                                lineColor: 'transparent',
                                tickLength: 0,
                                labels: {
                                    // Monthly (Jul 1, Jul 2…): angled like previous style
                                    // Yearly (Jan, Feb…): horizontal
                                    rotation: isYearly ? 0 : -45,
                                    style: { color: '#94a3b8', fontSize: '11px', fontWeight: '500' }
                                }
                            },
                            yAxis: {
                                gridLineColor: '#eef2f7',
                                gridLineWidth: 1,
                                gridLineDashStyle: 'Solid',
                                title: { text: null },
                                min: 0,
                                labels: {
                                    style: { color: '#94a3b8', fontSize: '11px' },
                                    formatter: function () {
                                        var v = this.value;
                                        if (v >= 1000) {
                                            return (v / 1000) + 'k';
                                        }
                                        return v;
                                    }
                                }
                            },
                            plotOptions: {
                                column: {
                                    borderWidth: 0,
                                    borderRadius: 14,
                                    pointPadding: 0.01,
                                    groupPadding: 0.02,
                                    maxPointWidth: 58,
                                    states: { hover: { brightness: 0.06 } }
                                },
                                series: { borderWidth: 0, animation: false }
                            }
                        }, false);

                        if (chart.series && chart.series.length) {
                            chart.series.forEach(function (s) {
                                var maxVal = -1, maxIdx = -1;
                                (s.data || []).forEach(function (point, idx) {
                                    var val = typeof point.y === 'number' ? point.y : 0;
                                    if (val > maxVal) { maxVal = val; maxIdx = idx; }
                                });
                                (s.data || []).forEach(function (point, idx) {
                                    var isPeak = idx === maxIdx && maxVal > 0;
                                    // Top (strong blue) → Bottom (lighter blue)
                                    var peakGradient = {
                                        linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                                        stops: [
                                            [0, '#1d4ed8'],
                                            [0.4, '#3b82f6'],
                                            [1, '#bfdbfe']
                                        ]
                                    };
                                    point.update({
                                        color: isPeak ? peakGradient : inactiveColor,
                                        borderRadius: isPeak ? 16 : 14
                                    }, false);
                                });
                            });
                        }
                        chart.redraw();
                    }
                    window.japsStyleRevenueChart = styleRevenueChart;

                    function refreshActiveRevenueChart() {
                        (Highcharts.charts || []).forEach(function (chart) {
                            if (!isRevenueChart(chart)) return;
                            var $pane = $(chart.renderTo).closest('.japs-dash-chart-pane');
                            if ($pane.length && !$pane.hasClass('is-active')) return;
                            try {
                                chart.reflow();
                            } catch (e) {}
                            styleRevenueChart(chart);
                        });
                    }

                    // Style both Monthly + Yearly (Yearly is hidden at first — briefly measure it)
                    (Highcharts.charts || []).forEach(function (chart) {
                        if (!isRevenueChart(chart)) return;
                        var $pane = $(chart.renderTo).closest('.japs-dash-chart-pane');
                        var wasHidden = $pane.length && !$pane.hasClass('is-active');
                        if (wasHidden) {
                            $pane.css({ display: 'block', visibility: 'hidden', position: 'absolute', left: '-9999px' });
                        }
                        try {
                            chart.reflow();
                        } catch (e) {}
                        styleRevenueChart(chart);
                        if (wasHidden) {
                            $pane.css({ display: '', visibility: '', position: '', left: '' });
                        }
                    });

                    // Apply filter data that may have arrived before Highcharts finished init
                    if (window.japsRevenueInsightsData && typeof window.japsApplyRevenueInsightsData === 'function') {
                        window.japsApplyRevenueInsightsData(window.japsRevenueInsightsData);
                    } else if (typeof window.japsLoadRevenueInsights === 'function' && $('#dashboard_date_filter').length) {
                        try {
                            var picker = $('#dashboard_date_filter').data('daterangepicker');
                            if (picker) {
                                window.japsLoadRevenueInsights(
                                    picker.startDate.format('YYYY-MM-DD'),
                                    picker.endDate.format('YYYY-MM-DD'),
                                    $('#dashboard_location').length ? $('#dashboard_location').val() : ''
                                );
                            }
                        } catch (e3) {}
                    }

                    $('.japs-dash-period-switch__btn').off('click.japsRevenueTab').on('click.japsRevenueTab', function () {
                        var period = $(this).data('period');
                        $('.japs-dash-period-switch__btn').removeClass('is-active');
                        $(this).addClass('is-active');
                        $('.japs-dash-chart-pane').removeClass('is-active').attr('hidden', true);
                        $('.japs-dash-chart-pane[data-period="' + period + '"]').addClass('is-active').removeAttr('hidden');
                        var cached = window.japsRevenueInsightsData;
                        if (cached && typeof Highcharts !== 'undefined') {
                            (Highcharts.charts || []).forEach(function (chart) {
                                if (!isRevenueChart(chart)) return;
                                var $pane = $(chart.renderTo).closest('.japs-dash-chart-pane');
                                if (!$pane.hasClass('is-active')) return;
                                var labels = period === 'fy' ? (cached.monthly_labels || []) : (cached.daily_labels || []);
                                var values = period === 'fy' ? (cached.monthly_values || []) : (cached.daily_values || []);
                                try {
                                    chart.xAxis[0].setCategories(labels, false);
                                    chart.series[0].setData(values, false);
                                    chart.redraw();
                                } catch (e) {}
                            });
                        }
                        // Yearly pane was display:none — reflow + same Salezy style as Monthly
                        setTimeout(refreshActiveRevenueChart, 30);
                        setTimeout(refreshActiveRevenueChart, 180);
                    });
                }, 400);
            });
        </script>
    @endif

    @if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view'))
    <script type="text/javascript">
        $(function () {
            if (!$('#recent_sales_table').length) {
                return;
            }
            var recentSalesTable = $('#recent_sales_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader: false,
                autoWidth: false,
                scrollX: true,
                scrollCollapse: true,
                scrollY: '320px',
                pageLength: 10,
                lengthChange: false,
                searching: false,
                pagingType: 'simple_numbers',
                dom: 'rti',
                buttons: [],
                aaSorting: [[2, 'desc']],
                ajax: {
                    url: '{{ action([\App\Http\Controllers\SellController::class, 'index']) }}',
                    data: function (d) {
                        d.is_direct_sale = 1;
                        if ($('#recent_sales_location').length) {
                            d.location_id = $('#recent_sales_location').val();
                        }
                    }
                },
                columnDefs: [{ targets: 0, orderable: false, searchable: false }],
                columns: [
                    { data: 'action', name: 'action' },
                    { data: 'invoice_no', name: 'invoice_no' },
                    { data: 'transaction_date', name: 'transaction_date' },
                    { data: 'conatct_name', name: 'conatct_name' },
                    { data: 'payment_status', name: 'payment_status' },
                    { data: 'final_total', name: 'final_total' }
                ],
                fnDrawCallback: function () {
                    __currency_convert_recursively($('#recent_sales_table'));
                }
            });
            $('#recent_sales_location').on('change', function () {
                recentSalesTable.ajax.reload();
            });
        });
    </script>
    @endif

    @can('attendance.clock')
    <script type="text/javascript">
        $(function () {
            var attState = 'clocked_out';
            var clockInAt = null;
            var breakStartAt = null;
            var baseWorked = 0;
            var baseBreak = 0;

            function pad(n) { return n < 10 ? '0' + n : '' + n; }
            function formatSecs(total) {
                total = Math.max(0, Math.floor(total || 0));
                var h = Math.floor(total / 3600);
                var m = Math.floor((total % 3600) / 60);
                var s = total % 60;
                return pad(h) + ':' + pad(m) + ':' + pad(s);
            }

            function applyStatus(st) {
                if (!st) return;
                attState = st.state || 'clocked_out';
                clockInAt = st.clock_in_at ? new Date(st.clock_in_at) : null;
                breakStartAt = st.break_start_at ? new Date(st.break_start_at) : null;
                baseWorked = parseInt(st.worked_seconds, 10) || 0;
                baseBreak = parseInt(st.break_seconds, 10) || 0;
                window.__attLoadedAt = Date.now();

                var $widget = $('#att_clock_widget');
                $widget.removeClass('is-working is-break is-out');
                $('#att_btn_clock_in, #att_btn_start_break, #att_btn_end_break, #att_btn_clock_out, #att_live_timer').addClass('hide');

                if (attState === 'clocked_in') {
                    $widget.addClass('is-working');
                    $('#att_state_label').text("{{ __('attendance.working') }}");
                    $('#att_btn_start_break, #att_btn_clock_out, #att_live_timer').removeClass('hide');
                    if (clockInAt) {
                        $('#att_meta_label').text("{{ __('attendance.clocked_in_at') }}: " + clockInAt.toLocaleTimeString());
                    }
                } else if (attState === 'on_break') {
                    $widget.addClass('is-break');
                    $('#att_state_label').text("{{ __('attendance.on_break') }}");
                    $('#att_btn_end_break, #att_btn_clock_out, #att_live_timer').removeClass('hide');
                    $('#att_meta_label').text("{{ __('attendance.on_break') }}");
                } else {
                    $widget.addClass('is-out');
                    $('#att_state_label').text("{{ __('attendance.not_clocked_in') }}");
                    $('#att_btn_clock_in').removeClass('hide');
                    $('#att_meta_label').text('');
                }

                updateTimers();
            }

            function updateTimers() {
                var now = Date.now();
                var worked = baseWorked;
                var brk = baseBreak;
                var loadedAt = window.__attLoadedAt || now;
                var elapsed = Math.max(0, Math.floor((now - loadedAt) / 1000));

                if (attState === 'clocked_in') {
                    if (clockInAt && !isNaN(clockInAt.getTime())) {
                        worked = Math.max(0, Math.floor((now - clockInAt.getTime()) / 1000) - baseBreak);
                    } else {
                        worked = baseWorked + elapsed;
                    }
                } else if (attState === 'on_break') {
                    brk = baseBreak + elapsed;
                    worked = baseWorked;
                }

                var workedLabel = formatSecs(worked);
                var breakLabel = formatSecs(brk);
                $('#att_worked_timer').text(workedLabel);
                $('#att_break_timer').text(breakLabel);
                if (attState === 'on_break') {
                    $('#att_live_timer_value').text(breakLabel);
                } else {
                    $('#att_live_timer_value').text(workedLabel);
                }
            }

            function loadStatus() {
                $.ajax({
                    url: '{{ action([\App\Http\Controllers\AttendanceController::class, "status"]) }}',
                    method: 'GET',
                    dataType: 'json',
                    success: function (resp) {
                        window.__attLoadedAt = Date.now();
                        applyStatus(resp);
                    }
                });
            }

            function postAction(url) {
                $.ajax({
                    method: 'POST',
                    url: url,
                    data: { _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function (result) {
                        if (typeof toastr !== 'undefined') {
                            if (result.success) {
                                toastr.success(result.msg);
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                        if (result && result.status) {
                            applyStatus(result.status);
                        } else {
                            loadStatus();
                        }
                    },
                    error: function () {
                        if (typeof toastr !== 'undefined') {
                            toastr.error("{{ __('messages.something_went_wrong') }}");
                        }
                    }
                });
            }

            $('#att_btn_clock_in').on('click', function () {
                postAction('{{ action([\App\Http\Controllers\AttendanceController::class, "clockIn"]) }}');
            });
            $('#att_btn_start_break').on('click', function () {
                postAction('{{ action([\App\Http\Controllers\AttendanceController::class, "startBreak"]) }}');
            });
            $('#att_btn_end_break').on('click', function () {
                postAction('{{ action([\App\Http\Controllers\AttendanceController::class, "endBreak"]) }}');
            });
            $('#att_btn_clock_out').on('click', function () {
                if (typeof swal === 'undefined') {
                    postAction('{{ action([\App\Http\Controllers\AttendanceController::class, "clockOut"]) }}');
                    return;
                }
                swal({
                    title: LANG.sure,
                    icon: 'warning',
                    buttons: true,
                }).then(function (confirmed) {
                    if (confirmed) {
                        postAction('{{ action([\App\Http\Controllers\AttendanceController::class, "clockOut"]) }}');
                    }
                });
            });

            loadStatus();
            setInterval(updateTimers, 1000);
        });
    </script>
    @endcan

@endsection
