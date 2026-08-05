@php
    $stats = $agent_stats ?? [];
    $total = (float) ($stats['total_agents'] ?? 0);
    $avgPercent = (float) ($stats['avg_percent'] ?? 0);
    $withCommission = (float) ($stats['with_commission'] ?? 0);
    $salesWithAgent = (float) ($stats['sales_with_agent'] ?? 0);
    $agentsUsed = (float) ($stats['agents_used'] ?? 0);
    $salesThisMonth = (float) ($stats['sales_this_month'] ?? 0);
@endphp
<div class="product-list-dash inv-dash" id="commission_agent_list_metrics">
    <div class="inv-metric-row product-list-metrics">
        <div class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.sales_commission_agents')</div>
                <div class="inv-metric__value">{{ @num_format($total) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.total_agents')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.avg_commission')</div>
                <div class="inv-metric__value">{{ @num_format($avgPercent) }}%</div>
                <div class="inv-metric__hint">@lang('lang_v1.cmmsn_percent')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.with_commission')</div>
                <div class="inv-metric__value">{{ @num_format($withCommission) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.cmmsn_percent') &gt; 0</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.agents_in_sales')</div>
                <div class="inv-metric__value">{{ @num_format($agentsUsed) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.used_on_sales')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--slate">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.sales_with_agent')</div>
                <div class="inv-metric__value">{{ @num_format($salesWithAgent) }}</div>
                <div class="inv-metric__hint">{{ @num_format($salesThisMonth) }} @lang('home.this_month')</div>
            </div>
        </div>
    </div>
</div>
