@php
    $stats = $variation_stats ?? [];
    $totalTemplates = (float) ($stats['total_templates'] ?? 0);
    $totalValues = (float) ($stats['total_values'] ?? 0);
    $inUse = (float) ($stats['templates_in_use'] ?? 0);
    $unused = (float) ($stats['unused_templates'] ?? 0);
    $avgValues = $stats['avg_values'] ?? 0;
    $productsUsing = (float) ($stats['products_using'] ?? 0);
@endphp
<div class="product-list-dash inv-dash" id="variation_list_metrics">
    <div class="inv-metric-row product-list-metrics">
        <div class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h10"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('product.variations')</div>
                <div class="inv-metric__value">{{ @num_format($totalTemplates) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.all_variations')</div>
            </div>
        </div>

        <div class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.values')</div>
                <div class="inv-metric__value">{{ @num_format($totalValues) }}</div>
                <div class="inv-metric__hint">
                    {{ is_numeric($avgValues) ? $avgValues : @num_format($avgValues) }} @lang('lang_v1.variation_avg_values')
                </div>
            </div>
        </div>

        <div class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.variation_templates_used')</div>
                <div class="inv-metric__value">{{ @num_format($inUse) }}</div>
                <div class="inv-metric__hint">{{ @num_format($productsUsing) }} @lang('sale.products')</div>
            </div>
        </div>

        <div class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.variation_templates_unused')</div>
                <div class="inv-metric__value {{ $unused > 0 ? 'is-warn' : '' }}">{{ @num_format($unused) }}</div>
                <div class="inv-metric__hint">@lang('product.variations')</div>
            </div>
        </div>

        <div class="inv-metric inv-metric--slate">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05"/><path d="M12 22.08V12"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('sale.products')</div>
                <div class="inv-metric__value">{{ @num_format($productsUsing) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.products_using_variations')</div>
            </div>
        </div>
    </div>
</div>
