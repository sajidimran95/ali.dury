@extends('layouts.app')
@section('title', __('ecommerce::lang.dashboard'))

@section('content')
<style>
.ecom-dash { --ed-blue:#2563eb; --ed-ink:#0f172a; --ed-muted:#64748b; --ed-line:#e8ecf1; --ed-bg:#f8fafc; }
.ecom-dash .ed-head { display:flex; flex-wrap:wrap; gap:12px; align-items:flex-start; justify-content:space-between; margin-bottom:18px; }
.ecom-dash .ed-title { margin:0; font-size:28px; font-weight:800; color:var(--ed-ink); letter-spacing:-.02em; }
.ecom-dash .ed-sub { margin:4px 0 0; color:var(--ed-muted); font-size:13px; }
.ecom-dash .ed-actions { display:flex; flex-wrap:wrap; gap:8px; }
.ecom-dash .ed-btn {
    height:38px; padding:0 14px; border-radius:10px; border:1px solid var(--ed-line);
    background:#fff; color:#334155; font-weight:600; font-size:13px;
    display:inline-flex; align-items:center; gap:6px; text-decoration:none !important;
}
.ecom-dash .ed-btn:hover { background:var(--ed-bg); color:#0f172a; }
.ecom-dash .ed-btn-primary { background:var(--ed-blue); border-color:var(--ed-blue); color:#fff !important; }
.ecom-dash .ed-btn-primary:hover { background:#1d4ed8; color:#fff !important; }
.ecom-dash .ed-alert {
    border-radius:12px; border:1px solid #fde68a; background:#fffbeb; color:#92400e;
    padding:12px 16px; margin-bottom:16px; display:flex; gap:10px; align-items:center; justify-content:space-between; flex-wrap:wrap;
}
.ecom-dash .ed-kpis { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; margin-bottom:16px; }
.ecom-dash .ed-kpi {
    --kpi-accent:#4f8fce; --kpi-soft:#eef6fd;
    position:relative; overflow:hidden;
    display:flex; align-items:flex-start; justify-content:space-between; gap:14px;
    background:#fff; border:1px solid #e7eaf0; border-radius:18px;
    padding:22px 20px 18px; min-height:112px;
    box-shadow:0 2px 10px rgba(15,20,37,.045);
    transition:transform .15s ease, box-shadow .15s ease;
}
.ecom-dash .ed-kpi::before {
    content:""; position:absolute; top:0; left:0; right:0; height:4px;
    border-radius:18px 18px 0 0; background:var(--kpi-accent);
}
.ecom-dash .ed-kpi:hover { transform:translateY(-2px); box-shadow:0 8px 22px rgba(15,20,37,.07); }
.ecom-dash .ed-kpi--blue { --kpi-accent:#4f8fce; --kpi-soft:#eef6fd; }
.ecom-dash .ed-kpi--teal { --kpi-accent:#4db39e; --kpi-soft:#ecf8f4; }
.ecom-dash .ed-kpi--amber { --kpi-accent:#d5aa35; --kpi-soft:#fff8e6; }
.ecom-dash .ed-kpi--violet { --kpi-accent:#7c72c9; --kpi-soft:#f5f0fc; }
.ecom-dash .ed-kpi--rose { --kpi-accent:#d9597b; --kpi-soft:#fff0f4; }
.ecom-dash .ed-kpi--slate { --kpi-accent:#64748b; --kpi-soft:#f1f5f9; }
.ecom-dash .ed-kpi--cyan { --kpi-accent:#0891b2; --kpi-soft:#ecfeff; }
.ecom-dash .ed-kpi--green { --kpi-accent:#059669; --kpi-soft:#ecfdf5; }
.ecom-dash .ed-kpi-body { flex:1; min-width:0; }
.ecom-dash .ed-kpi-label { font-size:14px; color:#7a8495; font-weight:500; margin:0 0 10px; display:flex; align-items:center; gap:6px; }
.ecom-dash .ed-kpi-value { font-size:26px; font-weight:700; color:#151b2b; line-height:1.15; letter-spacing:-.03em; margin:0; }
.ecom-dash .ed-kpi-value.is-money { color:#2d927b; }
.ecom-dash .ed-kpi-value.is-warn { color:#b78616; }
.ecom-dash .ed-kpi-value.is-danger { color:#be123c; }
.ecom-dash .ed-kpi-hint { margin-top:8px; font-size:12px; color:#94a3b8; }
.ecom-dash .ed-kpi-hint a { color:#4f8fce; font-weight:600; }
.ecom-dash .ed-kpi-icon {
    width:48px; height:48px; border-radius:14px; flex-shrink:0;
    background:var(--kpi-soft); color:var(--kpi-accent);
    display:inline-flex; align-items:center; justify-content:center; font-size:18px;
}

.ecom-dash .ed-grid { display:grid; grid-template-columns:1.6fr 1fr; gap:14px; margin-bottom:14px; }
.ecom-dash .ed-grid-3 { display:grid; grid-template-columns:1.2fr 1fr 1fr; gap:14px; margin-bottom:14px; }
.ecom-dash .ed-card {
    background:#fff; border:1px solid var(--ed-line); border-radius:14px;
    box-shadow:0 1px 2px rgba(15,23,42,.04); overflow:hidden;
}
.ecom-dash .ed-card-h {
    padding:14px 16px; border-bottom:1px solid #eef2f7;
    display:flex; align-items:center; justify-content:space-between; gap:8px;
}
.ecom-dash .ed-card-h h3 { margin:0; font-size:15px; font-weight:700; color:var(--ed-ink); }
.ecom-dash .ed-card-b { padding:14px 16px; }
.ecom-dash .ed-chart { width:100%; height:300px; }
.ecom-dash .ed-chart-sm { width:100%; height:240px; }
.ecom-dash table.ed-table { width:100%; margin:0; }
.ecom-dash table.ed-table th {
    font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:#94a3b8;
    border-bottom:1px solid #eef2f7 !important; font-weight:700; padding:10px 8px !important; background:#fff !important;
}
.ecom-dash table.ed-table td { padding:11px 8px !important; border-top:1px solid #f1f5f9 !important; vertical-align:middle !important; font-size:13px; }
.ecom-dash table.ed-table a { color:var(--ed-blue); font-weight:600; }
.ecom-dash .ed-pill {
    display:inline-flex; align-items:center; padding:3px 8px; border-radius:999px;
    font-size:11px; font-weight:700; line-height:1.2;
}
.ecom-dash .ed-pill-pending { background:#fff7ed; color:#c2410c; }
.ecom-dash .ed-pill-confirmed,.ecom-dash .ed-pill-processing,.ecom-dash .ed-pill-packed { background:#eff6ff; color:#1d4ed8; }
.ecom-dash .ed-pill-shipped { background:#f5f3ff; color:#6d28d9; }
.ecom-dash .ed-pill-delivered { background:#ecfdf5; color:#047857; }
.ecom-dash .ed-pill-cancelled,.ecom-dash .ed-pill-returned { background:#fff1f2; color:#be123c; }
.ecom-dash .ed-pill-approved { background:#ecfdf5; color:#047857; }
.ecom-dash .ed-pill-rejected { background:#fff1f2; color:#be123c; }
.ecom-dash .ed-mini { display:flex; align-items:center; justify-content:space-between; gap:8px; padding:8px 0; border-bottom:1px solid #f1f5f9; }
.ecom-dash .ed-mini:last-child { border-bottom:0; }
.ecom-dash .ed-stars { color:#f59e0b; letter-spacing:1px; font-size:12px; }
.ecom-dash .ed-empty { color:#94a3b8; text-align:center; padding:24px 8px; font-size:13px; }
.ecom-dash .ed-quick { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:10px; }
.ecom-dash .ed-quick a {
    display:flex; flex-direction:column; gap:8px; padding:14px; border:1px solid var(--ed-line);
    border-radius:12px; text-decoration:none !important; color:var(--ed-ink); background:#fff;
}
.ecom-dash .ed-quick a:hover { border-color:#bfdbfe; background:#f8fbff; }
.ecom-dash .ed-quick i { color:var(--ed-blue); font-size:16px; }
.ecom-dash .ed-quick span { font-size:13px; font-weight:700; }
.ecom-dash .ed-quick small { color:var(--ed-muted); font-size:11px; font-weight:500; }
@media (max-width:1199px) {
    .ecom-dash .ed-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .ecom-dash .ed-grid, .ecom-dash .ed-grid-3 { grid-template-columns:1fr; }
    .ecom-dash .ed-quick { grid-template-columns:repeat(2,minmax(0,1fr)); }
}
@media (max-width:767px) {
    .ecom-dash .ed-kpis { grid-template-columns:1fr; }
    .ecom-dash .ed-title { font-size:22px; }
}
</style>

<section class="content ecom-dash">
    <div class="ed-head">
        <div>
            <h1 class="ed-title">@lang('ecommerce::lang.dashboard')</h1>
            <p class="ed-sub">
                {{ $settings->store_name ?: __('ecommerce::lang.ecommerce') }}
                · @lang('ecommerce::lang.last_30_days_overview')
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
        <div class="ed-kpi ed-kpi--blue">
            <div class="ed-kpi-body">
                <div class="ed-kpi-label">@lang('ecommerce::lang.today_orders')</div>
                <div class="ed-kpi-value">{{ number_format($stats['today_orders']) }}</div>
                <div class="ed-kpi-hint">@lang('ecommerce::lang.today_sales'): <span class="display_currency" data-currency_symbol="true">{{ $stats['today_sales'] }}</span></div>
            </div>
            <span class="ed-kpi-icon"><i class="fas fa-shopping-cart"></i></span>
        </div>
        <div class="ed-kpi ed-kpi--teal">
            <div class="ed-kpi-body">
                <div class="ed-kpi-label">@lang('ecommerce::lang.sales_30d')</div>
                <div class="ed-kpi-value is-money"><span class="display_currency" data-currency_symbol="true">{{ $stats['sales_30d'] }}</span></div>
                <div class="ed-kpi-hint">@lang('ecommerce::lang.avg_order'): <span class="display_currency" data-currency_symbol="true">{{ $stats['avg_order'] }}</span></div>
            </div>
            <span class="ed-kpi-icon"><i class="fas fa-chart-line"></i></span>
        </div>
        <div class="ed-kpi ed-kpi--amber">
            <div class="ed-kpi-body">
                <div class="ed-kpi-label">@lang('ecommerce::lang.pending_orders')</div>
                <div class="ed-kpi-value is-warn">{{ number_format($stats['pending']) }}</div>
                <div class="ed-kpi-hint">@lang('ecommerce::lang.processing'): {{ $stats['processing'] }} · @lang('ecommerce::lang.shipped'): {{ $stats['shipped'] }}</div>
            </div>
            <span class="ed-kpi-icon"><i class="fas fa-file-invoice"></i></span>
        </div>
        <div class="ed-kpi ed-kpi--green">
            <div class="ed-kpi-body">
                <div class="ed-kpi-label">@lang('ecommerce::lang.completed_orders')</div>
                <div class="ed-kpi-value">{{ number_format($stats['completed']) }}</div>
                <div class="ed-kpi-hint">@lang('ecommerce::lang.cancelled_orders'): <span class="text-danger">{{ $stats['cancelled'] }}</span></div>
            </div>
            <span class="ed-kpi-icon"><i class="fas fa-check-circle"></i></span>
        </div>
        <div class="ed-kpi ed-kpi--teal">
            <div class="ed-kpi-body">
                <div class="ed-kpi-label">@lang('ecommerce::lang.total_sales')</div>
                <div class="ed-kpi-value is-money"><span class="display_currency" data-currency_symbol="true">{{ $stats['total_sales'] }}</span></div>
                <div class="ed-kpi-hint">@lang('ecommerce::lang.total_orders'): {{ number_format($stats['total_orders']) }}</div>
            </div>
            <span class="ed-kpi-icon"><i class="fas fa-wallet"></i></span>
        </div>
        <div class="ed-kpi ed-kpi--violet">
            <div class="ed-kpi-body">
                <div class="ed-kpi-label">@lang('ecommerce::lang.online_customers')</div>
                <div class="ed-kpi-value">{{ number_format($stats['customers']) }}</div>
                <div class="ed-kpi-hint">
                    @if($stats['pending_customers'] > 0)
                        <a href="{{ route('ecommerce.admin.customers', ['status' => 'pending']) }}">{{ $stats['pending_customers'] }} @lang('ecommerce::lang.awaiting_approval')</a>
                    @else
                        @lang('ecommerce::lang.all_approved')
                    @endif
                </div>
            </div>
            <span class="ed-kpi-icon"><i class="fas fa-users"></i></span>
        </div>
        <div class="ed-kpi ed-kpi--cyan">
            <div class="ed-kpi-body">
                <div class="ed-kpi-label">@lang('ecommerce::lang.online_products')</div>
                <div class="ed-kpi-value">{{ number_format($stats['products']) }}</div>
                <div class="ed-kpi-hint"><a href="{{ route('ecommerce.admin.products') }}">@lang('ecommerce::lang.manage_products')</a></div>
            </div>
            <span class="ed-kpi-icon"><i class="fas fa-boxes"></i></span>
        </div>
        <div class="ed-kpi ed-kpi--rose">
            <div class="ed-kpi-body">
                <div class="ed-kpi-label">@lang('ecommerce::lang.pending_reviews')</div>
                <div class="ed-kpi-value {{ $stats['pending_reviews'] ? 'is-warn' : '' }}">{{ number_format($stats['pending_reviews']) }}</div>
                <div class="ed-kpi-hint">@lang('ecommerce::lang.active_coupons'): {{ $stats['coupons'] }}</div>
            </div>
            <span class="ed-kpi-icon"><i class="fas fa-star"></i></span>
        </div>
    </div>

    <div class="ed-grid">
        <div class="ed-card">
            <div class="ed-card-h">
                <h3>@lang('ecommerce::lang.revenue_orders_chart')</h3>
                <span class="ed-sub" style="margin:0">@lang('ecommerce::lang.last_30_days')</span>
            </div>
            <div class="ed-card-b">
                <div id="ecom_revenue_chart" class="ed-chart"></div>
            </div>
        </div>
        <div class="ed-card">
            <div class="ed-card-h">
                <h3>@lang('ecommerce::lang.fulfillment_breakdown')</h3>
                <a href="{{ route('ecommerce.admin.orders') }}">@lang('ecommerce::lang.view_all')</a>
            </div>
            <div class="ed-card-b">
                <div id="ecom_fulfillment_chart" class="ed-chart-sm"></div>
            </div>
        </div>
    </div>

    <div class="ed-grid-3">
        <div class="ed-card">
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
                                <th>@lang('contact.name')</th>
                                <th>@lang('sale.total_amount')</th>
                                <th>@lang('ecommerce::lang.fulfillment')</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($recent_orders as $order)
                            @php $meta = $metas[$order->id] ?? null; $st = $meta->fulfillment_status ?? 'pending'; @endphp
                            <tr>
                                <td><a href="{{ route('ecommerce.admin.orders.show', $order->id) }}">{{ $order->invoice_no }}</a></td>
                                <td>{{ optional($order->contact)->name ?: '—' }}</td>
                                <td><span class="display_currency" data-currency_symbol="true">{{ $order->final_total }}</span></td>
                                <td><span class="ed-pill ed-pill-{{ $st }}">{{ ucfirst($st) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="ed-empty">@lang('ecommerce::lang.no_orders_yet')</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="ed-card">
            <div class="ed-card-h">
                <h3>@lang('ecommerce::lang.top_products')</h3>
                <a href="{{ route('ecommerce.admin.reports') }}">@lang('ecommerce::lang.reports')</a>
            </div>
            <div class="ed-card-b">
                @forelse($top_products as $p)
                    <div class="ed-mini">
                        <div>
                            <div style="font-weight:700;font-size:13px;">{{ $p->name }}</div>
                            <small class="text-muted">{{ number_format($p->qty, 0) }} @lang('sale.qty')</small>
                        </div>
                        <div style="font-weight:700;font-size:13px;">
                            <span class="display_currency" data-currency_symbol="true">{{ $p->revenue }}</span>
                        </div>
                    </div>
                @empty
                    <div class="ed-empty">@lang('ecommerce::lang.no_product_sales')</div>
                @endforelse
            </div>
        </div>

        <div class="ed-card">
            <div class="ed-card-h">
                <h3>@lang('ecommerce::lang.payment_methods')</h3>
            </div>
            <div class="ed-card-b">
                <div id="ecom_payment_chart" class="ed-chart-sm"></div>
            </div>
        </div>
    </div>

    <div class="ed-grid">
        <div class="ed-card">
            <div class="ed-card-h">
                <h3>@lang('ecommerce::lang.latest_reviews')</h3>
                <a href="{{ route('ecommerce.admin.reviews') }}">@lang('ecommerce::lang.reviews')</a>
            </div>
            <div class="ed-card-b">
                @forelse($recent_reviews as $review)
                    <div class="ed-mini">
                        <div style="min-width:0;">
                            <div style="font-weight:700;font-size:13px;">
                                {{ optional($review->product)->name ?: '—' }}
                                <span class="ed-pill ed-pill-{{ $review->status }}">{{ ucfirst($review->status) }}</span>
                            </div>
                            <div class="ed-stars">{{ str_repeat('★', (int) $review->rating) }}{{ str_repeat('☆', max(0, 5 - (int) $review->rating)) }}</div>
                            <small class="text-muted tw-block tw-truncate">{{ \Illuminate\Support\Str::limit($review->review, 80) }}</small>
                        </div>
                        <small class="text-muted">{{ optional($review->contact)->name ?: ($review->author_name ?: '—') }}</small>
                    </div>
                @empty
                    <div class="ed-empty">@lang('ecommerce::lang.no_reviews_yet')</div>
                @endforelse
            </div>
        </div>

        <div class="ed-card">
            <div class="ed-card-h">
                <h3>@lang('ecommerce::lang.quick_links')</h3>
            </div>
            <div class="ed-card-b">
                <div class="ed-quick">
                    <a href="{{ route('ecommerce.admin.orders') }}">
                        <i class="fas fa-shopping-bag"></i>
                        <span>@lang('ecommerce::lang.orders')</span>
                        <small>{{ number_format($stats['pending']) }} @lang('ecommerce::lang.need_attention')</small>
                    </a>
                    <a href="{{ route('ecommerce.admin.customers') }}">
                        <i class="fas fa-users"></i>
                        <span>@lang('ecommerce::lang.customers')</span>
                        <small>{{ number_format($stats['customers']) }} @lang('ecommerce::lang.registered')</small>
                    </a>
                    <a href="{{ route('ecommerce.admin.products') }}">
                        <i class="fas fa-boxes"></i>
                        <span>@lang('ecommerce::lang.products')</span>
                        <small>{{ number_format($stats['products']) }} @lang('ecommerce::lang.online')</small>
                    </a>
                    <a href="{{ route('ecommerce.admin.coupons') }}">
                        <i class="fas fa-ticket-alt"></i>
                        <span>@lang('ecommerce::lang.coupons')</span>
                        <small>{{ number_format($stats['coupons']) }} @lang('ecommerce::lang.active')</small>
                    </a>
                    <a href="{{ route('ecommerce.admin.banners') }}">
                        <i class="fas fa-images"></i>
                        <span>@lang('ecommerce::lang.banners')</span>
                        <small>@lang('ecommerce::lang.manage_storefront')</small>
                    </a>
                    <a href="{{ route('ecommerce.admin.collections') }}">
                        <i class="fas fa-layer-group"></i>
                        <span>@lang('ecommerce::lang.collections')</span>
                        <small>@lang('ecommerce::lang.manage_storefront')</small>
                    </a>
                    <a href="{{ route('ecommerce.admin.shipping_tax') }}">
                        <i class="fas fa-truck"></i>
                        <span>@lang('ecommerce::lang.shipping_tax')</span>
                        <small>@lang('ecommerce::lang.configure')</small>
                    </a>
                    <a href="{{ route('ecommerce.admin.reports') }}">
                        <i class="fas fa-chart-bar"></i>
                        <span>@lang('ecommerce::lang.reports')</span>
                        <small>@lang('ecommerce::lang.performance')</small>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script type="text/javascript">
(function () {
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
        colors: ['#2563eb', '#0d9488', '#f59e0b', '#8b5cf6', '#ef4444', '#64748b', '#06b6d4', '#84cc16']
    });

    Highcharts.chart('ecom_revenue_chart', {
        chart: { type: 'areaspline', height: 300, backgroundColor: 'transparent' },
        title: { text: null },
        xAxis: {
            categories: chartData.labels,
            tickInterval: 4,
            lineColor: '#e2e8f0',
            tickColor: '#e2e8f0',
            labels: { style: { color: '#94a3b8', fontSize: '11px' } }
        },
        yAxis: [{
            title: { text: null },
            gridLineColor: '#f1f5f9',
            labels: {
                style: { color: '#94a3b8', fontSize: '11px' },
                formatter: function () { return money(this.value); }
            }
        }, {
            title: { text: null },
            opposite: true,
            gridLineWidth: 0,
            labels: { style: { color: '#94a3b8', fontSize: '11px' } }
        }],
        tooltip: {
            shared: true,
            backgroundColor: '#0f172a',
            borderWidth: 0,
            style: { color: '#f8fafc' },
            formatter: function () {
                var s = '<b>' + this.x + '</b>';
                this.points.forEach(function (p) {
                    var val = p.series.name.indexOf('Revenue') >= 0 || p.series.options.isMoney ? money(p.y) : Highcharts.numberFormat(p.y, 0);
                    s += '<br/><span style="color:' + p.color + '">●</span> ' + p.series.name + ': <b>' + val + '</b>';
                });
                return s;
            }
        },
        legend: { align: 'right', verticalAlign: 'top', floating: true },
        plotOptions: {
            areaspline: {
                fillOpacity: 0.15,
                marker: { enabled: false, radius: 3, states: { hover: { enabled: true } } }
            },
            column: { borderRadius: 3, borderWidth: 0.4 }
        },
        series: [{
            name: @json(__('ecommerce::lang.revenue')),
            data: chartData.revenue,
            isMoney: true,
            color: '#2563eb',
            yAxis: 0
        }, {
            type: 'column',
            name: @json(__('ecommerce::lang.orders')),
            data: chartData.orders,
            color: '#94a3b8',
            yAxis: 1
        }]
    });

    var fulfillmentPairs = [];
    (chartData.fulfillment_labels || []).forEach(function (label, i) {
        fulfillmentPairs.push({ name: label.charAt(0).toUpperCase() + label.slice(1), y: chartData.fulfillment_values[i] });
    });

    Highcharts.chart('ecom_fulfillment_chart', {
        chart: { type: 'pie', height: 240, backgroundColor: 'transparent' },
        title: { text: null },
        tooltip: { pointFormat: '<b>{point.y}</b> ({point.percentage:.1f}%)' },
        plotOptions: {
            pie: {
                innerSize: '58%',
                dataLabels: { enabled: false },
                showInLegend: true,
                borderWidth: 0
            }
        },
        legend: { align: 'right', layout: 'vertical', verticalAlign: 'middle', itemStyle: { fontSize: '11px', fontWeight: '600' } },
        series: [{
            name: @json(__('ecommerce::lang.orders')),
            data: fulfillmentPairs.length ? fulfillmentPairs : [{ name: 'None', y: 1, color: '#e2e8f0' }]
        }]
    });

    var paymentPairs = [];
    (chartData.payment_labels || []).forEach(function (label, i) {
        paymentPairs.push({ name: label, y: chartData.payment_values[i] });
    });

    Highcharts.chart('ecom_payment_chart', {
        chart: { type: 'pie', height: 240, backgroundColor: 'transparent' },
        title: { text: null },
        tooltip: { pointFormat: '<b>{point.y}</b> ({point.percentage:.1f}%)' },
        plotOptions: {
            pie: {
                innerSize: '55%',
                dataLabels: { enabled: false },
                showInLegend: true,
                borderWidth: 0
            }
        },
        legend: { align: 'center', verticalAlign: 'bottom', itemStyle: { fontSize: '11px', fontWeight: '600' } },
        series: [{
            name: @json(__('ecommerce::lang.orders')),
            data: paymentPairs.length ? paymentPairs : [{ name: 'N/A', y: 1, color: '#e2e8f0' }]
        }]
    });
})();
</script>
@endsection
