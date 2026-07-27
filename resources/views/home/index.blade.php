@extends('layouts.app')
@section('title', __('home.home'))

@section('content')

    @if (auth()->user()->can('dashboard.data'))
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
                        <a href="{{ $card_sells }}" class="japs-dash-kpi-card" title="{{ __('home.total_sell') }}">
                            <p class="japs-dash-kpi-card__label">{{ __('home.total_sell') }}</p>
                            <p class="japs-dash-kpi-card__value total_sell"></p>
                            <p class="japs-dash-kpi-card__hint">{{ __('home.last_30_days') }}</p>
                        </a>
                        <a href="{{ $card_profit_loss }}" class="japs-dash-kpi-card japs-dash-kpi-card--profit" title="{{ __('home.net_profit') }}">
                            <p class="japs-dash-kpi-card__label">{{ __('home.net_profit') }} @show_tooltip(__('lang_v1.net_home_tooltip'))</p>
                            <p class="japs-dash-kpi-card__value net"></p>
                            <p class="japs-dash-kpi-card__hint">{{ __('home.net_profit_hint') }}</p>
                        </a>
                        <a href="{{ $card_purchases }}" class="japs-dash-kpi-card" title="{{ __('home.total_purchase') }}">
                            <p class="japs-dash-kpi-card__label">{{ __('home.total_purchase') }}</p>
                            <p class="japs-dash-kpi-card__value total_purchase"></p>
                            <p class="japs-dash-kpi-card__hint">{{ __('home.last_30_days') }}</p>
                        </a>
                        <a href="{{ $card_expenses }}" class="japs-dash-kpi-card" title="{{ __('lang_v1.expense') }}">
                            <p class="japs-dash-kpi-card__label">{{ __('lang_v1.expense') }}</p>
                            <p class="japs-dash-kpi-card__value total_expense"></p>
                            <p class="japs-dash-kpi-card__hint">{{ __('home.last_30_days') }}</p>
                        </a>
                    </div>

                    <div class="japs-dash-attention">
                        <h2 class="japs-dash-attention__title">{{ __('home.needs_attention') }}</h2>
                        <div class="japs-dash-attention__pills" id="dashboard_needs_attention">
                            <span class="japs-dash-attention__loading">{{ __('lang_v1.loading') }}...</span>
                        </div>
                    </div>

                    <div class="japs-dash-charts-row">
                        @if ((auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view')) && !empty($all_locations))
                            <div class="japs-dash-chart-card japs-dash-chart-card--sales zoho-dashboard__main-chart">
                                <div class="japs-dash-chart-card__header">
                                    <h3 class="japs-dash-chart-card__title">{{ __('home.sells_last_30_days') }}</h3>
                                </div>
                                <div class="zoho-dashboard__main-chart-canvas japs-dash-chart-card__canvas">
                                    {!! $sells_chart_1->container() !!}
                                </div>
                                <div class="japs-dash-chart-card__footer japs-dash-chart-card__footer--split">
                                    <a href="{{ $card_sell_return }}" class="japs-dash-mini-stat" title="{{ __('lang_v1.total_sell_return') }}">
                                        <span class="japs-dash-mini-stat__label">
                                            {{ __('lang_v1.total_sell_return') }}
                                            <i class="fa fa-info-circle hover-q no-print" aria-hidden="true" data-container="body"
                                            data-toggle="popover" data-placement="auto bottom" id="total_srp"
                                            data-value="{{ __('lang_v1.total_sell_return') }}-{{ __('lang_v1.total_sell_return_paid') }}"
                                            data-content="" data-html="true" data-trigger="hover"></i>
                                        </span>
                                        <span class="japs-dash-mini-stat__value total_sell_return"></span>
                                    </a>
                                    <a href="{{ $card_purchase_return }}" class="japs-dash-mini-stat" title="{{ __('lang_v1.total_purchase_return') }}">
                                        <span class="japs-dash-mini-stat__label">
                                            {{ __('lang_v1.total_purchase_return') }}
                                            <i class="fa fa-info-circle hover-q no-print" aria-hidden="true" data-container="body"
                                            data-toggle="popover" data-placement="auto bottom" id="total_prp"
                                            data-value="{{ __('lang_v1.total_purchase_return') }}-{{ __('lang_v1.total_purchase_return_paid') }}"
                                            data-content="" data-html="true" data-trigger="hover"></i>
                                        </span>
                                        <span class="japs-dash-mini-stat__value total_purchase_return"></span>
                                    </a>
                                </div>
                            </div>
                        @endif
                        <div class="japs-dash-chart-card japs-dash-chart-card--payment">
                            <div class="japs-dash-chart-card__header">
                                <h3 class="japs-dash-chart-card__title">{{ __('home.payment_method_mix') }}</h3>
                                <button type="button" class="japs-dash-chart-card__menu-btn" id="payment_method_mix_menu_btn" aria-label="Chart menu" title="Chart menu" aria-expanded="false" aria-haspopup="true">
                                    <span class="japs-dash-chart-card__menu-icon" aria-hidden="true"></span>
                                </button>
                            </div>
                            <div class="japs-dash-payment-body">
                                <div class="japs-dash-payment-mix">
                                    <ul id="payment_method_mix_legend" class="japs-dash-payment-legend" aria-label="{{ __('home.payment_method_mix') }}"></ul>
                                    <div class="japs-dash-payment-chart-wrap">
                                        <div id="payment_method_mix_chart" class="japs-dash-payment-chart"></div>
                                        <div id="payment_method_mix_center" class="japs-dash-donut-center" aria-hidden="true" style="display:none;">
                                            <div class="japs-dash-donut-center__amount"></div>
                                            <div class="japs-dash-donut-center__label">Total</div>
                                        </div>
                                    </div>
                                </div>
                                <p id="payment_method_mix_empty" class="japs-dash-payment-empty" style="display:none;">{{ __('lang_v1.no_data') }}</p>
                            </div>
                            <div class="japs-dash-chart-card__footer">
                                <a href="{{ $card_online_orders }}" class="japs-dash-mini-stat" title="{{ __('home.online_orders') }}">
                                    <span class="japs-dash-mini-stat__label">
                                        {{ __('home.online_orders') }}
                                        @show_tooltip(__('home.online_orders_tooltip'))
                                    </span>
                                    <span class="japs-dash-mini-stat__value online_orders"></span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if (auth()->user()->can('dashboard.data'))
            </div>{{-- /.zoho-dashboard__main --}}

            <aside class="zoho-dashboard__aside" id="dashboard_side_panel">
                <div class="zoho-side-panel">
                    <div class="zoho-side-panel__tabs">
                        <button type="button" class="zoho-side-panel__tab zoho-side-panel__tab--active"
                            data-panel="pending">@lang('lang_v1.pending_actions')</button>
                        <button type="button" class="zoho-side-panel__tab" data-panel="activities">@lang('lang_v1.recent_activities')</button>
                    </div>

                    <div class="zoho-side-panel__pane zoho-side-panel__pane--active" data-pane="pending">
                        <div class="zoho-side-panel__loading">
                            <span class="zoho-side-panel__spinner"></span>
                        </div>
                    </div>
                    <div class="zoho-side-panel__pane" data-pane="activities">
                        <div class="zoho-side-panel__loading">
                            <span class="zoho-side-panel__spinner"></span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>{{-- /.zoho-dashboard__layout --}}

        <div class="japs-dashboard-content zoho-dashboard__body">
            @php
                $custom_labels = json_decode(session('business.custom_labels'), true) ?: [];
                $is_service_staff_enabled = !empty(session('business.enabled_modules')) && in_array('service_staff', session('business.enabled_modules'));
            @endphp
            <div class="tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-gap-5 lg:tw-grid-cols-2">
                @if (auth()->user()->can('so.view_all') || auth()->user()->can('so.view_own'))
                    <div class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-2xl tw-ring-1 tw-ring-gray-200 zoho-so-summary-card">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="zoho-so-summary-card__header">
                                <h3 class="zoho-so-summary-card__title">{{ __('lang_v1.sales_order_summary') }}</h3>
                                <div class="zoho-so-summary-card__header-actions">
                                    @if (count($all_locations) > 1)
                                        {!! Form::select('so_summary_location', $all_locations, null, [
                                            'class' => 'form-control select2 zoho-so-summary-card__select',
                                            'placeholder' => __('lang_v1.all'),
                                            'id' => 'so_summary_location',
                                        ]) !!}
                                    @endif
                                    <select id="so_summary_period" class="form-control zoho-so-summary-card__select">
                                        <option value="this_month">{{ __('home.this_month') }}</option>
                                        <option value="last_month">{{ __('lang_v1.last_month') }}</option>
                                        <option value="this_week">{{ __('home.this_week') }}</option>
                                        <option value="last_30_days">{{ __('lang_v1.last_30_days') }}</option>
                                        <option value="this_year">{{ __('lang_v1.this_year') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="zoho-so-summary-card__toggles">
                                <button type="button" class="zoho-so-summary-toggle zoho-so-summary-toggle--active" data-metric="quantity">
                                    {{ __('lang_v1.by_quantity') }}
                                </button>
                                <button type="button" class="zoho-so-summary-toggle" data-metric="value">
                                    {{ __('lang_v1.by_value') }}
                                </button>
                            </div>
                            <div class="zoho-so-summary-chart-wrap">
                                <div id="so_summary_chart" class="zoho-so-summary-chart"></div>
                                <p id="so_summary_empty" class="zoho-so-summary-empty" style="display: none;">
                                    {{ __('lang_v1.no_sales_orders_in_period') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if ((auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view')) && !empty($all_locations))
                    <div
                        class="tw-transition-all lg:tw-col-span-2 xl:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-2xl tw-ring-1 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-w-10 tw-h-10">
                                    <svg aria-hidden="true" class="tw-size-5 tw-text-sky-500 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                        <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                        <path d="M17 17h-11v-14h-2"></path>
                                        <path d="M6 5l14 1l-1 7h-13"></path>
                                    </svg>
                                </div>
                                <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                    {{ __('home.sells_current_fy') }}
                                </h3>
                            </div>
                            <div class="tw-mt-5">
                                <div
                                    class="tw-grid tw-w-full tw-h-100 tw-border tw-border-gray-200 tw-border-dashed tw-rounded-xl tw-bg-gray-50 ">
                                    <p class="tw-text-sm tw-italic tw-font-normal tw-text-gray-400">
                                        {!! $sells_chart_2->container() !!}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- @if (!empty($widgets['after_sales_last_30_days']))
                        @foreach ($widgets['after_sales_last_30_days'] as $widget)
                            {!! $widget !!}
                        @endforeach
                    @endif --}}
                @if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view'))
                    <div
                        class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-2xl tw-ring-1 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-w-10 tw-h-10">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M12 9v4"></path>
                                        <path
                                            d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z">
                                        </path>
                                        <path d="M12 16h.01"></path>
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            {{ __('lang_v1.sales_payment_dues') }}
                                            @show_tooltip(__('lang_v1.tooltip_sales_payment_dues'))
                                        </h3>
                                    </div>
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        {!! Form::select('sales_payment_dues_location', $all_locations, null, [
                                            'class' => 'form-control select2',
                                            'placeholder' => __('lang_v1.select_location'),
                                            'id' => 'sales_payment_dues_location',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>


                            <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-bordered table-striped" id="sales_payment_dues_table"
                                            style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>@lang('contact.customer')</th>
                                                    <th>@lang('sale.invoice_no')</th>
                                                    <th>@lang('home.due_amount')</th>
                                                    <th class="not-export">@lang('messages.action')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @can('purchase.view')
                    <div
                        class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-2xl tw-ring-1 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-w-10 tw-h-10">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M12 9v4"></path>
                                        <path
                                            d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z">
                                        </path>
                                        <path d="M12 16h.01"></path>
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            {{ __('lang_v1.purchase_payment_dues') }}
                                            @show_tooltip(__('tooltip.payment_dues'))
                                        </h3>
                                    </div>
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        @if (count($all_locations) > 1)
                                            {!! Form::select('purchase_payment_dues_location', $all_locations, null, [
                                                'class' => 'form-control select2 ',
                                                'placeholder' => __('lang_v1.select_location'),
                                                'id' => 'purchase_payment_dues_location',
                                            ]) !!}
                                        @endif
                                    </div>
                                </div>

                            </div>
                            <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-bordered table-striped" id="purchase_payment_dues_table"
                                            style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>@lang('purchase.supplier')</th>
                                                    <th>@lang('purchase.ref_no')</th>
                                                    <th>@lang('home.due_amount')</th>
                                                    <th class="not-export">@lang('messages.action')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan
                @can('stock_report.view')
                    <div
                        class="tw-transition-all {{ session('business.enable_product_expiry') == 1 ? 'lg:tw-col-span-1' : 'lg:tw-col-span-2' }} tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-2xl tw-ring-1 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-w-10 tw-h-10">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path>
                                        <path d="M12 8v4"></path>
                                        <path d="M12 16h.01"></path>
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            {{ __('home.product_stock_alert') }}
                                            @show_tooltip(__('tooltip.product_stock_alert'))
                                        </h3>
                                    </div>
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        @if (count($all_locations) > 1)
                                            {!! Form::select('stock_alert_location', $all_locations, null, [
                                                'class' => 'form-control select2',
                                                'placeholder' => __('lang_v1.select_location'),
                                                'id' => 'stock_alert_location',
                                            ]) !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-bordered table-striped" id="stock_alert_table"
                                            style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>@lang('sale.product')</th>
                                                    <th>@lang('business.location')</th>
                                                    <th>@lang('report.current_stock')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if (session('business.enable_product_expiry') == 1)
                        <div
                            class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-2xl tw-ring-1 tw-ring-gray-200">
                            <div class="tw-p-4 sm:tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-2.5">
                                    <div
                                        class="tw-border tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-w-10 tw-h-10">
                                        <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path d="M12 9v4"></path>
                                            <path
                                                d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z">
                                            </path>
                                            <path d="M12 16h.01"></path>
                                        </svg>
                                    </div>
                                    <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                        <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                            <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                                {{ __('home.stock_expiry_alert') }}
                                                @show_tooltip(
                                                __('tooltip.stock_expiry_alert', [
                                                'days'
                                                =>session('business.stock_expiry_alert_days', 30) ]) )
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                    <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                        <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                            <input type="hidden" id="stock_expiry_alert_days"
                                                value="{{ \Carbon::now()->addDays(carbon_numeric(session('business.stock_expiry_alert_days', 30)))->format('Y-m-d') }}">
                                            <table class="table table-bordered table-striped" id="stock_expiry_alert_table">
                                                <thead>
                                                    <tr>
                                                        <th>@lang('business.product')</th>
                                                        <th>@lang('business.location')</th>
                                                        <th>@lang('report.stock_left')</th>
                                                        <th>@lang('product.expires_in')</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endcan
                @if (auth()->user()->can('so.view_all') || auth()->user()->can('so.view_own'))
                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-2xl tw-ring-1 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-w-10 tw-h-10">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path>
                                        <path d="M12 8v4"></path>
                                        <path d="M12 16h.01"></path>
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            {{ __('lang_v1.sales_order') }}
                                        </h3>
                                    </div>
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        @if (count($all_locations) > 1)
                                            {!! Form::select('so_location', $all_locations, null, [
                                                'class' => 'form-control select2',
                                                'placeholder' => __('lang_v1.select_location'),
                                                'id' => 'so_location',
                                            ]) !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="tw-mt-5 japs-dash-full-table">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped ajax_view" id="sales_order_table" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th class="not-export">@lang('messages.action')</th>
                                                <th>@lang('messages.date')</th>
                                                <th>@lang('restaurant.order_no')</th>
                                                <th>@lang('sale.customer_name')</th>
                                                <th>@lang('lang_v1.contact_no')</th>
                                                <th>@lang('sale.location')</th>
                                                <th>@lang('sale.status')</th>
                                                <th>@lang('lang_v1.shipping_status')</th>
                                                <th>@lang('lang_v1.quantity_remaining')</th>
                                                <th>@lang('lang_v1.added_by')</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @if (
                    !empty($common_settings['enable_purchase_requisition']) &&
                        (auth()->user()->can('purchase_requisition.view_all') || auth()->user()->can('purchase_requisition.view_own')))
                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-2xl tw-ring-1 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-w-10 tw-h-10">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M10 10v4a1 1 0 0 0 1 1h2a1 1 0 0 0 1 -1v-4"></path>
                                        <path d="M9 6h6"></path>
                                        <path d="M10 6v-2a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v2"></path>
                                        <circle cx="12" cy="16" r="2"></circle>
                                        <path d="M5 20h14a2 2 0 0 0 2 -2v-10"></path>
                                        <path d="M15 16v4"></path>
                                        <path d="M9 20v-4"></path>
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            @lang('lang_v1.purchase_requisition')
                                        </h3>
                                    </div>
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        @if (count($all_locations) > 1)
                                            @if (count($all_locations) > 1)
                                                {!! Form::select('pr_location', $all_locations, null, [
                                                    'class' => 'form-control select2',
                                                    'placeholder' => __('lang_v1.select_location'),
                                                    'id' => 'pr_location',
                                                ]) !!}
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-bordered table-striped ajax_view"
                                            id="purchase_requisition_table" style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th class="not-export">@lang('messages.action')</th>
                                                    <th>@lang('messages.date')</th>
                                                    <th>@lang('purchase.ref_no')</th>
                                                    <th>@lang('purchase.location')</th>
                                                    <th>@lang('sale.status')</th>
                                                    <th>@lang('lang_v1.required_by_date')</th>
                                                    <th>@lang('lang_v1.added_by')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if (
                    !empty($common_settings['enable_purchase_order']) &&
                        (auth()->user()->can('purchase_order.view_all') || auth()->user()->can('purchase_order.view_own')))

                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-2xl tw-ring-1 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-w-10 tw-h-10">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <rect x="4" y="4" width="16" height="16" rx="2" />
                                        <line x1="4" y1="10" x2="20" y2="10" />
                                        <line x1="12" y1="4" x2="12" y2="20" />
                                        <line x1="12" y1="10" x2="16" y2="10" />
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            @lang('lang_v1.purchase_order')
                                        </h3>
                                    </div>
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        @if (count($all_locations) > 1)
                                            {!! Form::select('po_location', $all_locations, null, [
                                                'class' => 'form-control select2',
                                                'placeholder' => __('lang_v1.select_location'),
                                                'id' => 'po_location',
                                            ]) !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-bordered table-striped ajax_view"
                                            id="purchase_order_table" style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th class="not-export">@lang('messages.action')</th>
                                                    <th>@lang('messages.date')</th>
                                                    <th>@lang('purchase.ref_no')</th>
                                                    <th>@lang('purchase.location')</th>
                                                    <th>@lang('purchase.supplier')</th>
                                                    <th>@lang('sale.status')</th>
                                                    <th>@lang('lang_v1.quantity_remaining')</th>
                                                    <th>@lang('lang_v1.added_by')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                @endif
                @if (auth()->user()->can('access_pending_shipments_only') ||
                        auth()->user()->can('access_shipping') ||
                        auth()->user()->can('access_own_shipping'))
                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-2xl tw-ring-1 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-w-10 tw-h-10">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                        <path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                        <path d="M5 17h-2v-4m-1 -8h11v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5"></path>
                                        <path d="M3 9l4 0"></path>
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            @lang('lang_v1.pending_shipments')
                                        </h3>
                                    </div>
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        @if (count($all_locations) > 1)
                                            {!! Form::select('pending_shipments_location', $all_locations, null, [
                                                'class' => 'form-control select2 ',
                                                'placeholder' => __('lang_v1.select_location'),
                                                'id' => 'pending_shipments_location',
                                            ]) !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="tw-mt-5 japs-dash-full-table">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped ajax_view" id="shipments_table" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th class="not-export">@lang('messages.action')</th>
                                                <th>@lang('messages.date')</th>
                                                <th>@lang('sale.invoice_no')</th>
                                                <th>@lang('sale.customer_name')</th>
                                                <th>@lang('lang_v1.contact_no')</th>
                                                <th>@lang('sale.location')</th>
                                                <th>@lang('lang_v1.delivery_person')</th>
                                                <th>@lang('lang_v1.shipping_status')</th>
                                                @if (!empty($custom_labels['shipping']['custom_field_1']))
                                                    <th>{{ $custom_labels['shipping']['custom_field_1'] }}</th>
                                                @endif
                                                @if (!empty($custom_labels['shipping']['custom_field_2']))
                                                    <th>{{ $custom_labels['shipping']['custom_field_2'] }}</th>
                                                @endif
                                                @if (!empty($custom_labels['shipping']['custom_field_3']))
                                                    <th>{{ $custom_labels['shipping']['custom_field_3'] }}</th>
                                                @endif
                                                @if (!empty($custom_labels['shipping']['custom_field_4']))
                                                    <th>{{ $custom_labels['shipping']['custom_field_4'] }}</th>
                                                @endif
                                                @if (!empty($custom_labels['shipping']['custom_field_5']))
                                                    <th>{{ $custom_labels['shipping']['custom_field_5'] }}</th>
                                                @endif
                                                <th>@lang('sale.payment_status')</th>
                                                <th>@lang('restaurant.service_staff')</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @if (auth()->user()->can('account.access') && config('constants.show_payments_recovered_today') == true)
                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-2xl tw-ring-1 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-w-10 tw-h-10">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M12 9v4"></path>
                                        <path
                                            d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z">
                                        </path>
                                        <path d="M12 16h.01"></path>
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            @lang('lang_v1.payment_recovered_today')
                                        </h3>
                                    </div>

                                </div>
                            </div>
                            <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-bordered table-striped" id="cash_flow_table">
                                            <thead>
                                                <tr>
                                                    <th>@lang('messages.date')</th>
                                                    <th>@lang('account.account')</th>
                                                    <th>@lang('lang_v1.description')</th>
                                                    <th>@lang('lang_v1.payment_method')</th>
                                                    <th>@lang('lang_v1.payment_details')</th>
                                                    <th>@lang('account.credit')</th>
                                                    <th>@lang('lang_v1.account_balance')
                                                        @show_tooltip(__('lang_v1.account_balance_tooltip'))</th>
                                                    <th>@lang('lang_v1.total_balance')
                                                        @show_tooltip(__('lang_v1.total_balance_tooltip'))</th>
                                                </tr>
                                            </thead>
                                            <tfoot>
                                                <tr class="bg-gray font-17 footer-total text-center">
                                                    <td colspan="5"><strong>@lang('sale.total'):</strong></td>
                                                    <td class="footer_total_credit"></td>
                                                    <td colspan="2"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                {{-- @if (!empty($widgets['after_dashboard_reports']))
                    @foreach ($widgets['after_dashboard_reports'] as $widget)
                        {!! $widget !!}
                    @endforeach
                @endif --}}
            </div>
        </div>{{-- /.zoho-dashboard__body --}}
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
    @includeIf('sales_order.common_js')
    @includeIf('purchase_order.common_js')
    @if (!empty($all_locations))
        {!! $sells_chart_1->script() !!}
        {!! $sells_chart_2->script() !!}
        <script type="text/javascript">
            $(function () {
                if (!$('body').hasClass('japs-dashboard-home') || typeof Highcharts === 'undefined') {
                    return;
                }
                setTimeout(function () {
                    if (!$('body').hasClass('japs-dashboard-home') || typeof Highcharts === 'undefined') {
                        return;
                    }
                    var columnStyle = {
                        chart: {
                            backgroundColor: 'transparent',
                            type: 'column'
                        },
                        colors: ['#3b82f6'],
                        legend: { enabled: false },
                        xAxis: {
                            lineColor: '#e5e7eb',
                            tickColor: '#e5e7eb',
                            labels: {
                                rotation: -45,
                                style: { color: '#6b7280', fontSize: '10px' }
                            }
                        },
                        yAxis: {
                            gridLineColor: '#e5e7eb',
                            gridLineWidth: 1,
                            gridLineDashStyle: 'Solid',
                            title: { text: null },
                            min: 0,
                            labels: { style: { color: '#6b7280', fontSize: '11px' } }
                        },
                        plotOptions: {
                            column: {
                                borderWidth: 0,
                                borderRadius: 3,
                                color: '#3b82f6',
                                pointPadding: 0.15,
                                groupPadding: 0.1
                            },
                            series: {
                                type: 'column',
                                borderWidth: 0
                            }
                        }
                    };
                    (Highcharts.charts || []).forEach(function (chart) {
                        if (!chart || !chart.renderTo) return;
                        // Skip dynamic charts that already set their own column style
                        var id = chart.renderTo.id || '';
                        if (id === 'payment_method_mix_chart' || id === 'so_summary_chart') return;

                        chart.update(columnStyle, false);
                        if (chart.series && chart.series.length) {
                            chart.series.forEach(function (s) {
                                try { s.update({ type: 'column', color: '#3b82f6', borderWidth: 0 }, false); } catch (e) {}
                            });
                        }
                        chart.redraw();
                    });
                }, 400);
            });
        </script>
    @endif
    @if (auth()->user()->can('so.view_all') || auth()->user()->can('so.view_own'))
    <script type="text/javascript">
        (function() {
            var soSummaryChart = null;
            var soSummaryMetric = 'quantity';

            function loadSoSummaryChart() {
                if (!$('#so_summary_chart').length) {
                    return;
                }

                $.get('{{ action([\App\Http\Controllers\HomeController::class, 'getSalesOrderSummary']) }}', {
                    period: $('#so_summary_period').val(),
                    metric: soSummaryMetric,
                    location_id: $('#so_summary_location').length ? $('#so_summary_location').val() : ''
                }, function(data) {
                    renderSoSummaryChart(data);
                });
            }

            function renderSoSummaryChart(data) {
                if (typeof Highcharts === 'undefined') {
                    return;
                }

                if (soSummaryChart) {
                    soSummaryChart.destroy();
                    soSummaryChart = null;
                }

                var isValue = data.metric === 'value';
                var maxVal = Math.max.apply(null, data.values.concat([0]));
                var yMax = maxVal > 0 ? null : (isValue ? 5000 : 5);

                $('#so_summary_empty').toggle(!data.has_data);
                $('#so_summary_chart').show();

                soSummaryChart = Highcharts.chart('so_summary_chart', {
                    chart: {
                        type: 'column',
                        height: 300,
                        backgroundColor: 'transparent',
                        spacing: [8, 8, 8, 0]
                    },
                    title: { text: null },
                    credits: { enabled: false },
                    legend: { enabled: false },
                    xAxis: {
                        categories: data.labels,
                        tickInterval: data.label_step,
                        lineColor: '#e5e7eb',
                        tickColor: '#e5e7eb',
                        labels: {
                            rotation: -45,
                            style: { color: '#6b7280', fontSize: '10px', fontWeight: '500' }
                        }
                    },
                    yAxis: {
                        title: { text: null },
                        max: yMax,
                        gridLineColor: '#e5e7eb',
                        gridLineWidth: 1,
                        gridLineDashStyle: 'Solid',
                        labels: {
                            style: { color: '#6b7280', fontSize: '11px', fontWeight: '500' },
                            formatter: function() {
                                var v = this.value;
                                if (v >= 1000) {
                                    return (v / 1000) + ' K';
                                }
                                return v;
                            }
                        },
                        min: 0
                    },
                    tooltip: {
                        shared: false,
                        backgroundColor: '#0f172a',
                        borderColor: '#0f172a',
                        borderRadius: 10,
                        style: { color: '#f8fafc', fontSize: '12px' },
                        formatter: function() {
                            if (isValue) {
                                return '<span style="color:#f8fafc"><b>' + this.x + '</b><br/>' + data.currency_symbol + ' ' + Highcharts.numberFormat(this.y, 2) + '</span>';
                            }
                            return '<span style="color:#f8fafc"><b>' + this.x + '</b><br/>' + Highcharts.numberFormat(this.y, 2) + '</span>';
                        }
                    },
                    plotOptions: {
                        column: {
                            borderWidth: 0,
                            borderRadius: 3,
                            color: '#3b82f6',
                            pointPadding: 0.15,
                            groupPadding: 0.1,
                            enableMouseTracking: data.has_data
                        },
                        series: {
                            animation: { duration: 400 }
                        }
                    },
                    series: [{
                        name: isValue ? '{{ __('lang_v1.by_value') }}' : '{{ __('lang_v1.by_quantity') }}',
                        type: 'column',
                        data: data.has_data ? data.values : data.values.map(function() { return null; }),
                        showInLegend: false
                    }]
                });
            }

            $(document).ready(function() {
                if (!$('#so_summary_chart').length) {
                    return;
                }

                loadSoSummaryChart();

                $(document).on('click', '.zoho-so-summary-toggle', function() {
                    $('.zoho-so-summary-toggle').removeClass('zoho-so-summary-toggle--active');
                    $(this).addClass('zoho-so-summary-toggle--active');
                    soSummaryMetric = $(this).data('metric');
                    loadSoSummaryChart();
                });

                $('#so_summary_period, #so_summary_location').on('change', loadSoSummaryChart);
            });
        })();
    </script>
    @endif
    <script type="text/javascript">
        $(document).ready(function() {
            sales_order_table = $('#sales_order_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader: false,
                autoWidth: false,
                scrollX: true,
                scrollCollapse: true,
                aaSorting: [
                    [1, 'desc']
                ],
                "ajax": {
                    "url": '{{ action([\App\Http\Controllers\SellController::class, 'index']) }}?sale_type=sales_order',
                    "data": function(d) {
                        d.for_dashboard_sales_order = true;

                        if ($('#so_location').length > 0) {
                            d.location_id = $('#so_location').val();
                        }
                    }
                },
                columnDefs: [{
                    "targets": 7,
                    "orderable": false,
                    "searchable": false
                }],
                columns: [{
                        data: 'action',
                        name: 'action'
                    },
                    {
                        data: 'transaction_date',
                        name: 'transaction_date'
                    },
                    {
                        data: 'invoice_no',
                        name: 'invoice_no'
                    },
                    {
                        data: 'conatct_name',
                        name: 'conatct_name'
                    },
                    {
                        data: 'mobile',
                        name: 'contacts.mobile'
                    },
                    {
                        data: 'business_location',
                        name: 'bl.name'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'shipping_status',
                        name: 'shipping_status'
                    },
                    {
                        data: 'so_qty_remaining',
                        name: 'so_qty_remaining',
                        "searchable": false
                    },
                    {
                        data: 'added_by',
                        name: 'u.first_name'
                    },
                ]
            });

            @if (auth()->user()->can('account.access') && config('constants.show_payments_recovered_today') == true)

                // Cash Flow Table
                cash_flow_table = $('#cash_flow_table').DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader:false,
                    "ajax": {
                        "url": "{{ action([\App\Http\Controllers\AccountController::class, 'cashFlow']) }}",
                        "data": function(d) {
                            d.type = 'credit';
                            d.only_payment_recovered = true;
                        }
                    },
                    "ordering": false,
                    "searching": false,
                    columns: [{
                            data: 'operation_date',
                            name: 'operation_date'
                        },
                        {
                            data: 'account_name',
                            name: 'account_name'
                        },
                        {
                            data: 'sub_type',
                            name: 'sub_type'
                        },
                        {
                            data: 'method',
                            name: 'TP.method'
                        },
                        {
                            data: 'payment_details',
                            name: 'payment_details',
                            searchable: false
                        },
                        {
                            data: 'credit',
                            name: 'amount'
                        },
                        {
                            data: 'balance',
                            name: 'balance'
                        },
                        {
                            data: 'total_balance',
                            name: 'total_balance'
                        },
                    ],
                    "fnDrawCallback": function(oSettings) {
                        __currency_convert_recursively($('#cash_flow_table'));
                    },
                    "footerCallback": function(row, data, start, end, display) {
                        var footer_total_credit = 0;

                        for (var r in data) {
                            footer_total_credit += $(data[r].credit).data('orig-value') ? parseFloat($(
                                data[r].credit).data('orig-value')) : 0;
                        }
                        $('.footer_total_credit').html(__currency_trans_from_en(footer_total_credit));
                    }
                });
            @endif

            $('#so_location').change(function() {
                sales_order_table.ajax.reload();
            });
            @if (!empty($common_settings['enable_purchase_order']))
                //Purchase table
                purchase_order_table = $('#purchase_order_table').DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader:false,
                    aaSorting: [
                        [1, 'desc']
                    ],
                    scrollY: "75vh",
                    scrollX: true,
                    scrollCollapse: true,
                    ajax: {
                        url: '{{ action([\App\Http\Controllers\PurchaseOrderController::class, 'index']) }}',
                        data: function(d) {
                            d.from_dashboard = true;

                            if ($('#po_location').length > 0) {
                                d.location_id = $('#po_location').val();
                            }
                        },
                    },
                    columns: [{
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'transaction_date',
                            name: 'transaction_date'
                        },
                        {
                            data: 'ref_no',
                            name: 'ref_no'
                        },
                        {
                            data: 'location_name',
                            name: 'BS.name'
                        },
                        {
                            data: 'name',
                            name: 'contacts.name'
                        },
                        {
                            data: 'status',
                            name: 'transactions.status'
                        },
                        {
                            data: 'po_qty_remaining',
                            name: 'po_qty_remaining',
                            "searchable": false
                        },
                        {
                            data: 'added_by',
                            name: 'u.first_name'
                        }
                    ]
                })

                $('#po_location').change(function() {
                    purchase_order_table.ajax.reload();
                });
            @endif

            @if (!empty($common_settings['enable_purchase_requisition']))
                //Purchase table
                purchase_requisition_table = $('#purchase_requisition_table').DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader:false,
                    aaSorting: [
                        [1, 'desc']
                    ],
                    scrollY: "75vh",
                    scrollX: true,
                    scrollCollapse: true,
                    ajax: {
                        url: '{{ action([\App\Http\Controllers\PurchaseRequisitionController::class, 'index']) }}',
                        data: function(d) {
                            d.from_dashboard = true;

                            if ($('#pr_location').length > 0) {
                                d.location_id = $('#pr_location').val();
                            }
                        },
                    },
                    columns: [{
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'transaction_date',
                            name: 'transaction_date'
                        },
                        {
                            data: 'ref_no',
                            name: 'ref_no'
                        },
                        {
                            data: 'location_name',
                            name: 'BS.name'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'delivery_date',
                            name: 'delivery_date'
                        },
                        {
                            data: 'added_by',
                            name: 'u.first_name'
                        },
                    ]
                })

                $('#pr_location').change(function() {
                    purchase_requisition_table.ajax.reload();
                });

                $(document).on('click', 'a.delete-purchase-requisition', function(e) {
                    e.preventDefault();
                    swal({
                        title: LANG.sure,
                        icon: 'warning',
                        buttons: true,
                        dangerMode: true,
                    }).then(willDelete => {
                        if (willDelete) {
                            var href = $(this).attr('href');
                            $.ajax({
                                method: 'DELETE',
                                url: href,
                                dataType: 'json',
                                success: function(result) {
                                    if (result.success == true) {
                                        toastr.success(result.msg);
                                        purchase_requisition_table.ajax.reload();
                                    } else {
                                        toastr.error(result.msg);
                                    }
                                },
                            });
                        }
                    });
                });
            @endif

            sell_table = $('#shipments_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader: false,
                autoWidth: false,
                scrollX: true,
                scrollCollapse: true,
                aaSorting: [
                    [1, 'desc']
                ],
                "ajax": {
                    "url": '{{ action([\App\Http\Controllers\SellController::class, 'index']) }}',
                    "data": function(d) {
                        d.only_pending_shipments = true;
                        if ($('#pending_shipments_location').length > 0) {
                            d.location_id = $('#pending_shipments_location').val();
                        }
                    }
                },
                columns: [{
                        data: 'action',
                        name: 'action',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'transaction_date',
                        name: 'transaction_date'
                    },
                    {
                        data: 'invoice_no',
                        name: 'invoice_no'
                    },
                    {
                        data: 'conatct_name',
                        name: 'conatct_name'
                    },
                    {
                        data: 'mobile',
                        name: 'contacts.mobile'
                    },
                    {
                        data: 'business_location',
                        name: 'bl.name'
                    },
                    {
                        data: 'delivery_person',
                        name: 'delivery_person'
                    },
                    {
                        data: 'shipping_status',
                        name: 'shipping_status'
                    },
                    @if (!empty($custom_labels['shipping']['custom_field_1']))
                        {
                            data: 'shipping_custom_field_1',
                            name: 'shipping_custom_field_1'
                        },
                    @endif
                    @if (!empty($custom_labels['shipping']['custom_field_2']))
                        {
                            data: 'shipping_custom_field_2',
                            name: 'shipping_custom_field_2'
                        },
                    @endif
                    @if (!empty($custom_labels['shipping']['custom_field_3']))
                        {
                            data: 'shipping_custom_field_3',
                            name: 'shipping_custom_field_3'
                        },
                    @endif
                    @if (!empty($custom_labels['shipping']['custom_field_4']))
                        {
                            data: 'shipping_custom_field_4',
                            name: 'shipping_custom_field_4'
                        },
                    @endif
                    @if (!empty($custom_labels['shipping']['custom_field_5']))
                        {
                            data: 'shipping_custom_field_5',
                            name: 'shipping_custom_field_5'
                        },
                    @endif
                    {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'waiter',
                        name: 'ss.first_name',
                        @if (empty($is_service_staff_enabled))
                            visible: false
                        @endif
                    }
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#shipments_table'));
                },
                createdRow: function(row, data, dataIndex) {
                    $(row).find('td:eq(4)').attr('class', 'clickable_td');
                }
            });

            $('#pending_shipments_location').change(function() {
                sell_table.ajax.reload();
            });
            $(window).on('resize', function() {
                if ($.fn.DataTable.isDataTable('#sales_order_table')) {
                    $('#sales_order_table').DataTable().columns.adjust();
                }
                if ($.fn.DataTable.isDataTable('#shipments_table')) {
                    $('#shipments_table').DataTable().columns.adjust();
                }
            });
        });
    </script>

    @if (auth()->user()->can('dashboard.data'))
    <script type="text/javascript">
        $(function () {
            var $panel = $('#dashboard_side_panel');
            if (!$panel.length) {
                return;
            }

            var spinner = '<div class="zoho-side-panel__loading"><span class="zoho-side-panel__spinner"></span></div>';

            function loadSidePanel() {
                var locationId = $('#dashboard_location').length ? $('#dashboard_location').val() : '';

                $panel.find('.zoho-side-panel__pane[data-pane="pending"]').html(spinner);
                $panel.find('.zoho-side-panel__pane[data-pane="activities"]').html(spinner);

                $.ajax({
                    url: '{{ action([\App\Http\Controllers\HomeController::class, 'getDashboardSidePanel']) }}',
                    method: 'GET',
                    cache: false,
                    data: { location_id: locationId },
                    dataType: 'json',
                    success: function (resp) {
                        $panel.find('.zoho-side-panel__pane[data-pane="pending"]').html(resp.pending_html);
                        $panel.find('.zoho-side-panel__pane[data-pane="activities"]').html(resp.activities_html);
                        renderNeedsAttention(resp.attention_items || []);
                    },
                    error: function () {
                        $panel.find('.zoho-side-panel__pane').html('<div class="zoho-side-panel__empty"><p>{{ __('messages.something_went_wrong') }}</p></div>');
                        $('#dashboard_needs_attention').html('<span class="japs-dash-attention__empty">{{ __('messages.something_went_wrong') }}</span>');
                    }
                });
            }

            function renderNeedsAttention(items) {
                var $wrap = $('#dashboard_needs_attention');
                if (!$wrap.length) {
                    return;
                }
                if (!items.length) {
                    $wrap.html('<span class="japs-dash-attention__empty">{{ __('lang_v1.no_pending_actions') }}</span>');
                    return;
                }

                // Prefer screenshot order: Invoice due, To be shipped, Purchase due, Below alert quantity
                var preferred = [
                    @json(__('home.invoice_due')),
                    @json(__('lang_v1.to_be_shipped')),
                    @json(__('home.purchase_due')),
                    @json(__('lang_v1.below_alert_quantity'))
                ];
                var sorted = items.slice().sort(function (a, b) {
                    var ai = preferred.indexOf(a.label);
                    var bi = preferred.indexOf(b.label);
                    if (ai === -1) ai = 999;
                    if (bi === -1) bi = 999;
                    return ai - bi;
                }).slice(0, 4);

                var html = '';
                sorted.forEach(function (item) {
                    var active = item.count > 0 ? ' is-active' : '';
                    html += '<a href="' + item.url + '" class="japs-dash-attention-pill' + active + '">';
                    html += '<span class="japs-dash-attention-pill__label">' + item.label + '</span>';
                    html += '<span class="japs-dash-attention-pill__count">' + item.count + '</span>';
                    html += '</a>';
                });
                $wrap.html(html);
            }

            $panel.on('click', '.zoho-side-panel__tab', function () {
                var target = $(this).data('panel');
                $panel.find('.zoho-side-panel__tab').removeClass('zoho-side-panel__tab--active');
                $(this).addClass('zoho-side-panel__tab--active');
                $panel.find('.zoho-side-panel__pane').removeClass('zoho-side-panel__pane--active');
                $panel.find('.zoho-side-panel__pane[data-pane="' + target + '"]').addClass('zoho-side-panel__pane--active');
            });

            $('#dashboard_location').on('change', loadSidePanel);

            loadSidePanel();
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
                    // Prefer absolute clock_in_at so the timer keeps ticking even if tab slept
                    if (clockInAt && !isNaN(clockInAt.getTime())) {
                        worked = Math.max(0, Math.floor((now - clockInAt.getTime()) / 1000) - baseBreak);
                    } else {
                        worked = baseWorked + elapsed;
                    }
                } else if (attState === 'on_break') {
                    if (breakStartAt && !isNaN(breakStartAt.getTime())) {
                        // baseBreak already includes open break at load; advance from load time
                        brk = baseBreak + elapsed;
                    } else {
                        brk = baseBreak + elapsed;
                    }
                    worked = baseWorked; // work time paused on break
                }

                var workedLabel = formatSecs(worked);
                var breakLabel = formatSecs(brk);
                $('#att_worked_timer').text(workedLabel);
                $('#att_break_timer').text(breakLabel);

                // Toolbar live badge: show break timer while on break, else worked time
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
