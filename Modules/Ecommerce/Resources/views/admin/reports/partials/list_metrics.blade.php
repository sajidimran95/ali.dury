@php
    $s = $summary ?? [];
    $orders = (float) ($s['orders'] ?? 0);
    $revenue = (float) ($s['revenue'] ?? 0);
    $avgOrder = (float) ($s['avg_order'] ?? 0);
    $todayOrders = (float) ($s['today_orders'] ?? 0);
    $todaySales = (float) ($s['today_sales'] ?? 0);
    $monthOrders = (float) ($s['this_month_orders'] ?? 0);
    $monthSales = (float) ($s['this_month_sales'] ?? 0);
@endphp
<div class="product-list-dash inv-dash" id="ecom_report_list_metrics">
    <div class="inv-metric-row product-list-metrics">
        <div class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.total_orders')</div>
                <div class="inv-metric__value">{{ @num_format($orders) }}</div>
                <div class="inv-metric__hint">@lang('ecommerce::lang.orders')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.revenue')</div>
                <div class="inv-metric__value is-sum">
                    <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $revenue }}">{{ $revenue }}</span>
                </div>
                <div class="inv-metric__hint">@lang('ecommerce::lang.total_sales')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.avg_order')</div>
                <div class="inv-metric__value is-sum">
                    <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $avgOrder }}">{{ $avgOrder }}</span>
                </div>
                <div class="inv-metric__hint">@lang('ecommerce::lang.orders')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.today_orders')</div>
                <div class="inv-metric__value">{{ @num_format($todayOrders) }}</div>
                <div class="inv-metric__hint">
                    <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $todaySales }}">{{ $todaySales }}</span>
                    · @lang('ecommerce::lang.today_sales')
                </div>
            </div>
        </div>
        <div class="inv-metric inv-metric--slate">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('home.this_month')</div>
                <div class="inv-metric__value">{{ @num_format($monthOrders) }}</div>
                <div class="inv-metric__hint">
                    <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $monthSales }}">{{ $monthSales }}</span>
                    · @lang('ecommerce::lang.total_sales')
                </div>
            </div>
        </div>
    </div>
</div>
