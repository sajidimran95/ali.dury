@php
    $s = $expense_category_stats ?? [];
    $total = (float) ($s['total'] ?? 0);
    $parents = (float) ($s['parents'] ?? 0);
    $subs = (float) ($s['subs'] ?? 0);
    $withCode = (float) ($s['withCode'] ?? 0);
    $inUse = (float) ($s['inUse'] ?? 0);
@endphp
<div class="product-list-dash inv-dash" id="expense_category_list_metrics">
    <div class="inv-metric-row product-list-metrics">
        <div class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('expense.expense_categories')</div>
                <div class="inv-metric__value">{{ @num_format($total) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.all')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('expense.category_name')</div>
                <div class="inv-metric__value">{{ @num_format($parents) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.all')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h7v7H3z"/><path d="M14 3h7v7h-7z"/><path d="M14 14h7v7h-7z"/><path d="M3 14h7v7H3z"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('product.sub_category')</div>
                <div class="inv-metric__value">{{ @num_format($subs) }}</div>
                <div class="inv-metric__hint">@lang('expense.expense_categories')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('expense.category_code')</div>
                <div class="inv-metric__value">{{ @num_format($withCode) }}</div>
                <div class="inv-metric__hint">@lang('expense.expense_categories')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--slate">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('expense.expenses')</div>
                <div class="inv-metric__value">{{ @num_format($inUse) }}</div>
                <div class="inv-metric__hint">@lang('expense.expense_category')</div>
            </div>
        </div>
    </div>
</div>
