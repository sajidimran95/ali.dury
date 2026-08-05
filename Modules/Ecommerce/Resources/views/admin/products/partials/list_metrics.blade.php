@php
    $stats = $product_stats ?? [];
    $total = (float) ($stats['total'] ?? 0);
    $posEcom = (float) ($stats['pos_ecommerce'] ?? 0);
    $ecomOnly = (float) ($stats['ecommerce_only'] ?? 0);
    $featured = (float) ($stats['featured'] ?? 0);
    $onDiscount = (float) ($stats['on_discount'] ?? 0);
@endphp
<div class="product-list-dash inv-dash" id="ecom_product_list_metrics">
    <div class="inv-metric-row product-list-metrics">
        <div class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05"/><path d="M12 22.08V12"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.online_products')</div>
                <div class="inv-metric__value">{{ @num_format($total) }}</div>
                <div class="inv-metric__hint">@lang('ecommerce::lang.products')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.pos_ecommerce')</div>
                <div class="inv-metric__value">{{ @num_format($posEcom) }}</div>
                <div class="inv-metric__hint">@lang('ecommerce::lang.sales_channel')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.ecommerce_only')</div>
                <div class="inv-metric__value">{{ @num_format($ecomOnly) }}</div>
                <div class="inv-metric__hint">@lang('ecommerce::lang.sales_channel')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.featured')</div>
                <div class="inv-metric__value">{{ @num_format($featured) }}</div>
                <div class="inv-metric__hint">@lang('ecommerce::lang.products')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--slate">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.discount_price')</div>
                <div class="inv-metric__value">{{ @num_format($onDiscount) }}</div>
                <div class="inv-metric__hint">@lang('ecommerce::lang.on_discount')</div>
            </div>
        </div>
    </div>
</div>
