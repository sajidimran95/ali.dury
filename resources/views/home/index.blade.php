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
                                        <button type="button" class="japs-dash-period-switch__btn is-active" data-period="30d">{{ __('lang_v1.last_30_days') }}</button>
                                        <button type="button" class="japs-dash-period-switch__btn" data-period="fy">{{ __('lang_v1.this_year') }}</button>
                                    </div>
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
                        @endif
                        <div class="japs-dash-chart-card japs-dash-chart-card--payment japs-dash-chart-card--overview">
                            <div class="japs-dash-chart-card__header">
                                <h3 class="japs-dash-chart-card__title">{{ __('home.sales_overview') }}</h3>
                            </div>
                            <div class="japs-dash-payment-body">
                                <div class="japs-dash-payment-mix">
                                    <div class="japs-dash-payment-chart-wrap">
                                        <div id="payment_method_mix_chart" class="japs-dash-payment-chart"></div>
                                        <div id="payment_method_mix_center" class="japs-dash-donut-center" aria-hidden="true" style="display:none;">
                                            <div class="japs-dash-donut-center__amount"></div>
                                            <div class="japs-dash-donut-center__label" data-default-label="{{ __('home.total_sell') }}">{{ __('home.total_sell') }}</div>
                                        </div>
                                    </div>
                                    <ul id="payment_method_mix_legend" class="japs-dash-payment-legend" aria-label="{{ __('home.payment_method_mix') }}"></ul>
                                </div>
                                <p id="payment_method_mix_empty" class="japs-dash-payment-empty" style="display:none;">{{ __('lang_v1.no_data') }}</p>
                            </div>
                            <div class="japs-dash-overview-footer" id="payment_overview_footer" style="display:none;">
                                <div class="japs-dash-overview-footer__row">
                                    <span class="japs-dash-overview-footer__label">{{ __('home.total_sell') }}</span>
                                    <span class="japs-dash-overview-footer__value japs-overview-sales-total">—</span>
                                </div>
                                <div class="japs-dash-overview-progress">
                                    <div class="japs-dash-overview-progress__bar" id="payment_overview_progress" style="width:0%"></div>
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
                    var columnStyle = {
                        chart: { backgroundColor: 'transparent', type: 'column' },
                        legend: { enabled: false },
                        xAxis: {
                            lineColor: '#e8ecf1',
                            tickColor: '#e8ecf1',
                            labels: { rotation: -45, style: { color: '#94a3b8', fontSize: '11px', fontWeight: '500' } }
                        },
                        yAxis: {
                            gridLineColor: '#eef2f7',
                            gridLineWidth: 1,
                            gridLineDashStyle: 'Solid',
                            title: { text: null },
                            min: 0,
                            labels: { style: { color: '#94a3b8', fontSize: '11px' } }
                        },
                        plotOptions: {
                            column: { borderWidth: 0, borderRadius: 6, pointPadding: 0.12, groupPadding: 0.08 },
                            series: { type: 'column', borderWidth: 0 }
                        }
                    };
                    (Highcharts.charts || []).forEach(function (chart) {
                        if (!chart || !chart.renderTo) return;
                        var id = chart.renderTo.id || '';
                        if (id === 'payment_method_mix_chart') return;
                        chart.update(columnStyle, false);
                        if (chart.series && chart.series.length) {
                            chart.series.forEach(function (s) {
                                var maxVal = -1, maxIdx = -1;
                                (s.data || []).forEach(function (point, idx) {
                                    var val = typeof point.y === 'number' ? point.y : 0;
                                    if (val > maxVal) { maxVal = val; maxIdx = idx; }
                                });
                                (s.data || []).forEach(function (point, idx) {
                                    var isPeak = idx === maxIdx && maxVal > 0;
                                    point.update({
                                        color: isPeak ? { linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 }, stops: [[0, '#3b82f6'], [1, '#93c5fd']] } : '#e8edf5',
                                        borderRadius: isPeak ? 10 : 6
                                    }, false);
                                });
                            });
                        }
                        chart.redraw();
                    });

                    $('.japs-dash-period-switch__btn').on('click', function () {
                        var period = $(this).data('period');
                        $('.japs-dash-period-switch__btn').removeClass('is-active');
                        $(this).addClass('is-active');
                        $('.japs-dash-chart-pane').removeClass('is-active').attr('hidden', true);
                        $('.japs-dash-chart-pane[data-period="' + period + '"]').addClass('is-active').removeAttr('hidden');
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
