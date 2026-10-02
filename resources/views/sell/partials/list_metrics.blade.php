@php
    $s = $sell_stats ?? [];
    $total = (float) ($s['total'] ?? 0);
    $direct = (float) ($s['direct'] ?? 0);
    $pos = (float) ($s['pos'] ?? 0);
    $paid = (float) ($s['paid'] ?? 0);
    $due = (float) ($s['due'] ?? 0);
    $partial = (float) ($s['partial'] ?? 0);
    $today = (float) ($s['today'] ?? 0);
    $thisMonth = (float) ($s['thisMonth'] ?? 0);
    $totalAmount = (float) ($s['totalAmount'] ?? 0);
@endphp
<div class="product-list-dash inv-dash" id="sell_list_metrics">
    <div class="inv-metric-row product-list-metrics">
        <div class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.invoices')</div>
                <div class="inv-metric__value">{{ @num_format($total) }}</div>
                <div class="inv-metric__hint">
                    <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $totalAmount }}">{{ $totalAmount }}</span>
                </div>
            </div>
        </div>
        <div class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.paid')</div>
                <div class="inv-metric__value">{{ @num_format($paid) }}</div>
                <div class="inv-metric__hint">@lang('sale.payment_status')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.due')</div>
                <div class="inv-metric__value {{ $due > 0 ? 'is-warn' : '' }}">{{ @num_format($due) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.partial'): {{ @num_format($partial) }}</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('sale.pos_sale')</div>
                <div class="inv-metric__value">{{ @num_format($pos) }}</div>
                <div class="inv-metric__hint">@lang('sale.add_sale'): {{ @num_format($direct) }}</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--slate">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('home.this_month')</div>
                <div class="inv-metric__value">{{ @num_format($thisMonth) }}</div>
                <div class="inv-metric__hint">@lang('home.today'): {{ @num_format($today) }}</div>
            </div>
        </div>
    </div>
</div>
