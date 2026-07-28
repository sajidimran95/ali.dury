@extends('layouts.app')
@section('title', __('ecommerce::lang.dashboard'))

@section('content')
<section class="content ecom-dash">
    <div class="ed-head">
        <div>
            <h1 class="ed-title">{{ __('home.welcome_message', ['name' => Session::get('user.first_name')]) }}</h1>
            <p class="ed-sub">
                {{ $settings->store_name ?: __('ecommerce::lang.ecommerce') }}
                · {{ @format_date(\Carbon::now()) }}
            </p>
        </div>
        <div class="ed-actions">
            <a class="ed-btn" href="{{ url(config('ecommerce.route_prefix', 'shop')) }}" target="_blank">
                <i class="fas fa-external-link-alt"></i> @lang('ecommerce::lang.view_website')
            </a>
            <a class="ed-btn" href="{{ route('ecommerce.admin.orders') }}">
                <i class="fas fa-shopping-bag"></i> @lang('ecommerce::lang.orders')
            </a>
            <a class="ed-btn ed-btn-primary" href="{{ route('ecommerce.admin.settings') }}">
                <i class="fas fa-cog"></i> @lang('ecommerce::lang.settings')
            </a>
        </div>
    </div>

    @if(!$settings->is_enabled)
        <div class="ed-alert">
            <span><i class="fas fa-exclamation-triangle"></i> @lang('ecommerce::lang.store_disabled_banner')</span>
            <a class="ed-btn ed-btn-primary" href="{{ route('ecommerce.admin.settings') }}">@lang('ecommerce::lang.enable_ecommerce')</a>
        </div>
    @endif

    <div class="ed-kpis">
        <a href="{{ route('ecommerce.admin.orders') }}" class="ed-kpi ed-kpi--blue">
            <div class="ed-kpi-body">
                <div class="ed-kpi-label">@lang('ecommerce::lang.today_orders')</div>
                <div class="ed-kpi-value">{{ number_format($stats['today_orders']) }}</div>
                <div class="ed-kpi-hint">@lang('ecommerce::lang.today_sales'): <span class="display_currency" data-currency_symbol="true">{{ $stats['today_sales'] }}</span></div>
            </div>
            <span class="ed-kpi-icon"><i class="fas fa-shopping-cart"></i></span>
        </a>
        <a href="{{ route('ecommerce.admin.reports') }}" class="ed-kpi ed-kpi--teal">
            <div class="ed-kpi-body">
                <div class="ed-kpi-label">@lang('ecommerce::lang.sales_30d')</div>
                <div class="ed-kpi-value is-money"><span class="display_currency" data-currency_symbol="true">{{ $stats['sales_30d'] }}</span></div>
                <div class="ed-kpi-hint">@lang('ecommerce::lang.avg_order'): <span class="display_currency" data-currency_symbol="true">{{ $stats['avg_order'] }}</span></div>
            </div>
            <span class="ed-kpi-icon"><i class="fas fa-chart-line"></i></span>
        </a>
        <a href="{{ route('ecommerce.admin.orders') }}" class="ed-kpi ed-kpi--amber">
            <div class="ed-kpi-body">
                <div class="ed-kpi-label">@lang('ecommerce::lang.pending_orders')</div>
                <div class="ed-kpi-value is-warn">{{ number_format($stats['pending']) }}</div>
                <div class="ed-kpi-hint">@lang('ecommerce::lang.processing'): {{ $stats['processing'] }} · @lang('ecommerce::lang.shipped'): {{ $stats['shipped'] }}</div>
            </div>
            <span class="ed-kpi-icon"><i class="fas fa-file-invoice"></i></span>
        </a>
        <a href="{{ route('ecommerce.admin.customers') }}" class="ed-kpi ed-kpi--violet">
            <div class="ed-kpi-body">
                <div class="ed-kpi-label">@lang('ecommerce::lang.online_customers')</div>
                <div class="ed-kpi-value">{{ number_format($stats['customers']) }}</div>
                <div class="ed-kpi-hint">
                    @if($stats['pending_customers'] > 0)
                        {{ $stats['pending_customers'] }} @lang('ecommerce::lang.awaiting_approval')
                    @else
                        @lang('ecommerce::lang.all_approved')
                    @endif
                </div>
            </div>
            <span class="ed-kpi-icon"><i class="fas fa-users"></i></span>
        </a>
    </div>

    <div class="ed-grid">
        <div class="ed-card">
            <div class="ed-card-h">
                <div>
                    <h3>@lang('ecommerce::lang.revenue_orders_chart')</h3>
                    <div class="ed-revenue-hero">
                        <span class="ed-revenue-hero__value display_currency" data-currency_symbol="true">{{ $stats['sales_30d'] }}</span>
                        <span class="ed-sub" style="margin:0">@lang('ecommerce::lang.last_30_days')</span>
                    </div>
                </div>
            </div>
            <div class="ed-card-b">
                <div id="ecom_revenue_chart" class="ed-chart"></div>
            </div>
        </div>
        <div class="ed-card ed-card--overview">
            <div class="ed-card-h">
                <h3>@lang('ecommerce::lang.fulfillment_breakdown')</h3>
                <a href="{{ route('ecommerce.admin.orders') }}">@lang('ecommerce::lang.view_all')</a>
            </div>
            <div class="ed-card-b">
                <div id="ecom_fulfillment_chart" class="ed-chart-sm"></div>
                <div class="ed-overview-footer">
                    <div class="ed-overview-footer__row">
                        <span>@lang('ecommerce::lang.completed_orders')</span>
                        <strong>{{ number_format($stats['completed']) }}</strong>
                    </div>
                    <div class="ed-overview-footer__row">
                        <span>@lang('ecommerce::lang.cancelled_orders')</span>
                        <strong class="text-danger">{{ number_format($stats['cancelled']) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ed-card ed-card--recent">
        <div class="ed-card-h">
            <h3>@lang('ecommerce::lang.recent_orders')</h3>
            <a href="{{ route('ecommerce.admin.orders') }}">@lang('ecommerce::lang.view_all')</a>
        </div>
        <div class="ed-card-b" style="padding:0;">
            <div class="table-responsive">
                <table class="table ed-table">
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
                            <td><span class="ed-pill ed-pill-{{ $st }}">{{ ucfirst($st) }}</span></td>
                            <td><span class="display_currency" data-currency_symbol="true">{{ $order->final_total }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="ed-empty">@lang('ecommerce::lang.no_orders_yet')</td></tr>
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
        __currency_convert_recursively($('.ecom-dash'));
    }

    if (typeof Highcharts === 'undefined') {
        return;
    }

    Highcharts.setOptions({
        chart: { style: { fontFamily: 'inherit' } },
        credits: { enabled: false },
        colors: ['#3b82f6', '#22c55e', '#f59e0b', '#8b5cf6', '#ef4444', '#64748b', '#06b6d4']
    });

    Highcharts.chart('ecom_revenue_chart', {
        chart: { type: 'column', height: 300, backgroundColor: 'transparent' },
        title: { text: null },
        xAxis: {
            categories: chartData.labels,
            tickInterval: 4,
            lineColor: '#e8ecf1',
            tickColor: '#e8ecf1',
            labels: { style: { color: '#94a3b8', fontSize: '11px', fontWeight: '500' } }
        },
        yAxis: [{
            title: { text: null },
            gridLineColor: '#eef2f7',
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
                stops: [[0, '#3b82f6'], [1, '#93c5fd']]
            },
            yAxis: 0
        }, {
            type: 'column',
            name: @json(__('ecommerce::lang.orders')),
            data: chartData.orders,
            color: '#e8edf5',
            yAxis: 1
        }]
    });

    var fulfillmentPairs = [];
    (chartData.fulfillment_labels || []).forEach(function (label, i) {
        fulfillmentPairs.push({ name: label.charAt(0).toUpperCase() + label.slice(1), y: chartData.fulfillment_values[i] });
    });

    Highcharts.chart('ecom_fulfillment_chart', {
        chart: { type: 'pie', height: 220, backgroundColor: 'transparent', spacing: [0, 0, 0, 0] },
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
