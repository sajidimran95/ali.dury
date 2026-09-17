@extends('layouts.app')
@section('title', __('home.home_dashboard'))

@php
    $hour = (int) \Carbon::now()->format('G');
    if ($hour < 12) {
        $greet_period = __('home.morning');
    } elseif ($hour < 17) {
        $greet_period = __('home.afternoon');
    } else {
        $greet_period = __('home.evening');
    }
    $first_name = explode(' ', trim($display_name ?? ''))[0] ?? ($display_name ?? '');
    $auth_user = auth()->user();
    $avatar = (isset($auth_user->media->display_url) && $auth_user->media->display_url)
        ? $auth_user->media->display_url
        : 'https://ui-avatars.com/api/?name=' . urlencode($first_name) . '&background=1d4ed8&color=fff';
    $sells_url = action([\App\Http\Controllers\SellController::class, 'index']);
    $new_order_url = action([\App\Http\Controllers\SellController::class, 'create']);
    $new_pos_url = action([\App\Http\Controllers\SellPosController::class, 'create']);
    $import_url = action([\App\Http\Controllers\ImportSalesController::class, 'index']);
    $shipments_url = action([\App\Http\Controllers\SellController::class, 'shipments']);
    $week_start = \Carbon::now()->subDays(6);
    $week_end = \Carbon::now();
@endphp

@section('content')
<div class="japs-dashboard-page">
<div class="japs-ops japs-shot">
    <div class="japs-shot__glass">
        <div class="japs-shot__clock">
            @include('home.partials.attendance_clock')
        </div>
        <div class="japs-shot__cols">
            <div class="japs-shot__main">
                <header class="japs-shot__hello">
                    <nav class="japs-ops-crumb">
                        <span>{{ __('home.home') }}</span>
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                        <strong>{{ __('home.home_dashboard') }}</strong>
                    </nav>
                    <h1 class="japs-ops__title"><span class="japs-ops__type" data-type="Good {{ $greet_period }}, {{ $first_name }}">Good {{ $greet_period }}, {{ $first_name }}</span><span class="japs-ops__caret" aria-hidden="true"></span></h1>
                    <p class="japs-ops__greeting">{{ __('home.home_happening') }}</p>
                </header>

                @if (auth()->user()->can('dashboard.data'))
                <div class="japs-shot__stats">
                    <a href="{{ $sells_url }}" class="japs-ops-kpi japs-shot__stat">
                        <span class="japs-ops-kpi__label">{{ __('home.total_orders') }}</span>
                        <span class="japs-ops-kpi__value total_orders">—</span>
                        <span class="japs-ops-kpi__trend"><span class="japs-dash-kpi-card__delta" data-kpi="total_orders">—</span> <em class="japs-ops-kpi__period"></em></span>
                    </a>
                    <a href="{{ $shipments_url }}" class="japs-ops-kpi japs-shot__stat">
                        <span class="japs-ops-kpi__label">{{ __('home.orders_awaiting_fulfillment') }}</span>
                        <span class="japs-ops-kpi__value awaiting_fulfillment">—</span>
                        <span class="japs-ops-kpi__trend"><span class="japs-dash-kpi-card__delta" data-kpi="awaiting_fulfillment">—</span> <em class="japs-ops-kpi__period"></em></span>
                    </a>
                    <div class="japs-ops-kpi japs-shot__stat">
                        <span class="japs-ops-kpi__label">{{ __('home.open_issues') }}</span>
                        <span class="japs-ops-kpi__value open_issues">—</span>
                        <span class="japs-ops-kpi__trend"><span class="japs-dash-kpi-card__delta" data-kpi="open_issues">—</span> <em class="japs-ops-kpi__period"></em></span>
                    </div>
                </div>

                <section class="japs-shot__card japs-shot__bar">
                    <div class="japs-ops-card__head">
                        <h3 class="japs-ops-card__title">{{ __('home.order_volume_trend') }}</h3>
                        <span class="japs-ops-chip is-up" id="ops_orders_growth">—</span>
                    </div>
                    <div id="japs_ops_orders_chart" class="japs-ops-chart japs-ops-chart--bar"></div>
                </section>

                <section class="japs-shot__card japs-shot__insights">
                    <h3 class="japs-ops-card__title">{{ __('home.by_source') }}</h3>
                    <table class="japs-ops-mini-table japs-ops-src-table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>{{ __('home.stage_picking') }}</th>
                                <th>{{ __('home.stage_packing') }}</th>
                                <th>{{ __('home.stage_shipment') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr data-src="online"><td><span class="japs-ops-dot japs-ops-dot--online"></span> {{ __('home.source_online_order') }}s</td><td class="c-pick">0</td><td class="c-pack">0</td><td class="c-ship">0</td></tr>
                            <tr data-src="field"><td><span class="japs-ops-dot japs-ops-dot--field"></span> {{ __('home.source_field_order') }}s</td><td class="c-pick">0</td><td class="c-pack">0</td><td class="c-ship">0</td></tr>
                            <tr data-src="pos"><td><span class="japs-ops-dot japs-ops-dot--pos"></span> {{ __('home.source_pos_order') }}s</td><td class="c-pick">0</td><td class="c-pack">0</td><td class="c-ship">0</td></tr>
                        </tbody>
                    </table>
                </section>
                @endif
            </div>

            <aside class="japs-shot__side">
                <div class="japs-shot__side-top">
                    <svg class="japs-shot__spark" viewBox="0 0 120 56" fill="none" aria-hidden="true">
                        <path d="M4 44 C18 40 22 28 34 30 C46 32 50 18 62 16 C74 14 78 24 90 14 C102 4 110 10 116 8" stroke="#22c55e" stroke-width="3" stroke-linecap="round"/>
                        <circle cx="116" cy="8" r="4" fill="#22c55e"/>
                    </svg>
                    @if (auth()->user()->can('dashboard.data'))
                    <a href="{{ $sells_url }}" class="japs-ops-kpi japs-shot__feat">
                        <img class="japs-shot__avatar" src="{{ $avatar }}" alt="">
                        <span class="japs-shot__feat-copy">
                            <span class="japs-ops-kpi__label">{{ __('home.total_revenue') }}</span>
                            <span class="japs-ops-kpi__value total_sell">—</span>
                            <span class="japs-ops-kpi__trend"><span class="japs-dash-kpi-card__delta" data-kpi="total_sell">—</span> <em class="japs-ops-kpi__period"></em></span>
                        </span>
                    </a>
                    @endif
                </div>
                @if (auth()->user()->can('dashboard.data'))
                <div class="japs-ops__toolbar japs-shot__filters">
                    <button type="button" id="dashboard_date_filter" class="japs-ops__date">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg>
                        <span id="dashboard_date_filter_label">{{ $week_start->format('M j') }} – {{ $week_end->format('M j, Y') }}</span>
                    </button>
                    @if (!empty($all_locations) && count($all_locations) > 1)
                        {!! Form::select('dashboard_location', $all_locations, null, [
                            'class' => 'form-control select2 japs-ops__select',
                            'placeholder' => __('lang_v1.all_locations'),
                            'id' => 'dashboard_location',
                        ]) !!}
                    @endif
                </div>
                <section class="japs-shot__card japs-shot__donut">
                    <div class="japs-ops-card__head">
                        <h3 class="japs-ops-card__title">{{ __('home.fulfillment_overview') }}</h3>
                    </div>
                    <div class="japs-shot__gauge">
                        <div id="japs_ops_donut" class="japs-ops-chart japs-ops-chart--donut"></div>
                        <div class="japs-shot__gauge-value" id="ops_gauge_pct">0%</div>
                    </div>
                    <div class="japs-shot__donut-stats">
                        <div>
                            <span>{{ __('home.stage_picking') }}</span>
                            <strong id="ops_pick">0</strong>
                        </div>
                        <div>
                            <span>{{ __('home.stage_packing') }}</span>
                            <strong id="ops_pack">0</strong>
                        </div>
                        <div class="hide">
                            <span>{{ __('home.stage_shipment') }}</span>
                            <strong id="ops_ship">0</strong>
                        </div>
                    </div>
                </section>
                <section class="japs-shot__card japs-shot__line">
                    <div class="japs-ops-card__head">
                        <h3 class="japs-ops-card__title">{{ __('home.revenue_trend') }}</h3>
                        <span class="japs-ops-chip is-up" id="ops_revenue_growth">—</span>
                    </div>
                    <div id="japs_ops_revenue_chart" class="japs-ops-chart"></div>
                </section>
                @endif
            </aside>
        </div>

        @if (auth()->user()->can('dashboard.data'))
        <div class="japs-shot__rest">
            <section class="japs-shot__card">
                <div class="japs-ops-card__head">
                    <h3 class="japs-ops-card__title">{{ __('home.needs_attention') }}</h3>
                </div>
                <div id="japs_ops_attention" class="japs-ops-attention"></div>
            </section>
            <section class="japs-shot__card japs-shot__orders">
                <div class="japs-ops-card__head">
                    <h3 class="japs-ops-card__title">{{ __('home.recent_orders') }}</h3>
                    <a href="{{ $sells_url }}" class="japs-ops-link">{{ __('home.view_all_orders') }} →</a>
                </div>
                <div class="table-responsive">
                    <table class="table ajax_view japs-ops-orders-table" id="recent_sales_table" style="width:100%">
                        <thead>
                            <tr>
                                <th>@lang('sale.invoice_no')</th>
                                <th>@lang('home.source')</th>
                                <th>@lang('contact.customer')</th>
                                <th>@lang('sale.total')</th>
                                <th>@lang('home.fulfillment_stage')</th>
                                <th>@lang('sale.status')</th>
                                <th>@lang('messages.date')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </section>
            <nav class="japs-shot__actions">
                @can('sell.create')
                    <a href="{{ $new_pos_url }}" class="japs-ops-action">
                        <span class="japs-ops-action__ico japs-ops-action__ico--blue"><i class="fas fa-plus"></i></span>
                        <span class="japs-ops-action__txt">{{ __('home.new_pos_order') }}</span>
                    </a>
                @endcan
                @can('sell.create')
                    <a href="{{ $import_url }}" class="japs-ops-action">
                        <span class="japs-ops-action__ico japs-ops-action__ico--green"><i class="fas fa-upload"></i></span>
                        <span class="japs-ops-action__txt">{{ __('home.import_orders') }}</span>
                    </a>
                @endcan
                <a href="{{ $shipments_url }}" class="japs-ops-action">
                    <span class="japs-ops-action__ico japs-ops-action__ico--navy"><i class="fas fa-truck"></i></span>
                    <span class="japs-ops-action__txt">{{ __('home.view_fulfillment') }}</span>
                </a>
                @can('direct_sell.access')
                    <a href="{{ $new_order_url }}" class="japs-ops-action">
                        <span class="japs-ops-action__ico japs-ops-action__ico--violet"><i class="fas fa-file-invoice"></i></span>
                        <span class="japs-ops-action__txt">{{ __('home.create_invoice') }}</span>
                    </a>
                @endcan
            </nav>
            <section class="japs-shot__card japs-shot__stock">
                <div class="japs-ops-card__head">
                    <h3 class="japs-ops-card__title">{{ __('home.low_stock_summary') }}</h3>
                    <a href="{{ action([\App\Http\Controllers\ReportController::class, 'getStockReport']) }}" class="japs-ops-link">{{ __('home.view_details') }}</a>
                </div>
                <div class="table-responsive">
                    <table class="japs-ops-mini-table" id="japs_ops_low_stock">
                        <thead>
                            <tr>
                                <th>@lang('sale.product')</th>
                                <th>SKU</th>
                                <th>{{ __('home.on_hand') }}</th>
                                <th>{{ __('home.reorder_level') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </section>
        </div>
        @endif
    </div>
</div>
</div>
@endsection

<div class="modal fade payment_modal" tabindex="-1" role="dialog"></div>
<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog"></div>

@section('css')
    <link href="{{ asset('css/japs-ui.css') }}" rel="stylesheet">
@endsection

@section('javascript')
    <script src="{{ asset('js/home.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/home-ops.js?v=' . $asset_v . '&shot=7') }}"></script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
    @include('home.partials.attendance_clock_js')
    <script>
        (function () {
            var el = document.querySelector('.japs-ops__type');
            if (!el) return;
            var full = el.getAttribute('data-type') || '';
            el.textContent = '';
            var i = 0;
            function tick() {
                if (i <= full.length) {
                    el.textContent = full.slice(0, i);
                    i += 1;
                    setTimeout(tick, 36);
                }
            }
            tick();
        })();
    </script>
@endsection
