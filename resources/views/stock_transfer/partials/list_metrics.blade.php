@php
    $stats = $transfer_stats ?? [];
    $total = (float) ($stats['total_transfers'] ?? 0);
    $pending = (float) ($stats['pending'] ?? 0);
    $inTransit = (float) ($stats['in_transit'] ?? 0);
    $completed = (float) ($stats['completed'] ?? 0);
    $thisMonth = (float) ($stats['this_month'] ?? 0);
    $totalAmount = (float) ($stats['total_amount'] ?? 0);
@endphp
<div class="product-list-dash inv-dash" id="stock_transfer_list_metrics">
    <div class="inv-metric-row product-list-metrics">
        <div class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.stock_transfers')</div>
                <div class="inv-metric__value">{{ @num_format($total) }}</div>
                <div class="inv-metric__hint">
                    @can('view_purchase_price')
                        <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $totalAmount }}">{{ $totalAmount }}</span>
                        ·
                    @endcan
                    @lang('lang_v1.all_stock_transfers')
                </div>
            </div>
        </div>
        <div class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.pending')</div>
                <div class="inv-metric__value {{ $pending > 0 ? 'is-warn' : '' }}">{{ @num_format($pending) }}</div>
                <div class="inv-metric__hint">@lang('sale.status')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.in_transit')</div>
                <div class="inv-metric__value">{{ @num_format($inTransit) }}</div>
                <div class="inv-metric__hint">@lang('sale.status')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('restaurant.completed')</div>
                <div class="inv-metric__value">{{ @num_format($completed) }}</div>
                <div class="inv-metric__hint">@lang('sale.status')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--slate">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('home.this_month')</div>
                <div class="inv-metric__value">{{ @num_format($thisMonth) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.stock_transfers')</div>
            </div>
        </div>
    </div>
</div>
