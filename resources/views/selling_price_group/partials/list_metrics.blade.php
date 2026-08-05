@php
    $stats = $spg_stats ?? [];
    $total = (float) ($stats['total_groups'] ?? 0);
    $active = (float) ($stats['active_groups'] ?? 0);
    $inactive = (float) ($stats['inactive_groups'] ?? 0);
    $pricedRows = (float) ($stats['priced_rows'] ?? 0);
    $productsWithPrices = (float) ($stats['products_with_prices'] ?? 0);
@endphp
<div class="product-list-dash inv-dash" id="spg_list_metrics">
    <div class="inv-metric-row product-list-metrics">
        <div class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.selling_price_group')</div>
                <div class="inv-metric__value">{{ @num_format($total) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.all_selling_price_group')</div>
            </div>
        </div>

        <div class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('business.is_active')</div>
                <div class="inv-metric__value">{{ @num_format($active) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.selling_price_group')</div>
            </div>
        </div>

        <div class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.inactive')</div>
                <div class="inv-metric__value {{ $inactive > 0 ? 'is-warn' : '' }}">{{ @num_format($inactive) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.selling_price_group')</div>
            </div>
        </div>

        <div class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.group_prices')</div>
                <div class="inv-metric__value">{{ @num_format($pricedRows) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.price_entries')</div>
            </div>
        </div>

        <div class="inv-metric inv-metric--slate">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05"/><path d="M12 22.08V12"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('sale.products')</div>
                <div class="inv-metric__value">{{ @num_format($productsWithPrices) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.with_group_prices')</div>
            </div>
        </div>
    </div>
</div>
