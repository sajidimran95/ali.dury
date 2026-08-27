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
            <h1 class="japs-ops__title">{{ __('home.home_dashboard') }}</h1>
            <p class="japs-ops__greeting">{{ __('home.home_greeting', ['period' => $greet_period, 'name' => $first_name]) }}</p>
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
                <span class="japs-ops-kpi__icon japs-ops-kpi__icon--blue"><i class="fas fa-shopping-bag"></i></span>
                <span class="japs-ops-kpi__label">{{ __('home.total_orders') }}</span>
                <span class="japs-ops-kpi__value total_orders">—</span>
                <span class="japs-ops-kpi__trend"><span class="japs-dash-kpi-card__delta" data-kpi="total_orders">—</span> <em class="japs-ops-kpi__period">{{ __('lang_v1.from_previous_period') }}</em></span>
            </a>
            <a href="{{ $sells_url }}" class="japs-ops-kpi">
                <span class="japs-ops-kpi__icon japs-ops-kpi__icon--green"><i class="fas fa-dollar-sign"></i></span>
                <span class="japs-ops-kpi__label">{{ __('home.total_revenue') }}</span>
                <span class="japs-ops-kpi__value total_sell">—</span>
                <span class="japs-ops-kpi__trend"><span class="japs-dash-kpi-card__delta" data-kpi="total_sell">—</span> <em class="japs-ops-kpi__period">{{ __('lang_v1.from_previous_period') }}</em></span>
            </a>
            <a href="{{ $shipments_url }}" class="japs-ops-kpi">
                <span class="japs-ops-kpi__icon japs-ops-kpi__icon--navy"><i class="fas fa-box"></i></span>
                <span class="japs-ops-kpi__label">{{ __('home.orders_awaiting_fulfillment') }}</span>
                <span class="japs-ops-kpi__value awaiting_fulfillment">—</span>
                <span class="japs-ops-kpi__trend"><span class="japs-dash-kpi-card__delta" data-kpi="awaiting_fulfillment">—</span> <em class="japs-ops-kpi__period">{{ __('lang_v1.from_previous_period') }}</em></span>
            </a>
            <div class="japs-ops-kpi">
                <span class="japs-ops-kpi__icon japs-ops-kpi__icon--red"><i class="fas fa-exclamation"></i></span>
                <span class="japs-ops-kpi__label">{{ __('home.open_issues') }}</span>
                <span class="japs-ops-kpi__value open_issues">—</span>
                <span class="japs-ops-kpi__trend"><span class="japs-dash-kpi-card__delta" data-kpi="open_issues">—</span> <em class="japs-ops-kpi__period">{{ __('lang_v1.from_previous_period') }}</em></span>
            </div>
        </div>

        <div class="japs-ops__row japs-ops__row--2">
            <section class="japs-ops-card">
                <h3 class="japs-ops-card__title">{{ __('home.needs_attention') }}</h3>
                <div id="japs_ops_attention" class="japs-ops-attention"></div>
            </section>
            <section class="japs-ops-card">
                <h3 class="japs-ops-card__title">{{ __('home.fulfillment_overview') }}</h3>
                <div class="japs-ops-flow">
                    <div class="japs-ops-flow__step">
                        <span>{{ __('home.stage_picking') }}</span>
                        <strong id="ops_pick">0</strong>
                    </div>
                    <span class="japs-ops-flow__arrow">→</span>
                    <div class="japs-ops-flow__step">
                        <span>{{ __('home.stage_packing') }}</span>
                        <strong id="ops_pack">0</strong>
                    </div>
                    <span class="japs-ops-flow__arrow">→</span>
                    <div class="japs-ops-flow__step">
                        <span>{{ __('home.stage_shipment') }}</span>
                        <strong id="ops_ship">0</strong>
                    </div>
                </div>
                <table class="japs-ops-mini-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>{{ __('home.stage_picking') }}</th>
                            <th>{{ __('home.stage_packing') }}</th>
                            <th>{{ __('home.stage_shipment') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-src="online"><td>{{ __('home.source_online') }}</td><td class="c-pick">0</td><td class="c-pack">0</td><td class="c-ship">0</td></tr>
                        <tr data-src="field"><td>{{ __('home.source_field') }}</td><td class="c-pick">0</td><td class="c-pack">0</td><td class="c-ship">0</td></tr>
                        <tr data-src="pos"><td>{{ __('home.source_pos') }}</td><td class="c-pick">0</td><td class="c-pack">0</td><td class="c-ship">0</td></tr>
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
                    <table class="table ajax_view" id="recent_sales_table" style="width:100%">
                        <thead>
                            <tr>
                                <th>@lang('sale.invoice_no')</th>
                                <th>@lang('home.source')</th>
                                <th>@lang('contact.customer')</th>
                                <th>@lang('sale.total')</th>
                                <th>@lang('home.fulfillment_stage')</th>
                                <th>@lang('sale.payment_status')</th>
                                <th>@lang('messages.date')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </section>
            <section class="japs-ops-card japs-ops-card--actions">
                <h3 class="japs-ops-card__title">{{ __('home.quick_actions') }}</h3>
                <div class="japs-ops-actions">
                    @can('direct_sell.access')
                        <a href="{{ $new_order_url }}" class="japs-ops-action"><i class="fas fa-plus"></i> {{ __('home.new_order') }}</a>
                    @endcan
                    @can('sell.create')
                        <a href="{{ $import_url }}" class="japs-ops-action"><i class="fas fa-upload"></i> {{ __('home.import_orders') }}</a>
                    @endcan
                    <a href="{{ $shipments_url }}" class="japs-ops-action"><i class="fas fa-truck"></i> {{ __('home.view_fulfillment') }}</a>
                    @can('direct_sell.access')
                        <a href="{{ $new_order_url }}" class="japs-ops-action"><i class="fas fa-file-invoice"></i> {{ __('home.create_invoice') }}</a>
                    @endcan
                </div>
            </section>
        </div>

        <div class="japs-ops__row japs-ops__row--3">
            <section class="japs-ops-card">
                <h3 class="japs-ops-card__title">{{ __('home.order_volume_trend') }}</h3>
                <div id="japs_ops_orders_chart" class="japs-ops-chart"></div>
            </section>
            <section class="japs-ops-card">
                <h3 class="japs-ops-card__title">{{ __('home.revenue_trend') }}</h3>
                <div id="japs_ops_revenue_chart" class="japs-ops-chart"></div>
            </section>
            <section class="japs-ops-card">
                <h3 class="japs-ops-card__title">{{ __('home.low_stock_summary') }}</h3>
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
