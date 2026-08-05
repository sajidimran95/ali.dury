@php
    $stats = $adjustment_stats ?? [];
    $total = (float) ($stats['total_adjustments'] ?? 0);
    $normal = (float) ($stats['normal'] ?? 0);
    $abnormal = (float) ($stats['abnormal'] ?? 0);
    $thisMonth = (float) ($stats['this_month'] ?? 0);
    $totalAmount = (float) ($stats['total_amount'] ?? 0);
    $recovered = (float) ($stats['total_recovered'] ?? 0);
@endphp
<div class="product-list-dash inv-dash" id="stock_adjustment_list_metrics">
    <div class="inv-metric-row product-list-metrics">
        <div class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v18"/><path d="M5 8h14"/><path d="M5 16h14"/><path d="M9 3h6"/><path d="M9 21h6"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('stock_adjustment.stock_adjustments')</div>
                <div class="inv-metric__value">{{ @num_format($total) }}</div>
                <div class="inv-metric__hint">@lang('stock_adjustment.all_stock_adjustments')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('stock_adjustment.normal')</div>
                <div class="inv-metric__value">{{ @num_format($normal) }}</div>
                <div class="inv-metric__hint">@lang('stock_adjustment.adjustment_type')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('stock_adjustment.abnormal')</div>
                <div class="inv-metric__value {{ $abnormal > 0 ? 'is-warn' : '' }}">{{ @num_format($abnormal) }}</div>
                <div class="inv-metric__hint">@lang('stock_adjustment.adjustment_type')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('home.this_month')</div>
                <div class="inv-metric__value">{{ @num_format($thisMonth) }}</div>
                <div class="inv-metric__hint">@lang('stock_adjustment.stock_adjustments')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--slate">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('stock_adjustment.total_amount')</div>
                <div class="inv-metric__value">
                    @can('view_purchase_price')
                        <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $totalAmount }}">{{ $totalAmount }}</span>
                    @else
                        —
                    @endcan
                </div>
                <div class="inv-metric__hint">
                    @lang('stock_adjustment.total_amount_recovered'):
                    @can('view_purchase_price')
                        <strong>
                            <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $recovered }}">{{ $recovered }}</span>
                        </strong>
                    @else
                        —
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
