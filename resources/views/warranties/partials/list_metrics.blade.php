@php
    $stats = $warranty_stats ?? [];
    $total = (float) ($stats['total_warranties'] ?? 0);
    $days = (float) ($stats['duration_days'] ?? 0);
    $months = (float) ($stats['duration_months'] ?? 0);
    $years = (float) ($stats['duration_years'] ?? 0);
    $products = (float) ($stats['products_with_warranty'] ?? 0);
@endphp
<div class="product-list-dash inv-dash" id="warranty_list_metrics">
    <div class="inv-metric-row product-list-metrics">
        <div class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.warranties')</div>
                <div class="inv-metric__value">{{ @num_format($total) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.all_warranties')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.days')</div>
                <div class="inv-metric__value">{{ @num_format($days) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.duration')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.months')</div>
                <div class="inv-metric__value">{{ @num_format($months) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.duration')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20v-6"/><path d="M6 20V10"/><path d="M18 20V4"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.years')</div>
                <div class="inv-metric__value">{{ @num_format($years) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.duration')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--slate">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05"/><path d="M12 22.08V12"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('sale.products')</div>
                <div class="inv-metric__value">{{ @num_format($products) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.with_warranty')</div>
            </div>
        </div>
    </div>
</div>
