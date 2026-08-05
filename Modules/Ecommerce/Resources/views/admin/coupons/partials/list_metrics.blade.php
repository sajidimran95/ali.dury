@php
    $stats = $coupon_stats ?? [];
    $total = (float) ($stats['total'] ?? 0);
    $active = (float) ($stats['active'] ?? 0);
    $inactive = (float) ($stats['inactive'] ?? 0);
    $usedTimes = (float) ($stats['used_times'] ?? 0);
    $usedCoupons = (float) ($stats['used_coupons'] ?? 0);
    $neverUsed = (float) ($stats['never_used'] ?? 0);
    $topCode = $stats['top_code'] ?? null;
    $topApplied = (float) ($stats['top_applied'] ?? 0);
@endphp
<div class="product-list-dash inv-dash" id="ecom_coupon_list_metrics">
    <div class="inv-metric-row product-list-metrics">
        <div class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.times_applied')</div>
                <div class="inv-metric__value">{{ @num_format($usedTimes) }}</div>
                <div class="inv-metric__hint">@lang('ecommerce::lang.total_redemptions')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.coupons_applied')</div>
                <div class="inv-metric__value">{{ @num_format($usedCoupons) }}</div>
                <div class="inv-metric__hint">@lang('ecommerce::lang.used_at_least_once')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.top_coupon')</div>
                <div class="inv-metric__value" style="font-size:clamp(14px,1.4vw,18px);word-break:break-all;">
                    {{ $topCode ?: '—' }}
                </div>
                <div class="inv-metric__hint">
                    @if($topCode)
                        {{ @num_format($topApplied) }} @lang('ecommerce::lang.times_applied')
                    @else
                        @lang('ecommerce::lang.no_redemptions_yet')
                    @endif
                </div>
            </div>
        </div>
        <div class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.never_used')</div>
                <div class="inv-metric__value {{ $neverUsed > 0 ? 'is-warn' : '' }}">{{ @num_format($neverUsed) }}</div>
                <div class="inv-metric__hint">@lang('ecommerce::lang.coupons')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--slate">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('ecommerce::lang.coupons')</div>
                <div class="inv-metric__value">{{ @num_format($total) }}</div>
                <div class="inv-metric__hint">
                    {{ @num_format($active) }} @lang('ecommerce::lang.active')
                    · {{ @num_format($inactive) }} @lang('lang_v1.inactive')
                </div>
            </div>
        </div>
    </div>
</div>
