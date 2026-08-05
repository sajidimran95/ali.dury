@php
    $stats = $brand_stats ?? [];
    $total = (float) ($stats['total_brands'] ?? 0);
    $inUse = (float) ($stats['brands_in_use'] ?? 0);
    $unused = (float) ($stats['unused_brands'] ?? 0);
    $withBrand = (float) ($stats['products_with_brand'] ?? 0);
    $noBrand = (float) ($stats['products_no_brand'] ?? 0);
@endphp
<div class="product-list-dash inv-dash" id="brand_list_metrics">
    <div class="inv-metric-row product-list-metrics">
        <div class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('brand.brands')</div>
                <div class="inv-metric__value">{{ @num_format($total) }}</div>
                <div class="inv-metric__hint">@lang('brand.all_your_brands')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.variation_templates_used')</div>
                <div class="inv-metric__value">{{ @num_format($inUse) }}</div>
                <div class="inv-metric__hint">@lang('brand.brands')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.variation_templates_unused')</div>
                <div class="inv-metric__value {{ $unused > 0 ? 'is-warn' : '' }}">{{ @num_format($unused) }}</div>
                <div class="inv-metric__hint">@lang('brand.brands')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05"/><path d="M12 22.08V12"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('sale.products')</div>
                <div class="inv-metric__value">{{ @num_format($withBrand) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.with_brand')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--slate">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('sale.products')</div>
                <div class="inv-metric__value">{{ @num_format($noBrand) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.no_brand')</div>
            </div>
        </div>
    </div>
</div>
