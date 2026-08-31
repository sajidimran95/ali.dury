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
<div class="japs-ops">
    <div class="japs-ops__header">
        <div class="japs-ops__title-block">
            <h1 class="japs-ops__title">Good {{ $greet_period }}, {{ $first_name }}</h1>
            <p class="japs-ops__greeting">{{ __('home.home_happening') }}</p>
        </div>
        <div class="japs-ops__toolbar">
            @if (auth()->user()->can('dashboard.data'))
                <button type="button" id="dashboard_date_filter" class="japs-ops__date">
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg>
                    <span id="dashboard_date_filter_label">{{ $week_start->format('M j') }} – {{ $week_end->format('M j, Y') }}</span>
                    <svg aria-hidden="true" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                </button>
                @if (!empty($all_locations) && count($all_locations) > 1)
                    {!! Form::select('dashboard_location', $all_locations, null, [
                        'class' => 'form-control select2 japs-ops__select',
                        'placeholder' => __('lang_v1.all_locations'),
                        'id' => 'dashboard_location',
                    ]) !!}
                @endif
            @endif
        </div>
    </div>

    @include('home.partials.attendance_clock')

    @if (auth()->user()->can('dashboard.data'))
        <div class="japs-ops__kpis">
            <a href="{{ $sells_url }}" class="japs-ops-kpi">
                <span class="japs-ops-kpi__icon japs-ops-kpi__icon--blue" aria-hidden="true"><i class="fas fa-shopping-bag"></i></span>
                <span class="japs-ops-kpi__body">
                    <span class="japs-ops-kpi__label">{{ __('home.total_orders') }}</span>
                    <span class="japs-ops-kpi__value total_orders">—</span>
                    <span class="japs-ops-kpi__trend"><span class="japs-dash-kpi-card__delta" data-kpi="total_orders">—</span> <em class="japs-ops-kpi__period"></em></span>
                </span>
            </a>
            <a href="{{ $sells_url }}" class="japs-ops-kpi">
                <span class="japs-ops-kpi__icon japs-ops-kpi__icon--green" aria-hidden="true"><i class="fas fa-dollar-sign"></i></span>
                <span class="japs-ops-kpi__body">
                    <span class="japs-ops-kpi__label">{{ __('home.total_revenue') }}</span>
                    <span class="japs-ops-kpi__value total_sell">—</span>
                    <span class="japs-ops-kpi__trend"><span class="japs-dash-kpi-card__delta" data-kpi="total_sell">—</span> <em class="japs-ops-kpi__period"></em></span>
                </span>
            </a>
            <a href="{{ $shipments_url }}" class="japs-ops-kpi">
                <span class="japs-ops-kpi__icon japs-ops-kpi__icon--orange" aria-hidden="true"><i class="fas fa-box"></i></span>
                <span class="japs-ops-kpi__body">
                    <span class="japs-ops-kpi__label">{{ __('home.orders_awaiting_fulfillment') }}</span>
                    <span class="japs-ops-kpi__value awaiting_fulfillment">—</span>
                    <span class="japs-ops-kpi__trend"><span class="japs-dash-kpi-card__delta" data-kpi="awaiting_fulfillment">—</span> <em class="japs-ops-kpi__period"></em></span>
                </span>
            </a>
            <div class="japs-ops-kpi">
                <span class="japs-ops-kpi__icon japs-ops-kpi__icon--red" aria-hidden="true"><i class="fas fa-exclamation"></i></span>
                <span class="japs-ops-kpi__body">
                    <span class="japs-ops-kpi__label">{{ __('home.open_issues') }}</span>
                    <span class="japs-ops-kpi__value open_issues">—</span>
                    <span class="japs-ops-kpi__trend"><span class="japs-dash-kpi-card__delta" data-kpi="open_issues">—</span> <em class="japs-ops-kpi__period"></em></span>
                </span>
            </div>
        </div>

        <div class="japs-ops__row japs-ops__row--2">
            <section class="japs-ops-card">
                <div class="japs-ops-card__head">
                    <h3 class="japs-ops-card__title">{{ __('home.needs_attention') }}</h3>
                </div>
                <div id="japs_ops_attention" class="japs-ops-attention"></div>
            </section>
            <section class="japs-ops-card">
                <div class="japs-ops-card__head">
                    <h3 class="japs-ops-card__title">{{ __('home.fulfillment_overview') }}</h3>
                    <a href="{{ $shipments_url }}" class="japs-ops-link">{{ __('home.view_details') }}</a>
                </div>
                <div class="japs-ops-stepper">
                    <div class="japs-ops-stepper__item">
                        <span class="japs-ops-stepper__icon"><i class="fas fa-clipboard-list"></i></span>
                        <span class="japs-ops-stepper__label">{{ __('home.stage_picking') }}</span>
                        <strong class="japs-ops-stepper__count" id="ops_pick">0</strong>
                    </div>
                    <span class="japs-ops-stepper__line" aria-hidden="true"></span>
                    <div class="japs-ops-stepper__item">
                        <span class="japs-ops-stepper__icon"><i class="fas fa-box"></i></span>
                        <span class="japs-ops-stepper__label">{{ __('home.stage_packing') }}</span>
                        <strong class="japs-ops-stepper__count" id="ops_pack">0</strong>
                    </div>
                    <span class="japs-ops-stepper__line" aria-hidden="true"></span>
                    <div class="japs-ops-stepper__item">
                        <span class="japs-ops-stepper__icon"><i class="fas fa-truck"></i></span>
                        <span class="japs-ops-stepper__label">{{ __('home.stage_shipment') }}</span>
                        <strong class="japs-ops-stepper__count" id="ops_ship">0</strong>
                    </div>
                </div>
                <p class="japs-ops-bysource">{{ __('home.by_source') }}</p>
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
        </div>

        <div class="japs-ops__row japs-ops__row--orders">
            <section class="japs-ops-card japs-ops-card--wide">
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
            <section class="japs-ops-card japs-ops-card--actions">
                <h3 class="japs-ops-card__title">{{ __('home.quick_actions') }}</h3>
                <div class="japs-ops-actions">
                    @can('sell.create')
                        <a href="{{ $new_pos_url }}" class="japs-ops-action">
                            <span class="japs-ops-action__ico japs-ops-action__ico--blue"><i class="fas fa-plus"></i></span>
                            <span class="japs-ops-action__txt">{{ __('home.new_pos_order') }}</span>
                            <i class="fas fa-chevron-right japs-ops-action__chev"></i>
                        </a>
                    @endcan
                    @can('sell.create')
                        <a href="{{ $import_url }}" class="japs-ops-action">
                            <span class="japs-ops-action__ico japs-ops-action__ico--green"><i class="fas fa-upload"></i></span>
                            <span class="japs-ops-action__txt">{{ __('home.import_orders') }}</span>
                            <i class="fas fa-chevron-right japs-ops-action__chev"></i>
                        </a>
                    @endcan
                    <a href="{{ $shipments_url }}" class="japs-ops-action">
                        <span class="japs-ops-action__ico japs-ops-action__ico--navy"><i class="fas fa-truck"></i></span>
                        <span class="japs-ops-action__txt">{{ __('home.view_fulfillment') }}</span>
                        <i class="fas fa-chevron-right japs-ops-action__chev"></i>
                    </a>
                    @can('direct_sell.access')
                        <a href="{{ $new_order_url }}" class="japs-ops-action">
                            <span class="japs-ops-action__ico japs-ops-action__ico--violet"><i class="fas fa-file-invoice"></i></span>
                            <span class="japs-ops-action__txt">{{ __('home.create_invoice') }}</span>
                            <i class="fas fa-chevron-right japs-ops-action__chev"></i>
                        </a>
                    @endcan
                </div>
            </section>
        </div>

        <div class="japs-ops__row japs-ops__row--3">
            <section class="japs-ops-card">
                <div class="japs-ops-card__head">
                    <h3 class="japs-ops-card__title">{{ __('home.order_volume_trend') }}</h3>
                    <span class="japs-ops-chip is-up" id="ops_orders_growth">—</span>
                </div>
                <div id="japs_ops_orders_chart" class="japs-ops-chart"></div>
            </section>
            <section class="japs-ops-card">
                <div class="japs-ops-card__head">
                    <h3 class="japs-ops-card__title">{{ __('home.revenue_trend') }}</h3>
                    <span class="japs-ops-chip is-up" id="ops_revenue_growth">—</span>
                </div>
                <div id="japs_ops_revenue_chart" class="japs-ops-chart"></div>
            </section>
            <section class="japs-ops-card">
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
@endsection

<div class="modal fade payment_modal" tabindex="-1" role="dialog"></div>
<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog"></div>

@section('css')
    <link href="{{ asset('css/japs-ui.css') }}" rel="stylesheet">
@endsection

@section('javascript')
    <script src="{{ asset('js/home.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/home-ops.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
    @include('home.partials.attendance_clock_js')
@endsection
