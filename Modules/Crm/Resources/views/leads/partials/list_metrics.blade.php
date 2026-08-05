@php
    $stats = $lead_stats ?? [];
    $total = (float) ($stats['total_leads'] ?? 0);
    $withStage = (float) ($stats['with_stage'] ?? 0);
    $noStage = (float) ($stats['no_stage'] ?? 0);
    $assigned = (float) ($stats['assigned'] ?? 0);
    $unassigned = (float) ($stats['unassigned'] ?? 0);
    $thisMonth = (float) ($stats['this_month'] ?? 0);
@endphp
<div class="product-list-dash inv-dash" id="lead_list_metrics">
    <div class="inv-metric-row product-list-metrics">
        <div class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('crm::lang.total_leads')</div>
                <div class="inv-metric__value">{{ @num_format($total) }}</div>
                <div class="inv-metric__hint">@lang('crm::lang.leads')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('crm::lang.with_stage')</div>
                <div class="inv-metric__value">{{ @num_format($withStage) }}</div>
                <div class="inv-metric__hint">@lang('crm::lang.life_stage')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('crm::lang.no_stage')</div>
                <div class="inv-metric__value {{ $noStage > 0 ? 'is-warn' : '' }}">{{ @num_format($noStage) }}</div>
                <div class="inv-metric__hint">@lang('crm::lang.leads')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('crm::lang.assigned_to')</div>
                <div class="inv-metric__value">{{ @num_format($assigned) }}</div>
                <div class="inv-metric__hint">{{ @num_format($unassigned) }} @lang('crm::lang.unassigned')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--slate">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('home.this_month')</div>
                <div class="inv-metric__value">{{ @num_format($thisMonth) }}</div>
                <div class="inv-metric__hint">@lang('crm::lang.this_month_leads')</div>
            </div>
        </div>
    </div>
</div>
