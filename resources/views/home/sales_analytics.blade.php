@extends('layouts.app')
@section('title', __('lang_v1.sales_dashboard'))

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

                @if (auth()->user()->can('dashboard.data') && $is_admin)
                    @php
                        $card_sells = action([\App\Http\Controllers\SellController::class, 'index']);
                        $pipeline_stage_meta = [
                            'draft' => ['label' => __('sale.draft'), 'color' => '#3b82f6'],
                            'quotation' => ['label' => __('lang_v1.quotation'), 'color' => '#8b5cf6'],
                            'proforma' => ['label' => __('lang_v1.proforma'), 'color' => '#14b8a6'],
                            'final_due' => ['label' => __('lang_v1.due'), 'color' => '#f59e0b'],
                            'final_partial' => ['label' => __('lang_v1.partial'), 'color' => '#f97316'],
                            'final_paid' => ['label' => __('lang_v1.paid'), 'color' => '#22c55e'],
                        ];
                    @endphp

                    @if ((auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view')) && !empty($all_locations))
                    <div class="japs-dash-kpi-row">
                        <a href="{{ $card_sells }}" class="japs-dash-kpi-card japs-dash-kpi-card--blue" title="{{ __('home.total_orders') }}">
                            <div class="japs-dash-kpi-card__top">
                                <span class="japs-dash-kpi-card__icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M9 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3"/><path d="M9 12h6M9 16h6M9 8h1"/></svg>
                                </span>
                                <p class="japs-dash-kpi-card__label">{{ __('home.total_orders') }}</p>
                            </div>
                            <p class="japs-dash-kpi-card__value total_orders"></p>
                            <p class="japs-dash-kpi-card__trend">
                                <span class="japs-dash-kpi-card__delta" data-kpi="total_orders">—</span>
                                <span class="japs-dash-kpi-card__period">{{ __('lang_v1.from_previous_period') }}</span>
                            </p>
                            <div class="japs-dash-kpi-card__sparkline">{!! $kpi_sparkline_orders->container() !!}</div>
                        </a>
                        <a href="{{ $card_sells }}" class="japs-dash-kpi-card japs-dash-kpi-card--violet" title="{{ __('home.total_revenue') }}">
                            <div class="japs-dash-kpi-card__top">
                                <span class="japs-dash-kpi-card__icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                </span>
                                <p class="japs-dash-kpi-card__label">{{ __('home.total_revenue') }}</p>
                            </div>
                            <p class="japs-dash-kpi-card__value total_sell"></p>
                            <p class="japs-dash-kpi-card__trend">
                                <span class="japs-dash-kpi-card__delta" data-kpi="total_sell">—</span>
                                <span class="japs-dash-kpi-card__period">{{ __('lang_v1.from_previous_period') }}</span>
                            </p>
                            <div class="japs-dash-kpi-card__sparkline">{!! $kpi_sparkline_revenue->container() !!}</div>
                        </a>
                        <a href="{{ $card_sells }}" class="japs-dash-kpi-card japs-dash-kpi-card--teal" title="{{ __('home.average_order_value') }}">
                            <div class="japs-dash-kpi-card__top">
                                <span class="japs-dash-kpi-card__icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                                </span>
                                <p class="japs-dash-kpi-card__label">{{ __('home.average_order_value') }}</p>
                            </div>
                            <p class="japs-dash-kpi-card__value avg_order_value"></p>
                            <p class="japs-dash-kpi-card__trend">
                                <span class="japs-dash-kpi-card__delta" data-kpi="avg_order_value">—</span>
                                <span class="japs-dash-kpi-card__period">{{ __('lang_v1.from_previous_period') }}</span>
                            </p>
                            <div class="japs-dash-kpi-card__sparkline">{!! $kpi_sparkline_aov->container() !!}</div>
                        </a>
                        <div class="japs-dash-kpi-card japs-dash-kpi-card--amber" title="{{ __('home.fulfillment_time') }}">
                            <div class="japs-dash-kpi-card__top">
                                <span class="japs-dash-kpi-card__icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                                </span>
                                <p class="japs-dash-kpi-card__label">{{ __('home.fulfillment_time') }}</p>
                            </div>
                            <p class="japs-dash-kpi-card__value"><span class="avg_payment_hours"></span> {{ __('home.hrs_short') }}</p>
                            <p class="japs-dash-kpi-card__trend">
                                <span class="japs-dash-kpi-card__delta" data-kpi="avg_payment_hours">—</span>
                                <span class="japs-dash-kpi-card__period">{{ __('lang_v1.from_previous_period') }}</span>
                            </p>
                            <div class="japs-dash-kpi-card__sparkline">{!! $kpi_sparkline_payment_hours->container() !!}</div>
                        </div>
                    </div>

                    <div class="japs-dash-row-3col">
                        <div class="japs-dash-chart-card japs-dash-chart-card--orders-trend">
                            <div class="japs-dash-chart-card__header">
                                <h3 class="japs-dash-chart-card__title">{{ __('home.orders_over_time') }}</h3>
                                <span class="japs-dash-period-chip">{{ __('home.last_30_days') }}</span>
                            </div>
                            <div class="japs-dash-chart-card__canvas">
                                {!! $orders_chart->container() !!}
                            </div>
                        </div>
                        <div class="japs-dash-chart-card japs-dash-chart-card--orders-source">
                            <div class="japs-dash-chart-card__header">
                                <h3 class="japs-dash-chart-card__title">{{ __('home.orders_by_source') }}</h3>
                            </div>
                            <div class="japs-dash-source-body">
                                <div class="japs-dash-payment-chart-wrap">
                                    <div class="japs-dash-payment-chart">
                                        {!! $location_donut->container() !!}
                                    </div>
                                    <div class="japs-dash-donut-center">
                                        <div class="japs-dash-donut-center__amount">{{ number_format(collect($orders_by_location)->sum('count')) }}</div>
                                        <div class="japs-dash-donut-center__label">{{ __('home.total_orders') }}</div>
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
                        <div class="japs-dash-chart-card japs-dash-chart-card--pipeline">
                            <div class="japs-dash-chart-card__header">
                                <h3 class="japs-dash-chart-card__title">{{ __('home.fulfillment_pipeline') }}</h3>
                            </div>
                            <div class="japs-dash-bar-list">
                                @foreach ($pipeline_stage_meta as $stage_key => $stage_meta)
                                    @php $stage_count = $fulfillment_pipeline[$stage_key] ?? 0; @endphp
                                    <div class="japs-dash-bar-list__row">
                                        <span class="japs-dash-bar-list__label">{{ $stage_meta['label'] }}</span>
                                        <span class="japs-dash-bar-list__track">
                                            <span class="japs-dash-bar-list__fill" style="width: {{ $fulfillment_pipeline_max > 0 ? round(($stage_count / $fulfillment_pipeline_max) * 100, 1) : 0 }}%; background: {{ $stage_meta['color'] }};"></span>
                                        </span>
                                        <span class="japs-dash-bar-list__value">{{ number_format($stage_count) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="japs-dash-row-4col">
                        <div class="japs-dash-chart-card japs-dash-chart-card--status">
                            <div class="japs-dash-chart-card__header">
                                <h3 class="japs-dash-chart-card__title">{{ __('home.orders_by_status') }}</h3>
                            </div>
                            <div class="japs-dash-chart-card__canvas">
                                <div id="orders_status_weekly_chart" style="min-height:240px;height:100%;"></div>
                            </div>
                        </div>
                        <div class="japs-dash-chart-card japs-dash-chart-card--top-products">
                            <div class="japs-dash-chart-card__header">
                                <h3 class="japs-dash-chart-card__title">{{ __('home.top_products') }}</h3>
                                <span class="japs-dash-period-chip">{{ __('home.by_units_sold') }}</span>
                            </div>
                            <div class="japs-dash-bar-list">
                                @forelse ($top_products as $tp_idx => $product)
                                    @php
                                        $tp_img = asset('/img/default.png');
                                        if (! empty($product->product_image)) {
                                            $tp_path = public_path('uploads/'.config('constants.product_img_path').'/'.$product->product_image);
                                            if (is_file($tp_path)) {
                                                $tp_img = asset('/uploads/'.config('constants.product_img_path').'/'.rawurlencode($product->product_image));
                                            }
                                        }
                                    @endphp
                                    <div class="japs-dash-bar-list__row japs-dash-bar-list__row--product">
                                        <span class="japs-dash-product-avatar">
                                            <img src="{{ $tp_img }}" alt="" onerror="this.src='{{ asset('/img/default.png') }}'">
                                        </span>
                                        <span class="japs-dash-bar-list__label" title="{{ $product->product }}">{{ \Illuminate\Support\Str::limit($product->product, 22) }}</span>
                                        <span class="japs-dash-bar-list__track">
                                            <span class="japs-dash-bar-list__fill" style="width: {{ $top_products_max > 0 ? round(($product->total_unit_sold / $top_products_max) * 100, 1) : 0 }}%; background: #3b82f6;"></span>
                                        </span>
                                        <span class="japs-dash-bar-list__value">{{ @format_quantity($product->total_unit_sold) }}</span>
                                    </div>
                                @empty
                                    <p class="japs-dash-payment-empty">{{ __('lang_v1.no_data') }}</p>
                                @endforelse
                            </div>
                        </div>
                        <div class="japs-dash-chart-card japs-dash-chart-card--sales japs-dash-chart-card--revenue zoho-dashboard__main-chart">
                            <div class="japs-dash-chart-card__header">
                                <h3 class="japs-dash-chart-card__title">{{ __('home.revenue_trend') }}</h3>
                                <span class="japs-dash-period-chip">{{ __('home.last_30_days') }}</span>
                            </div>
                            <div class="japs-dash-chart-panes">
                                <div class="japs-dash-chart-pane is-active" id="japs_dash_main_chart_wrap" data-period="30d">
                                    {!! $sells_chart_1->container() !!}
                                </div>
                                @if (!empty($sells_chart_2))
                                <div class="japs-dash-chart-pane" id="japs_dash_fy_chart_wrap" data-period="fy" hidden>
                                    {!! $sells_chart_2->container() !!}
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="japs-dash-chart-card japs-dash-chart-card--location-perf">
                            <div class="japs-dash-chart-card__header">
                                <h3 class="japs-dash-chart-card__title">{{ __('home.location_performance') }}</h3>
                            </div>
                            <div class="japs-dash-loc-grid">
                                @php
                                    $loc_chg = $location_performance['change_pct'] ?? [];
                                    $fmtLocDelta = function ($pct, $invert = false) {
                                        $num = (float) $pct;
                                        $arrow = $num >= 0 ? '↑' : '↓';
                                        $good = $invert ? ($num <= 0) : ($num >= 0);
                                        return [
                                            'text' => $arrow.' '.abs($num).'%',
                                            'cls' => $good ? 'is-up' : 'is-down',
                                        ];
                                    };
                                    $dActive = $fmtLocDelta($loc_chg['active_locations'] ?? 0);
                                    $dSales = $fmtLocDelta($loc_chg['sales_today'] ?? 0);
                                    $dPay = $fmtLocDelta($loc_chg['avg_payment_hours'] ?? 0, true);
                                    $dColl = $fmtLocDelta($loc_chg['collection_rate'] ?? 0);
                                @endphp
                                <div class="japs-dash-loc-tile japs-dash-loc-tile--blue">
                                    <span class="japs-dash-loc-tile__icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-5.686-7-11a7 7 0 0 1 14 0c0 5.314-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                    </span>
                                    <span class="japs-dash-loc-tile__label">{{ __('home.active_locations') }}</span>
                                    <span class="japs-dash-loc-tile__value">{{ $location_performance['active_locations'] }}/{{ $location_performance['total_locations'] }}</span>
                                    <span class="japs-dash-loc-tile__trend {{ $dActive['cls'] }}">{{ $dActive['text'] }} <small>{{ __('home.vs_last_period') }}</small></span>
                                </div>
                                <div class="japs-dash-loc-tile japs-dash-loc-tile--teal">
                                    <span class="japs-dash-loc-tile__icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                                    </span>
                                    <span class="japs-dash-loc-tile__label">{{ __('home.sales_today') }}</span>
                                    <span class="japs-dash-loc-tile__value">{{ number_format($location_performance['sales_today']) }}</span>
                                    <span class="japs-dash-loc-tile__trend {{ $dSales['cls'] }}">{{ $dSales['text'] }} <small>{{ __('home.vs_yesterday') }}</small></span>
                                </div>
                                <div class="japs-dash-loc-tile japs-dash-loc-tile--amber">
                                    <span class="japs-dash-loc-tile__icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                                    </span>
                                    <span class="japs-dash-loc-tile__label">{{ __('home.avg_payment_short') }}</span>
                                    <span class="japs-dash-loc-tile__value">{{ $location_performance['avg_payment_hours'] }} {{ __('home.hrs_short') }}</span>
                                    <span class="japs-dash-loc-tile__trend {{ $dPay['cls'] }}">{{ $dPay['text'] }} <small>{{ __('home.vs_last_period') }}</small></span>
                                </div>
                                <div class="japs-dash-loc-tile japs-dash-loc-tile--rose">
                                    <span class="japs-dash-loc-tile__icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
                                    </span>
                                    <span class="japs-dash-loc-tile__label">{{ __('home.collection_rate') }}</span>
                                    <span class="japs-dash-loc-tile__value">{{ $location_performance['collection_rate'] }}%</span>
                                    <span class="japs-dash-loc-tile__trend {{ $dColl['cls'] }}">{{ $dColl['text'] }} <small>{{ __('home.vs_last_period') }}</small></span>
                                </div>
                            </div>
                        </div>
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
                        if (
                            $el.closest('.japs-dash-kpi-card__sparkline').length ||
                            $el.closest('.japs-dash-chart-card--orders-source').length
                        ) {
                            try {
                                chart.setSize(null, null, false);
                                chart.reflow();
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
                                series: { animation: false }
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
                                    animation: false,
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
