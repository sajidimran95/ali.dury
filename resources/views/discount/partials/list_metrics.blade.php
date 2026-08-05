@php
    $s = $discount_stats ?? [];
    $total = (float) ($s['total'] ?? 0);
    $active = (float) ($s['active'] ?? 0);
    $inactive = (float) ($s['inactive'] ?? 0);
    $running = (float) ($s['running'] ?? 0);
    $expired = (float) ($s['expired'] ?? 0);
@endphp
<div class="product-list-dash inv-dash" id="discount_list_metrics">
    <div class="inv-metric-row product-list-metrics">
        <div class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="9" r="2"/><path d="M21 15l-6-6L5 19"/><path d="M15 8h6v6"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('sale.discount')</div>
                <div class="inv-metric__value">{{ @num_format($total) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.all')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.is_active')</div>
                <div class="inv-metric__value">{{ @num_format($active) }}</div>
                <div class="inv-metric__hint">@lang('sale.discount')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--slate">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.inactive')</div>
                <div class="inv-metric__value">{{ @num_format($inactive) }}</div>
                <div class="inv-metric__hint">@lang('sale.discount')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.current')</div>
                <div class="inv-metric__value">{{ @num_format($running) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.is_active') · @lang('lang_v1.starts_at')–@lang('lang_v1.ends_at')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('report.expired')</div>
                <div class="inv-metric__value {{ $expired > 0 ? 'is-warn' : '' }}">{{ @num_format($expired) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.ends_at')</div>
            </div>
        </div>
    </div>
</div>
