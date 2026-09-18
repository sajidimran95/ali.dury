@extends('layouts.app')
@section('title', __('lang_v1.sales_dashboard'))

@section('content')

    @if (auth()->user()->can('dashboard.data'))
        <div class="japs-dashboard-page japs-sales-dash">
        <div class="zoho-dashboard__layout">
            <div class="zoho-dashboard__main">
    @endif

    <div class="zoho-dashboard">
        <div class="zoho-dashboard__header japs-dashboard-hero">
            <div class="zoho-dashboard__header-inner">
                <div class="zoho-dashboard__title-row">
                    <div class="zoho-dashboard__greeting">
                        <div>
                            <h1 class="japs-dashboard-welcome zoho-dashboard__welcome">
                                {{ __('home.sales_fulfillment_analytics') }}
                            </h1>
                        </div>
                    </div>

                    <div class="zoho-dashboard__toolbar japs-dash-toolbar">
                        @if (auth()->user()->can('dashboard.data') && $is_admin)
                            <button type="button" id="dashboard_date_filter"
                                class="japs-dashboard-filter-btn zoho-dashboard__filter-btn">
                                <svg aria-hidden="true" class="tw-size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"/><path d="M16 3v4M8 3v4M4 11h16"/></svg>
                                <span id="dashboard_date_filter_label">{{ \Carbon::now()->subDays(29)->format('M j') }} – {{ \Carbon::now()->format('M j, Y') }}</span>
                                <svg aria-hidden="true" class="tw-size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M6 9l6 6l6 -6"/></svg>
                            </button>
                            <div class="zoho-dashboard__toolbar-item japs-dash-filter-wrap">
                                <span class="japs-dash-filter-affix" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
                                </span>
                                {!! Form::select('dashboard_location', $all_locations, null, [
                                    'class' => 'form-control select2 zoho-dashboard__select',
                                    'placeholder' => __('home.filters'),
                                    'id' => 'dashboard_location',
                                ]) !!}
                            </div>
                            <button type="button" id="dashboard_export_btn" class="japs-dashboard-filter-btn">
                                <i class="fas fa-download" aria-hidden="true"></i>
                                <span>{{ __('lang_v1.export') }}</span>
                                <svg aria-hidden="true" class="tw-size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M6 9l6 6l6 -6"/></svg>
                            </button>
                        @endif
                    </div>
                </div>

                @if (auth()->user()->can('dashboard.data') && $is_admin)
                    @php
                        $card_sells = action([\App\Http\Controllers\SellController::class, 'index']);
                        $pipe_ordered = (int) (($fulfillment_pipeline['draft'] ?? 0) + ($fulfillment_pipeline['quotation'] ?? 0));
                        $pipe_processing = (int) ($fulfillment_pipeline['proforma'] ?? 0);
                        $pipe_shipped = (int) ($fulfillment_pipeline['final_due'] ?? 0);
                        $pipe_delivered = (int) ($fulfillment_pipeline['final_paid'] ?? 0);
                        $pipe_returned = (int) ($fulfillment_pipeline['final_partial'] ?? 0);
                        $pipeline_rows = [
                            ['label' => 'Ordered', 'count' => $pipe_ordered, 'color' => '#60a5fa'],
                            ['label' => 'Processing', 'count' => $pipe_processing, 'color' => '#fbbf24'],
                            ['label' => 'Shipped', 'count' => $pipe_shipped, 'color' => '#fb923c'],
                            ['label' => 'Delivered', 'count' => $pipe_delivered, 'color' => '#34d399'],
                            ['label' => 'Returned', 'count' => $pipe_returned, 'color' => '#f87171'],
                        ];
                        $pipeline_max = max(1, max(array_column($pipeline_rows, 'count')));
                        $status_paid = $pipe_delivered;
                        $status_pending = $pipe_ordered + $pipe_processing + $pipe_shipped;
                        $status_failed = $pipe_returned;
                        $status_total = max(1, $status_paid + $status_pending + $status_failed);
                        $status_slices = [
                            ['label' => 'Paid', 'count' => $status_paid, 'color' => '#3b82f6'],
                            ['label' => 'Pending', 'count' => $status_pending, 'color' => '#f59e0b'],
                            ['label' => 'Failed', 'count' => $status_failed, 'color' => '#ef4444'],
                        ];
                        $top_product_name = !empty($top_products[0]) ? $top_products[0]->product : null;
                        $sale_edit_insights = $sale_edit_insights ?? [];
                        $sale_edit_activities = $sale_edit_activities ?? [];
                        $show_all_edits = $show_all_edits ?? false;
                        $view_all_edits_url = $view_all_edits_url ?? '#japs-sf-activity';
                        $activity_log_url = $activity_log_url ?? '#';
                    @endphp

                    @if ((auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view')) && !empty($all_locations))
                    <div class="japs-sf-shell">
                    <div class="japs-sf-main">
                    <div class="japs-dash-kpi-row">
                        <a href="{{ $card_sells }}" class="japs-dash-kpi-card japs-dash-kpi-card--blue" title="{{ __('home.total_orders') }}">
                            <p class="japs-dash-kpi-card__label">{{ __('home.total_orders') }}</p>
                            <p class="japs-dash-kpi-card__value total_orders"></p>
                            <p class="japs-dash-kpi-card__trend">
                                <span class="japs-dash-kpi-card__delta" data-kpi="total_orders">—</span>
                                <span class="japs-dash-kpi-card__period">{{ __('lang_v1.from_previous_period') }}</span>
                            </p>
                            <div class="japs-dash-kpi-card__sparkline">{!! $kpi_sparkline_orders->container() !!}</div>
                        </a>
                        <a href="{{ $card_sells }}" class="japs-dash-kpi-card japs-dash-kpi-card--violet" title="{{ __('home.total_sales') }}">
                            <p class="japs-dash-kpi-card__label">{{ __('home.total_sales') }}</p>
                            <p class="japs-dash-kpi-card__value total_sell"></p>
                            <p class="japs-dash-kpi-card__trend">
                                <span class="japs-dash-kpi-card__delta" data-kpi="total_sell">—</span>
                                <span class="japs-dash-kpi-card__period">{{ __('lang_v1.from_previous_period') }}</span>
                            </p>
                            <div class="japs-dash-kpi-card__sparkline">{!! $kpi_sparkline_revenue->container() !!}</div>
                        </a>
                        <a href="{{ $card_sells }}" class="japs-dash-kpi-card japs-dash-kpi-card--teal" title="{{ __('home.average_order_value') }}">
                            <p class="japs-dash-kpi-card__label">{{ __('home.average_order_value') }}</p>
                            <p class="japs-dash-kpi-card__value avg_order_value"></p>
                            <p class="japs-dash-kpi-card__trend">
                                <span class="japs-dash-kpi-card__delta" data-kpi="avg_order_value">—</span>
                                <span class="japs-dash-kpi-card__period">{{ __('lang_v1.from_previous_period') }}</span>
                            </p>
                            <div class="japs-dash-kpi-card__sparkline">{!! $kpi_sparkline_aov->container() !!}</div>
                        </a>
                        <div class="japs-dash-kpi-card japs-dash-kpi-card--amber" title="{{ __('home.refund_rate') }}">
                            <p class="japs-dash-kpi-card__label">{{ __('home.refund_rate') }}</p>
                            <p class="japs-dash-kpi-card__value refund_rate">0%</p>
                            <p class="japs-dash-kpi-card__trend">
                                <span class="japs-dash-kpi-card__delta" data-kpi="refund_rate">—</span>
                                <span class="japs-dash-kpi-card__period">{{ __('lang_v1.from_previous_period') }}</span>
                            </p>
                            <div class="japs-dash-kpi-card__sparkline">{!! $kpi_sparkline_payment_hours->container() !!}</div>
                        </div>
                    </div>

                    <div class="japs-dash-row-3col">
                        <div class="japs-dash-chart-card japs-dash-chart-card--sales japs-dash-chart-card--revenue zoho-dashboard__main-chart">
                            <div class="japs-dash-chart-card__header">
                                <h3 class="japs-dash-chart-card__title">{{ __('home.sales_over_time') }}</h3>
                                <span class="japs-dash-period-chip">Daily</span>
                            </div>
                            <div class="japs-dash-chart-card__canvas">
                                {!! $sells_chart_1->container() !!}
                            </div>
                        </div>
                        <div class="japs-dash-chart-card japs-dash-chart-card--status">
                            <div class="japs-dash-chart-card__header">
                                <h3 class="japs-dash-chart-card__title">{{ __('home.orders_by_status') }}</h3>
                            </div>
                            <div class="japs-dash-source-body">
                                <div class="japs-dash-payment-chart-wrap">
                                    <svg class="japs-sf-donut" viewBox="0 0 42 42" aria-hidden="true">
                                        @php
                                            $circ = 2 * 3.1416 * 15.5;
                                            $off = 0;
                                        @endphp
                                        <circle cx="21" cy="21" r="15.5" fill="none" stroke="#eef2f7" stroke-width="5"></circle>
                                        @foreach ($status_slices as $slice)
                                            @php
                                                $len = $status_total > 0 ? ($slice['count'] / $status_total) * $circ : 0;
                                            @endphp
                                            <circle cx="21" cy="21" r="15.5" fill="none" stroke="{{ $slice['color'] }}" stroke-width="5"
                                                stroke-dasharray="{{ $len }} {{ $circ }}" stroke-dashoffset="{{ -$off }}" transform="rotate(-90 21 21)"></circle>
                                            @php $off += $len; @endphp
                                        @endforeach
                                        <text x="21" y="20" text-anchor="middle" dominant-baseline="middle" fill="#0f172a" font-size="7" font-weight="800">{{ number_format($status_paid + $status_pending + $status_failed) }}</text>
                                        <text x="21" y="25.8" text-anchor="middle" fill="#64748b" font-size="3.2" font-weight="500">Total</text>
                                    </svg>
                                </div>
                                <ul class="japs-dash-source-legend">
                                    @foreach ($status_slices as $slice)
                                        <li class="japs-dash-source-legend__item">
                                            <span class="japs-dash-source-legend__swatch" style="background: {{ $slice['color'] }}"></span>
                                            <span class="japs-dash-source-legend__name">{{ $slice['label'] }}</span>
                                            <span class="japs-dash-source-legend__nums">
                                                <b>{{ number_format($slice['count']) }}</b>
                                                <em>{{ $status_total > 0 ? round(($slice['count'] / $status_total) * 100, 1) : 0 }}%</em>
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <div class="japs-dash-chart-card japs-dash-chart-card--pipeline">
                            <div class="japs-dash-chart-card__header">
                                <h3 class="japs-dash-chart-card__title">{{ __('home.fulfillment_pipeline') }}</h3>
                            </div>
                            <div class="japs-dash-bar-list">
                                @foreach ($pipeline_rows as $row)
                                    <div class="japs-dash-bar-list__row">
                                        <span class="japs-dash-bar-list__label">{{ $row['label'] }}</span>
                                        <span class="japs-dash-bar-list__track">
                                            <span class="japs-dash-bar-list__fill" style="width: {{ round(($row['count'] / $pipeline_max) * 100, 1) }}%; background: {{ $row['color'] }};"></span>
                                        </span>
                                        <span class="japs-dash-bar-list__value">{{ number_format($row['count']) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="japs-dash-row-3col japs-sf-bottom">
                        <div class="japs-dash-chart-card japs-dash-chart-card--orders-source">
                            <div class="japs-dash-chart-card__header">
                                <h3 class="japs-dash-chart-card__title">{{ __('home.orders_by_source') }}</h3>
                                <span class="japs-dash-period-chip">{{ __('home.by_sales') }}</span>
                            </div>
                            <div class="japs-dash-source-body">
                                <div class="japs-dash-payment-chart-wrap">
                                    <div class="japs-dash-payment-chart">
                                        {!! $location_donut->container() !!}
                                    </div>
                                </div>
                                <ul class="japs-dash-source-legend">
                                    @forelse ($orders_by_location as $loc)
                                        <li class="japs-dash-source-legend__item">
                                            <span class="japs-dash-source-legend__swatch" style="background: {{ $loc['color'] }}"></span>
                                            <span class="japs-dash-source-legend__name">{{ $loc['label'] }}</span>
                                            <span class="japs-dash-source-legend__nums">
                                                <b>{{ number_format($loc['count']) }}</b>
                                                @if($loc['pct'] > 0)<em>{{ $loc['pct'] }}%</em>@endif
                                            </span>
                                        </li>
                                    @empty
                                        <p class="japs-dash-payment-empty">{{ __('lang_v1.no_data') }}</p>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                        <div class="japs-dash-chart-card japs-dash-chart-card--top-products">
                            <div class="japs-dash-chart-card__header">
                                <h3 class="japs-dash-chart-card__title">{{ __('home.top_products') }}</h3>
                                <span class="japs-dash-period-chip">{{ __('home.by_sales') }}</span>
                            </div>
                            <div class="japs-dash-bar-list">
                                @forelse ($top_products as $tp_idx => $product)
                                    @php
                                        $bar_colors = ['#3b82f6', '#8b5cf6', '#14b8a6', '#22c55e', '#94a3b8'];
                                    @endphp
                                    <div class="japs-dash-bar-list__row">
                                        <span class="japs-dash-bar-list__label" title="{{ $product->product }}">{{ ($tp_idx + 1) }}. {{ \Illuminate\Support\Str::limit($product->product, 22) }}</span>
                                        <span class="japs-dash-bar-list__track">
                                            <span class="japs-dash-bar-list__fill" style="width: {{ $top_products_max > 0 ? round(($product->total_unit_sold / $top_products_max) * 100, 1) : 0 }}%; background: {{ $bar_colors[$tp_idx % 5] }};"></span>
                                        </span>
                                        <span class="japs-dash-bar-list__value">{{ @format_quantity($product->total_unit_sold) }}</span>
                                    </div>
                                @empty
                                    <p class="japs-dash-payment-empty">{{ __('lang_v1.no_data') }}</p>
                                @endforelse
                            </div>
                        </div>
                        <div class="japs-dash-chart-card japs-dash-chart-card--orders-trend">
                            <div class="japs-dash-chart-card__header">
                                <h3 class="japs-dash-chart-card__title">{{ __('home.orders_per_day') }}</h3>
                                <span class="japs-dash-period-chip">Daily</span>
                            </div>
                            <div class="japs-dash-chart-card__canvas">
                                {!! $orders_chart->container() !!}
                            </div>
                        </div>
                    </div>
                    </div>

                    <aside class="japs-sf-aside">
                        <div class="japs-dash-chart-card japs-sf-activity" id="japs-sf-activity">
                            <div class="japs-dash-chart-card__header">
                                <h3 class="japs-dash-chart-card__title">{{ __('home.insights_actions') }}</h3>
                                <a href="{{ $view_all_edits_url }}" class="japs-dash-period-chip">{{ __('home.view_all') }}</a>
                            </div>
                            <ul class="japs-sf-activity__list">
                                @forelse ($sale_edit_activities as $act)
                                    <li>
                                        <span class="japs-sf-activity__dot {{ $act->dot }}"></span>
                                        <span class="japs-sf-activity__text">
                                            {{ $act->when }}
                                            · <b>{{ $act->kind }} {{ $act->invoice_no }}</b>
                                            · {{ $act->product }}
                                            · {{ $act->change }}
                                            · by {{ $act->editor }}
                                        </span>
                                        @if (!empty($act->view_url))
                                            <a href="{{ $act->view_url }}" class="japs-sf-activity__view" title="{{ __('messages.view') }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                        @endif
                                    </li>
                                @empty
                                    <li class="japs-sf-activity__empty">{{ __('lang_v1.no_data') }}</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="japs-dash-chart-card japs-sf-channels">
                            <div class="japs-dash-chart-card__header">
                                <h3 class="japs-dash-chart-card__title">{{ __('home.top_products_channels') }}</h3>
                                <span class="japs-dash-period-chip">{{ __('home.by_sales') }}</span>
                            </div>
                            <div class="japs-sf-channels__body">
                                @php
                                    $side_colors = ['#3b82f6', '#8b5cf6', '#14b8a6', '#22c55e', '#94a3b8'];
                                    $side_units_total = max(1, (float) collect($top_products)->sum('total_unit_sold'));
                                    $side_products = collect($top_products)->take(4);
                                    $side_ch_total = max(1, collect($orders_by_location)->sum('count'));
                                @endphp
                                <ul class="japs-sf-rank">
                                    @forelse ($side_products as $i => $product)
                                        @php $pct = round(((float) $product->total_unit_sold / $side_units_total) * 100, 1); @endphp
                                        <li>
                                            <span class="japs-sf-rank__n">{{ $i + 1 }}.</span>
                                            <span class="japs-sf-rank__name" title="{{ $product->product }}">{{ \Illuminate\Support\Str::limit($product->product, 22) }}</span>
                                            <span class="japs-sf-rank__val">{{ @format_quantity($product->total_unit_sold) }}</span>
                                            <span class="japs-sf-rank__pct">{{ $pct }}%</span>
                                            <span class="japs-sf-rank__bar"><i style="width: {{ $pct }}%; background: {{ $side_colors[$i % 5] }};"></i></span>
                                        </li>
                                    @empty
                                        <li class="japs-sf-rank__empty">{{ __('lang_v1.no_data') }}</li>
                                    @endforelse
                                </ul>
                                <p class="japs-sf-channels__kicker">Channels</p>
                                <ul class="japs-sf-rank japs-sf-rank--ch">
                                    @foreach ($orders_by_location as $ch)
                                        <li>
                                            <span class="japs-sf-rank__dot" style="background: {{ $ch['color'] }}"></span>
                                            <span class="japs-sf-rank__name">{{ $ch['label'] }}</span>
                                            <span class="japs-sf-rank__val">{{ number_format($ch['count']) }}</span>
                                            <span class="japs-sf-rank__pct">{{ $ch['pct'] }}%</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </aside>
                    </div>
                    @endif

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
                        <h3 class="japs-dash-recent-card__title">{{ __('home.recent_orders') }}</h3>
                        <div class="japs-dash-recent-card__actions">
                            @if (count($all_locations) > 1)
                                {!! Form::select('recent_sales_location', $all_locations, null, [
                                    'class' => 'form-control select2 japs-dash-recent-filter',
                                    'placeholder' => __('lang_v1.all_locations'),
                                    'id' => 'recent_sales_location',
                                ]) !!}
                            @endif
                            <button type="button" id="recent_orders_export_btn" class="hide" aria-hidden="true"></button>
                            <a href="{{ action([\App\Http\Controllers\SellController::class, 'index']) }}" class="japs-dash-recent-viewall">
                                {{ __('home.view_all_orders') }} →
                            </a>
                        </div>
                    </div>
                    <div class="japs-dash-full-table">
                        <table class="table table-bordered table-striped ajax_view" id="recent_sales_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>@lang('sale.invoice_no')</th>
                                    <th>@lang('contact.customer')</th>
                                    <th>@lang('home.source')</th>
                                    <th>@lang('sale.payment_status')</th>
                                    <th>@lang('sale.total')</th>
                                    <th>@lang('messages.date')</th>
                                    <th class="not-export">@lang('messages.action')</th>
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
    <link href="{{ asset('css/japs-sales-dash.css?v=' . ($asset_v ?? '') . '&sd=27') }}" rel="stylesheet">
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
        <div hidden>{!! $sells_chart_2->container() !!}</div>
        {!! $sells_chart_1->script() !!}
        {!! $sells_chart_2->script() !!}
        {!! $orders_chart->script() !!}
        {!! $location_donut->script() !!}
        {!! $kpi_sparkline_orders->script() !!}
        {!! $kpi_sparkline_revenue->script() !!}
        {!! $kpi_sparkline_aov->script() !!}
        {!! $kpi_sparkline_payment_hours->script() !!}
        <script type="text/javascript">
            // Reflow KPI sparklines + Orders-by-Source donut after layout settles
            $(function () {
                function reflowDashCharts() {
                    if (typeof Highcharts === 'undefined') {
                        return;
                    }
                    (Highcharts.charts || []).forEach(function (chart) {
                        if (!chart || !chart.renderTo) {
                            return;
                        }
                        var $el = $(chart.renderTo);
                        if ($el.closest('.japs-dash-kpi-card__sparkline').length) {
                            try {
                                var w = $el.parent().width() || $el.width() || null;
                                chart.setSize(w, 44, false);
                            } catch (e) {}
                            return;
                        }
                        if ($el.closest('.japs-dash-chart-card--orders-source').length) {
                            try {
                                chart.setSize(null, null, false);
                                chart.reflow();
                            } catch (e) {}
                            return;
                        }
                        if ($el.closest('.japs-dash-chart-card--orders-trend').length) {
                            try {
                                var $box = $el.closest('.japs-dash-chart-card__canvas');
                                chart.setSize($box.width() || null, 200, false);
                            } catch (e) {}
                        }
                    });
                }
                setTimeout(reflowDashCharts, 80);
                setTimeout(reflowDashCharts, 350);
                setTimeout(reflowDashCharts, 800);
            });
        </script>
        <script type="text/javascript">
            // Defined at top level (not inside $(document).ready) so it already exists
            // by the time home.js's own ready handler calls japsLoadDashboardCharts().
            window.japsLoadOrdersByStatusWeekly = function (locationId) {
                if (!$('#orders_status_weekly_chart').length || typeof Highcharts === 'undefined') {
                    return;
                }
                $.get('/home/orders-by-status-weekly', { location_id: locationId || '' }, function (data) {
                        if (!data) {
                            return;
                        }
                        if (window.japsOrdersStatusChart) {
                            try {
                                window.japsOrdersStatusChart.destroy();
                            } catch (e) {}
                        }
                        var statusColors = ['#3b82f6', '#8b5cf6', '#14b8a6', '#f97316', '#f59e0b', '#22c55e'];
                        var thisWeek = (data.this_week || []).map(function (v, i) {
                            return { y: v, color: statusColors[i % statusColors.length] };
                        });
                        window.japsOrdersStatusChart = Highcharts.chart('orders_status_weekly_chart', {
                            chart: {
                                type: 'bar',
                                backgroundColor: 'transparent',
                                spacingLeft: 4,
                                spacingRight: 12,
                                style: { fontFamily: 'Inter, "Segoe UI Variable", "Segoe UI", Roboto, Helvetica, Arial, sans-serif' },
                                events: {
                                    load: function () {
                                        var chart = this;
                                        if (!chart.renderer || !chart.renderer.defs) {
                                            return;
                                        }
                                        var patternId = 'japs-status-hatch';
                                        if (!chart.renderer.box.querySelector('#' + patternId)) {
                                            var pattern = chart.renderer.createElement('pattern').attr({
                                                id: patternId,
                                                patternUnits: 'userSpaceOnUse',
                                                width: 6,
                                                height: 6
                                            }).add(chart.renderer.defs);
                                            chart.renderer.rect(0, 0, 6, 6).attr({ fill: '#eef2f7' }).add(pattern);
                                            chart.renderer.path({
                                                d: 'M0,6 l6,-6 M-1.5,1.5 l3,-3 M4.5,7.5 l3,-3',
                                                stroke: '#cbd5e1',
                                                'stroke-width': 1.2
                                            }).add(pattern);
                                        }
                                        var hatch = 'url(#' + patternId + ')';
                                        if (chart.series && chart.series[0]) {
                                            var hatched = (data.last_week || []).map(function (v, i) {
                                                return {
                                                    y: v,
                                                    color: hatch,
                                                    borderColor: statusColors[i % statusColors.length],
                                                    borderWidth: 1
                                                };
                                            });
                                            chart.series[0].setData(hatched, true);
                                        }
                                    }
                                }
                            },
                            title: { text: null },
                            credits: { enabled: false },
                            exporting: { enabled: false },
                            xAxis: {
                                categories: data.categories || [],
                                lineColor: 'transparent',
                                tickLength: 0,
                                labels: { style: { color: '#64748b', fontSize: '11px', fontWeight: '500' } }
                            },
                            yAxis: {
                                title: { text: null },
                                gridLineColor: '#eef2f7',
                                gridLineWidth: 1,
                                min: 0,
                                labels: { style: { color: '#94a3b8', fontSize: '11px' } }
                            },
                            legend: {
                                reversed: true,
                                itemStyle: { fontSize: '12px', fontWeight: '600', color: '#475569' }
                            },
                            plotOptions: {
                                bar: { borderWidth: 0, borderRadius: 5, groupPadding: 0.16, pointPadding: 0.06 },
                                series: { animation: { duration: 700 } }
                            },
                            tooltip: {
                                backgroundColor: '#3b82f6',
                                borderColor: '#3b82f6',
                                borderRadius: 8,
                                borderWidth: 0,
                                shadow: false,
                                style: { color: '#fff', fontSize: '12px', fontWeight: '700' },
                                padding: 10
                            },
                            series: [
                                {
                                    name: @json(__('home.last_week')),
                                    data: (data.last_week || []).map(function (v) { return { y: v, color: '#e2e8f0' }; })
                                },
                                { name: @json(__('home.this_week')), data: thisWeek }
                            ]
                        });
                    });
            };
        </script>
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

                        chart.update({
                            chart: {
                                backgroundColor: 'transparent',
                                type: 'areaspline',
                                spacingTop: 12,
                                spacingRight: 12,
                                spacingBottom: isYearly ? 8 : 16,
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
                                    rotation: isYearly ? 0 : -30,
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
                                            return '$' + (v / 1000) + 'K';
                                        }
                                        return v;
                                    }
                                }
                            },
                            plotOptions: {
                                areaspline: {
                                    fillOpacity: 0.22,
                                    lineWidth: 2.75,
                                    marker: { enabled: false, radius: 3, states: { hover: { enabled: true } } }
                                },
                                series: {
                                    borderWidth: 0,
                                    animation: { duration: 650 },
                                    color: '#3b82f6',
                                    fillColor: {
                                        linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                                        stops: [
                                            [0, 'rgba(59, 130, 246, 0.28)'],
                                            [1, 'rgba(59, 130, 246, 0.02)']
                                        ]
                                    }
                                }
                            }
                        }, false);
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

                    // Apply chart data that may have arrived before Highcharts finished init
                    if (window.japsRevenueInsightsData && typeof window.japsApplyRevenueInsightsData === 'function') {
                        window.japsApplyRevenueInsightsData(window.japsRevenueInsightsData);
                    } else if (typeof window.japsLoadDashboardCharts === 'function') {
                        window.japsLoadDashboardCharts();
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
                            var sumVals = period === 'fy' ? (cached.monthly_values || []) : (cached.daily_values || []);
                            var sum = 0;
                            for (var si = 0; si < sumVals.length; si++) {
                                sum += parseFloat(sumVals[si]) || 0;
                            }
                            $('.japs-chart-total-sell').html(
                                typeof __currency_trans_from_en === 'function'
                                    ? __currency_trans_from_en(sum, true)
                                    : sum
                            );
                            var locId = $('#dashboard_location').length ? ($('#dashboard_location').val() || '') : '';
                            var endD = moment().format('YYYY-MM-DD');
                            var startD = period === 'fy'
                                ? moment().startOf('year').format('YYYY-MM-DD')
                                : moment().subtract(29, 'days').format('YYYY-MM-DD');
                            $.get('/home/get-totals', { start: startD, end: endD, location_id: locId }, function (tot) {
                                if (tot.change_pct && typeof japsApplyDelta === 'function') {
                                    japsApplyDelta($('.japs-chart-sell-growth'), tot.change_pct.total_sell);
                                }
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
                aaSorting: [[5, 'desc']],
                ajax: {
                    url: '/home/recent-orders',
                    data: function (d) {
                        if ($('#recent_sales_location').length) {
                            d.location_id = $('#recent_sales_location').val();
                        }
                    }
                },
                columnDefs: [{ targets: 6, orderable: false, searchable: false }],
                columns: [
                    { data: 'invoice_no', name: 'invoice_no' },
                    { data: 'name', name: 'name' },
                    { data: 'business_location_badge', name: 'business_location' },
                    { data: 'payment_status', name: 'payment_status' },
                    { data: 'final_total', name: 'final_total' },
                    { data: 'transaction_date', name: 'transaction_date' },
                    { data: 'action', name: 'action' }
                ],
                fnDrawCallback: function () {
                    __currency_convert_recursively($('#recent_sales_table'));
                }
            });
            $('#recent_sales_location').on('change', function () {
                recentSalesTable.ajax.reload();
            });

            // Export (CSV) — reuses the app's global DataTables Buttons extension
            $('#dashboard_export_btn').on('click', function () {
                $('#recent_orders_export_btn').trigger('click');
            });

            if ($.fn.dataTable.Buttons) {
                new $.fn.dataTable.Buttons(recentSalesTable, {
                    buttons: [
                        {
                            extend: 'csvHtml5',
                            className: 'hide',
                            title: @json(__('home.recent_orders')),
                            exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
                        }
                    ]
                });
                $('#recent_orders_export_btn').on('click', function (e) {
                    e.preventDefault();
                    recentSalesTable.button('.buttons-csv').trigger();
                });
            }
        });
    </script>
    @endif

@endsection
