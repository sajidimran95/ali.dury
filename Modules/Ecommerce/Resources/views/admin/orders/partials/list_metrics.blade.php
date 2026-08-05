@php
    $stats = $order_stats ?? [];
    $total = (float) ($stats['total'] ?? 0);
    $today = (float) ($stats['today'] ?? 0);
    $pending = (float) ($stats['pending'] ?? 0);
    $processing = (float) ($stats['processing'] ?? 0);
    $shipped = (float) ($stats['shipped'] ?? 0);
    $completed = (float) ($stats['completed'] ?? 0);
    $thisMonth = (float) ($stats['this_month'] ?? 0);
    $salesTotal = (float) ($stats['sales_total'] ?? 0);
@endphp
<div class="product-list-dash inv-dash" id="ecom_order_list_metrics">
    <div class="inv-metric-row product-list-metrics">
        <div class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.total_orders')</div>
                <div class="inv-metric__value">{{ @num_format($total) }}</div>
                <div class="inv-metric__hint">
                    <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $salesTotal }}">{{ $salesTotal }}</span>
                    · @lang('ecommerce::lang.total_sales')
                </div>
            </div>
        </div>
        <div class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.today_orders')</div>
                <div class="inv-metric__value">{{ @num_format($today) }}</div>
                <div class="inv-metric__hint">{{ @num_format($thisMonth) }} @lang('home.this_month')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.pending_orders')</div>
                <div class="inv-metric__value {{ $pending > 0 ? 'is-warn' : '' }}">{{ @num_format($pending) }}</div>
                <div class="inv-metric__hint">@lang('ecommerce::lang.fulfillment')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.processing')</div>
                <div class="inv-metric__value">{{ @num_format($processing) }}</div>
                <div class="inv-metric__hint">@lang('ecommerce::lang.shipped'): {{ @num_format($shipped) }}</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--slate">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.completed_orders')</div>
                <div class="inv-metric__value">{{ @num_format($completed) }}</div>
                <div class="inv-metric__hint">@lang('ecommerce::lang.fulfillment')</div>
            </div>
        </div>
    </div>
</div>
