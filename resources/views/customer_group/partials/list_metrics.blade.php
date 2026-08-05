@php
    $stats = $customer_group_stats ?? [];
    $total = (float) ($stats['total_groups'] ?? 0);
    $percentage = (float) ($stats['percentage_type'] ?? 0);
    $priceGroup = (float) ($stats['price_group_type'] ?? 0);
    $inUse = (float) ($stats['groups_in_use'] ?? 0);
    $customersIn = (float) ($stats['customers_in_group'] ?? 0);
    $customersNo = (float) ($stats['customers_no_group'] ?? 0);
@endphp
<div class="product-list-dash inv-dash" id="customer_group_list_metrics">
    <div class="inv-metric-row product-list-metrics">
        <div class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.customer_groups')</div>
                <div class="inv-metric__value">{{ @num_format($total) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.all_your_customer_groups')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.percentage')</div>
                <div class="inv-metric__value">{{ @num_format($percentage) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.price_calculation_type')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.selling_price_group')</div>
                <div class="inv-metric__value">{{ @num_format($priceGroup) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.price_calculation_type')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.customers')</div>
                <div class="inv-metric__value">{{ @num_format($customersIn) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.in_customer_group') · {{ @num_format($inUse) }} @lang('lang_v1.variation_templates_used')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--slate">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.customers')</div>
                <div class="inv-metric__value">{{ @num_format($customersNo) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.no_customer_group')</div>
            </div>
        </div>
    </div>
</div>
