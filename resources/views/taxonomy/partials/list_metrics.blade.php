@php
    $stats = $taxonomy_stats ?? [];
    $total = (float) ($stats['total'] ?? 0);
    $parents = (float) ($stats['parents'] ?? 0);
    $children = (float) ($stats['children'] ?? 0);
    $inCat = (float) ($stats['products_in_cat'] ?? 0);
    $noCat = (float) ($stats['products_no_cat'] ?? 0);
    $isProduct = ($stats['category_type'] ?? '') === 'product';
@endphp
<div class="product-list-dash inv-dash" id="taxonomy_list_metrics">
    <div class="inv-metric-row product-list-metrics">
        <div class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('category.categories')</div>
                <div class="inv-metric__value">{{ @num_format($total) }}</div>
                <div class="inv-metric__hint">@lang('category.manage_your_categories')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.parent_categories')</div>
                <div class="inv-metric__value">{{ @num_format($parents) }}</div>
                <div class="inv-metric__hint">@lang('category.category')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h10"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.sub_categories')</div>
                <div class="inv-metric__value">{{ @num_format($children) }}</div>
                <div class="inv-metric__hint">@lang('category.category')</div>
            </div>
        </div>
        @if($isProduct)
        <div class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05"/><path d="M12 22.08V12"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('sale.products')</div>
                <div class="inv-metric__value">{{ @num_format($inCat) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.in_category')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--slate">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('sale.products')</div>
                <div class="inv-metric__value {{ $noCat > 0 ? 'is-warn' : '' }}">{{ @num_format($noCat) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.uncategorized')</div>
            </div>
        </div>
        @endif
    </div>
</div>
