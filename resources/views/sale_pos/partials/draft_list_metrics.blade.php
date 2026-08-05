@php
    $s = $draft_stats ?? [];
    $total = (float) ($s['total'] ?? 0);
    $direct = (float) ($s['direct'] ?? 0);
    $pos = (float) ($s['pos'] ?? 0);
    $today = (float) ($s['today'] ?? 0);
    $thisMonth = (float) ($s['thisMonth'] ?? 0);
    $totalAmount = (float) ($s['totalAmount'] ?? 0);
@endphp
<div class="product-list-dash inv-dash" id="draft_list_metrics">
    <div class="inv-metric-row product-list-metrics">
        <div class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('sale.drafts')</div>
                <div class="inv-metric__value">{{ @num_format($total) }}</div>
                <div class="inv-metric__hint">
                    <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $totalAmount }}">{{ $totalAmount }}</span>
                </div>
            </div>
        </div>
        <div class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.invoices')</div>
                <div class="inv-metric__value">{{ @num_format($direct) }}</div>
                <div class="inv-metric__hint">@lang('sale.drafts')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('sale.pos_sale')</div>
                <div class="inv-metric__value">{{ @num_format($pos) }}</div>
                <div class="inv-metric__hint">@lang('sale.drafts')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('home.today')</div>
                <div class="inv-metric__value">{{ @num_format($today) }}</div>
                <div class="inv-metric__hint">@lang('sale.drafts')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--slate">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('home.this_month')</div>
                <div class="inv-metric__value">{{ @num_format($thisMonth) }}</div>
                <div class="inv-metric__hint">@lang('sale.drafts')</div>
            </div>
        </div>
    </div>
</div>
