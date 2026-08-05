@php
    $stats = $unit_stats ?? [];
    $total = (float) ($stats['total_units'] ?? 0);
    $base = (float) ($stats['base_units'] ?? 0);
    $sub = (float) ($stats['sub_units'] ?? 0);
    $allowDec = (float) ($stats['allow_decimal'] ?? 0);
    $products = (float) ($stats['products_using'] ?? 0);
@endphp
<div class="product-list-dash inv-dash" id="unit_list_metrics">
    <div class="inv-metric-row product-list-metrics">
        <div class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h7v7H3z"/><path d="M14 3h7v7h-7z"/><path d="M14 14h7v7h-7z"/><path d="M3 14h7v7H3z"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('unit.units')</div>
                <div class="inv-metric__value">{{ @num_format($total) }}</div>
                <div class="inv-metric__hint">@lang('unit.all_your_units')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.base_unit')</div>
                <div class="inv-metric__value">{{ @num_format($base) }}</div>
                <div class="inv-metric__hint">@lang('unit.units')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5"/><path d="M8 3H3v5"/><path d="M12 22v-8.3a4 4 0 0 0-1.172-2.872L3 3"/><path d="m15 9 6-6"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.sub_units')</div>
                <div class="inv-metric__value">{{ @num_format($sub) }}</div>
                <div class="inv-metric__hint">@lang('unit.units')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20v-6"/><path d="M6 20V10"/><path d="M18 20V4"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('unit.allow_decimal')</div>
                <div class="inv-metric__value">{{ @num_format($allowDec) }}</div>
                <div class="inv-metric__hint">@lang('unit.units')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--slate">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05"/><path d="M12 22.08V12"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('sale.products')</div>
                <div class="inv-metric__value">{{ @num_format($products) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.using_unit')</div>
            </div>
        </div>
    </div>
</div>
