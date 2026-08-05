@extends('layouts.app')
@section('title', __('ecommerce::lang.dashboard'))

@section('content')
@php
    $pending = (int) ($stats['pending'] ?? 0);
    $openFulfill = $pending + (int) ($stats['processing'] ?? 0) + (int) ($stats['shipped'] ?? 0);
    $fulfillHealth = $openFulfill > 0
        ? max(0, 100 - min(100, (int) round(($pending / max(1, $openFulfill)) * 100)))
        : 100;
    $topMax = 0;
    foreach ($top_products as $tp) {
        $topMax = max($topMax, (float) ($tp->qty ?? 0));
    }
@endphp
<section class="content inv-dash ecom-inv-dash">
    {{-- Hero pulse --}}
    <div class="inv-pulse">
        <div class="inv-pulse__glow" aria-hidden="true"></div>
        <div class="inv-pulse__top">
            <div class="inv-pulse__intro">
                <p class="inv-pulse__eyebrow">@lang('ecommerce::lang.dashboard')</p>
                <h1 class="inv-pulse__title">{{ __('home.welcome_message', ['name' => Session::get('user.first_name')]) }}</h1>
                <p class="inv-pulse__sub">
                    {{ $settings->store_name ?: __('ecommerce::lang.ecommerce') }}
                    · {{ @format_date(\Carbon::now()) }}
                </p>
            </div>
            <div class="inv-pulse__actions">
                <a class="inv-btn inv-btn-ghost" href="{{ url(config('ecommerce.route_prefix', 'shop')) }}" target="_blank" rel="noopener">
                    @lang('ecommerce::lang.view_website')
                </a>
                <a class="inv-btn inv-btn-ghost" href="{{ route('ecommerce.admin.orders') }}">
                    @lang('ecommerce::lang.orders')
                </a>
                <a class="inv-btn inv-btn-primary" href="{{ route('ecommerce.admin.settings') }}">
                    @lang('ecommerce::lang.settings')
                </a>
            </div>
        </div>

        @if(!$settings->is_enabled)
            <div class="ecom-inv-alert">
                <span>@lang('ecommerce::lang.store_disabled_banner')</span>
                <a class="inv-btn inv-btn-primary" href="{{ route('ecommerce.admin.settings') }}">@lang('ecommerce::lang.enable_ecommerce')</a>
            </div>
        @endif

        <div class="inv-pulse__spotlight">
            <a href="{{ route('ecommerce.admin.reports') }}" class="inv-spotlight">
                <div class="inv-spotlight__label">@lang('ecommerce::lang.sales_30d')</div>
                <div class="inv-spotlight__value">
                    <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $stats['sales_30d'] }}">{{ $stats['sales_30d'] }}</span>
                </div>
                <div class="inv-spotlight__meta">
                    @lang('ecommerce::lang.avg_order')
                    <strong>
                        <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $stats['avg_order'] }}">{{ $stats['avg_order'] }}</span>
                    </strong>
                    · @lang('ecommerce::lang.last_30_days')
                </div>
            </a>

            <a href="{{ route('ecommerce.admin.orders') }}" class="inv-health">
                <div class="inv-health__head">
                    <span>@lang('ecommerce::lang.pending_orders')</span>
                    <strong class="{{ $pending > 0 ? 'is-warn' : '' }}">{{ @num_format($pending) }}</strong>
                </div>
                <div class="inv-health__track" aria-hidden="true">
                    <span class="inv-health__fill {{ $pending > 0 ? 'is-warn' : 'is-ok' }}" style="width: {{ $fulfillHealth }}%"></span>
                </div>
                <div class="inv-health__hint">
                    @lang('ecommerce::lang.processing'): {{ @num_format($stats['processing'] ?? 0) }}
                    · @lang('ecommerce::lang.shipped'): {{ @num_format($stats['shipped'] ?? 0) }}
                </div>
            </a>
        </div>
    </div>

    {{-- Quick metrics --}}
    <div class="inv-metric-row ecom-inv-metrics">
        <a href="{{ route('ecommerce.admin.orders') }}" class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.today_orders')</div>
                <div class="inv-metric__value">{{ @num_format($stats['today_orders'] ?? 0) }}</div>
                <div class="inv-metric__hint">
                    @lang('ecommerce::lang.today_sales'):
                    <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $stats['today_sales'] ?? 0 }}">{{ $stats['today_sales'] ?? 0 }}</span>
                </div>
            </div>
        </a>

        <a href="{{ route('ecommerce.admin.customers') }}" class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.online_customers')</div>
                <div class="inv-metric__value">{{ @num_format($stats['customers'] ?? 0) }}</div>
                <div class="inv-metric__hint">
                    @if(($stats['pending_customers'] ?? 0) > 0)
                        {{ @num_format($stats['pending_customers']) }} @lang('ecommerce::lang.awaiting_approval')
                    @else
                        @lang('ecommerce::lang.all_approved')
                    @endif
                </div>
            </div>
        </a>

        <a href="{{ route('ecommerce.admin.products') }}" class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05"/><path d="M12 22.08V12"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.online_products')</div>
                <div class="inv-metric__value">{{ @num_format($stats['products'] ?? 0) }}</div>
                <div class="inv-metric__hint">{{ @num_format($stats['coupons'] ?? 0) }} @lang('ecommerce::lang.coupons')</div>
            </div>
        </a>

        <a href="{{ route('ecommerce.admin.reviews') }}" class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.pending_reviews')</div>
                <div class="inv-metric__value {{ ($stats['pending_reviews'] ?? 0) > 0 ? 'is-warn' : '' }}">{{ @num_format($stats['pending_reviews'] ?? 0) }}</div>
                <div class="inv-metric__hint">@lang('ecommerce::lang.completed_orders'): {{ @num_format($stats['completed'] ?? 0) }}</div>
            </div>
        </a>
    </div>

    {{-- Charts + top products --}}
    <div class="inv-bento ecom-inv-bento">
        <div class="inv-panel ecom-inv-panel--chart">
            <div class="inv-panel__header">
                <div class="inv-panel__heading">
                    <span class="inv-panel__chip inv-panel__chip--slate">@lang('ecommerce::lang.revenue')</span>
                    <h3 class="inv-panel__title">@lang('ecommerce::lang.revenue_orders_chart')</h3>
                </div>
            </div>
            <div class="inv-panel__body ecom-inv-chart-body">
                <div class="ecom-inv-revenue-hero">
                    <span class="ecom-inv-revenue-hero__value display_currency" data-currency_symbol="true" data-orig-value="{{ $stats['sales_30d'] }}">{{ $stats['sales_30d'] }}</span>
                    <span class="ecom-inv-revenue-hero__meta">@lang('ecommerce::lang.last_30_days')</span>
                </div>
                <div id="ecom_revenue_chart" class="ecom-inv-chart"></div>
            </div>
        </div>

        <div class="inv-panel">
            <div class="inv-panel__header">
                <div class="inv-panel__heading">
                    <span class="inv-panel__chip inv-panel__chip--teal">@lang('ecommerce::lang.fulfillment')</span>
                    <h3 class="inv-panel__title">@lang('ecommerce::lang.fulfillment_breakdown')</h3>
                </div>
            </div>
            <div class="inv-panel__body ecom-inv-chart-body">
                <div id="ecom_fulfillment_chart" class="ecom-inv-chart ecom-inv-chart--sm"></div>
                <div class="ecom-inv-overview">
                    <div class="ecom-inv-overview__row">
                        <span>@lang('ecommerce::lang.completed_orders')</span>
                        <strong>{{ @num_format($stats['completed'] ?? 0) }}</strong>
                    </div>
                    <div class="ecom-inv-overview__row">
                        <span>@lang('ecommerce::lang.cancelled_orders')</span>
                        <strong class="is-warn">{{ @num_format($stats['cancelled'] ?? 0) }}</strong>
                    </div>
                </div>
            </div>
            <div class="inv-panel__footer">
                <a href="{{ route('ecommerce.admin.orders') }}" class="inv-panel__link">
                    @lang('ecommerce::lang.view_all')
                    <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <div class="inv-panel inv-panel--cats">
            <div class="inv-panel__header">
                <div class="inv-panel__heading">
                    <span class="inv-panel__chip inv-panel__chip--warn">@lang('ecommerce::lang.top_products')</span>
                    <h3 class="inv-panel__title">@lang('ecommerce::lang.top_products')</h3>
                </div>
            </div>
            <div class="inv-panel__body">
                @forelse($top_products as $row)
                    @php
                        $qty = (float) ($row->qty ?? 0);
                        $bar = $topMax > 0 ? min(100, round(($qty / $topMax) * 100)) : 0;
                    @endphp
                    <div class="inv-cat">
                        <div class="inv-cat__top">
                            <div class="inv-cat__name">{{ $row->name }}</div>
                            <div class="inv-cat__value">{{ @num_format($qty) }}</div>
                        </div>
                        <div class="inv-bar inv-bar--teal" aria-hidden="true"><span style="width: {{ $bar }}%"></span></div>
                        <div class="inv-cat__meta">
                            <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $row->revenue ?? 0 }}">{{ $row->revenue ?? 0 }}</span>
                            · @lang('ecommerce::lang.last_30_days')
                        </div>
                    </div>
                @empty
                    <div class="inv-empty">@lang('ecommerce::lang.no_orders_yet')</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent orders --}}
    <div class="inv-panel ecom-inv-recent">
        <div class="inv-panel__header">
            <div class="inv-panel__heading">
                <span class="inv-panel__chip inv-panel__chip--slate">@lang('ecommerce::lang.orders')</span>
                <h3 class="inv-panel__title">@lang('ecommerce::lang.recent_orders')</h3>
            </div>
            <a href="{{ route('ecommerce.admin.orders') }}" class="inv-panel__link">@lang('ecommerce::lang.view_all') →</a>
        </div>
        <div class="inv-panel__body" style="padding: 4px 12px 12px;">
            <div class="table-responsive">
                <table class="table ecom-inv-table">
                    <thead>
                        <tr>
                            <th>@lang('sale.invoice_no')</th>
                            <th>@lang('messages.date')</th>
                            <th>@lang('contact.name')</th>
                            <th>@lang('ecommerce::lang.fulfillment')</th>
                            <th>@lang('sale.total_amount')</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($recent_orders as $order)
                        @php $meta = $metas[$order->id] ?? null; $st = $meta->fulfillment_status ?? 'pending'; @endphp
                        <tr>
                            <td><a href="{{ route('ecommerce.admin.orders.show', $order->id) }}">{{ $order->invoice_no }}</a></td>
                            <td>{{ @format_datetime($order->transaction_date) }}</td>
                            <td>{{ optional($order->contact)->name ?: '—' }}</td>
                            <td><span class="ecom-inv-pill ecom-inv-pill--{{ $st }}">{{ ucfirst($st) }}</span></td>
                            <td>
                                <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $order->final_total }}">{{ $order->final_total }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="inv-empty" style="padding: 28px 8px;">@lang('ecommerce::lang.no_orders_yet')</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script type="text/javascript">
$(function () {
    var chartData = @json($chart);
    var currencySymbol = @json(session('currency')['symbol'] ?? '');

    function money(v) {
        return (currencySymbol ? currencySymbol + ' ' : '') + Highcharts.numberFormat(v, 2);
    }

    if (typeof __currency_convert_recursively === 'function') {
        __currency_convert_recursively($('.ecom-inv-dash'));
    }

    if (typeof Highcharts === 'undefined') {
        return;
    }

    Highcharts.setOptions({
        chart: { style: { fontFamily: 'inherit' } },
        credits: { enabled: false },
        colors: ['#2563eb', '#0d9488', '#d97706', '#7c3aed', '#ef4444', '#64748b', '#06b6d4']
    });

    Highcharts.chart('ecom_revenue_chart', {
        chart: { type: 'column', height: 280, backgroundColor: 'transparent' },
        title: { text: null },
        xAxis: {
            categories: chartData.labels,
            tickInterval: 4,
            lineColor: '#e2e8f0',
            tickColor: '#e2e8f0',
            labels: { style: { color: '#94a3b8', fontSize: '11px', fontWeight: '500' } }
        },
        yAxis: [{
            title: { text: null },
            gridLineColor: '#f1f5f9',
            min: 0,
            labels: {
                style: { color: '#94a3b8', fontSize: '11px' },
                formatter: function () { return money(this.value); }
            }
        }, {
            title: { text: null },
            opposite: true,
            gridLineWidth: 0,
            min: 0,
            labels: { style: { color: '#94a3b8', fontSize: '11px' } }
        }],
        tooltip: {
            shared: true,
            backgroundColor: '#0f172a',
            borderWidth: 0,
            borderRadius: 8,
            style: { color: '#f8fafc' },
            formatter: function () {
                var s = '<b>' + this.x + '</b>';
                this.points.forEach(function (p) {
                    var val = p.series.options.isMoney ? money(p.y) : Highcharts.numberFormat(p.y, 0);
                    s += '<br/><span style="color:' + p.color + '">●</span> ' + p.series.name + ': <b>' + val + '</b>';
                });
                return s;
            }
        },
        legend: { enabled: false },
        plotOptions: {
            column: { borderRadius: 6, borderWidth: 0, pointPadding: 0.12, groupPadding: 0.08 }
        },
        series: [{
            name: @json(__('ecommerce::lang.revenue')),
            data: chartData.revenue,
            isMoney: true,
            color: {
                linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                stops: [[0, '#2563eb'], [1, '#93c5fd']]
            },
            yAxis: 0
        }, {
            type: 'column',
            name: @json(__('ecommerce::lang.orders')),
            data: chartData.orders,
            color: '#e2e8f0',
            yAxis: 1
        }]
    });

    var fulfillmentPairs = [];
    (chartData.fulfillment_labels || []).forEach(function (label, i) {
        fulfillmentPairs.push({ name: label.charAt(0).toUpperCase() + label.slice(1), y: chartData.fulfillment_values[i] });
    });

    Highcharts.chart('ecom_fulfillment_chart', {
        chart: { type: 'pie', height: 200, backgroundColor: 'transparent', spacing: [0, 0, 0, 0] },
        title: { text: null },
        tooltip: { pointFormat: '<b>{point.y}</b> ({point.percentage:.1f}%)' },
        plotOptions: {
            pie: {
                startAngle: -90,
                endAngle: 90,
                innerSize: '72%',
                size: '115%',
                center: ['50%', '78%'],
                dataLabels: { enabled: false },
                showInLegend: true,
                borderWidth: 3,
                borderColor: '#ffffff'
            }
        },
        legend: {
            align: 'center',
            verticalAlign: 'bottom',
            layout: 'horizontal',
            itemStyle: { fontSize: '11px', fontWeight: '600', color: '#64748b' }
        },
        series: [{
            name: @json(__('ecommerce::lang.orders')),
            data: fulfillmentPairs.length ? fulfillmentPairs : [{ name: 'None', y: 1, color: '#e2e8f0' }]
        }]
    });
});
</script>
@endsection
