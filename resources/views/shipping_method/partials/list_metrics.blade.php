@php
    $stats = $shipping_stats ?? [];
    $total = (float) ($stats['total_methods'] ?? 0);
    $active = (float) ($stats['active_methods'] ?? 0);
    $inactive = (float) ($stats['inactive_methods'] ?? 0);
    $inUse = (float) ($stats['methods_in_use'] ?? 0);
    $salesUsing = (float) ($stats['sales_using'] ?? 0);
@endphp
<div class="product-list-dash inv-dash" id="shipping_method_list_metrics">
    <div class="inv-metric-row product-list-metrics">
        <div class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.shipping_methods')</div>
                <div class="inv-metric__value">{{ @num_format($total) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.all_shipping_methods')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('business.is_active')</div>
                <div class="inv-metric__value">{{ @num_format($active) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.shipping_methods')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.inactive')</div>
                <div class="inv-metric__value {{ $inactive > 0 ? 'is-warn' : '' }}">{{ @num_format($inactive) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.shipping_methods')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5"/><path d="M8 3H3v5"/><path d="M12 22v-8.3a4 4 0 0 0-1.172-2.872L3 3"/><path d="m15 9 6-6"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.variation_templates_used')</div>
                <div class="inv-metric__value">{{ @num_format($inUse) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.shipping_methods')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--slate">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('sale.sale')</div>
                <div class="inv-metric__value">{{ @num_format($salesUsing) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.used_on_sales')</div>
            </div>
        </div>
    </div>
</div>
