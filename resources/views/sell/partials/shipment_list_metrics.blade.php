@php
    $s = $shipment_stats ?? [];
    $total = (float) ($s['total'] ?? 0);
    $ordered = (float) ($s['ordered'] ?? 0);
    $packed = (float) ($s['packed'] ?? 0);
    $shipped = (float) ($s['shipped'] ?? 0);
    $delivered = (float) ($s['delivered'] ?? 0);
    $today = (float) ($s['today'] ?? 0);
    $totalAmount = (float) ($s['totalAmount'] ?? 0);
@endphp
<div class="product-list-dash inv-dash" id="shipment_list_metrics">
    <div class="inv-metric-row product-list-metrics">
        <div class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.shipments')</div>
                <div class="inv-metric__value">{{ @num_format($total) }}</div>
                <div class="inv-metric__hint">
                    <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $totalAmount }}">{{ $totalAmount }}</span>
                    · @lang('home.today'): {{ @num_format($today) }}
                </div>
            </div>
        </div>
        <div class="inv-metric inv-metric--slate">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.ordered')</div>
                <div class="inv-metric__value">{{ @num_format($ordered) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.shipping_status')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.packed')</div>
                <div class="inv-metric__value">{{ @num_format($packed) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.shipping_status')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 18H3c-.6 0-1-.4-1-1V7c0-.6.4-1 1-1h10c.6 0 1 .4 1 1v11"/><path d="M14 9h4l4 4v4c0 .6-.4 1-1 1h-2"/><circle cx="7" cy="18" r="2"/><path d="M15 18H9"/><circle cx="17" cy="18" r="2"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.shipped')</div>
                <div class="inv-metric__value">{{ @num_format($shipped) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.shipping_status')</div>
            </div>
        </div>
        <div class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.delivered')</div>
                <div class="inv-metric__value">{{ @num_format($delivered) }}</div>
                <div class="inv-metric__hint">@lang('lang_v1.shipping_status')</div>
            </div>
        </div>
    </div>
</div>
