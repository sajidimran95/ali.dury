@extends('sale.layout')
@section('title', $listTitle)
@section('header', $listTitle)
@section('header_right')
<a href="{{ route('sale.orders', array_filter(['period' => $period ?? null, 'month' => ($period ?? '') === 'monthly' ? ($monthKey ?? null) : null])) }}" class="sale-rpt-more-btn" aria-label="Back to reports" style="display:flex;align-items:center;justify-content:center;text-decoration:none;color:#0b1220">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
</a>
@endsection
@section('content')
@php
    $section = $section ?? '';
    $period = $period ?? 'monthly';
    $monthKey = $monthKey ?? now()->format('Y-m');
    $dr = $dayReport ?? [];
@endphp

@if($section === 'day')
<div class="sale-card mb-3">
    <div class="font-extrabold text-center mb-2">{{ $dr['date'] ?? now()->format('l, M j, Y') }}</div>
    <div class="grid grid-cols-2 gap-2 text-sm">
        <div class="rounded-xl bg-slate-50 p-3">
            <div class="text-xs text-slate-400 font-bold">Start of day</div>
            <div class="font-extrabold text-lg">{{ $dr['start'] ?? '—' }}</div>
        </div>
        <div class="rounded-xl bg-slate-50 p-3">
            <div class="text-xs text-slate-400 font-bold">End of day</div>
            <div class="font-extrabold text-lg">{{ $dr['end'] ?? '—' }}</div>
        </div>
        <div class="rounded-xl bg-slate-50 p-3">
            <div class="text-xs text-slate-400 font-bold">Orders</div>
            <div class="font-extrabold">{{ (int) ($dr['orders'] ?? 0) }} · ${{ number_format((float) ($dr['orders_total'] ?? 0), 2) }}</div>
        </div>
        <div class="rounded-xl bg-slate-50 p-3">
            <div class="text-xs text-slate-400 font-bold">Payments</div>
            <div class="font-extrabold text-sale">${{ number_format((float) ($dr['cash'] ?? 0), 2) }}</div>
        </div>
        <div class="rounded-xl bg-slate-50 p-3">
            <div class="text-xs text-slate-400 font-bold">Credit</div>
            <div class="font-extrabold text-rose-600">${{ number_format((float) ($dr['credit'] ?? 0), 2) }}</div>
        </div>
        <div class="rounded-xl bg-slate-50 p-3">
            <div class="text-xs text-slate-400 font-bold">Visits</div>
            <div class="font-extrabold">{{ (int) ($dr['visits'] ?? 0) }}</div>
        </div>
    </div>
</div>
@endif

<form method="GET" action="{{ route('sale.orders') }}" class="sale-card !p-3 mb-3 space-y-2" id="saleOrderSearchForm">
    <input type="hidden" name="section" value="{{ $section }}">
    <input type="hidden" name="period" value="{{ $period }}">
    @if($period === 'monthly')
        <input type="hidden" name="month" value="{{ $monthKey }}">
    @endif
    @if(!empty($dayKey))
        <input type="hidden" name="day" value="{{ $dayKey }}">
    @endif
    <input type="search" name="q" id="saleOrderSearch" value="{{ $q ?? '' }}" class="sale-input" placeholder="Search customer / order / invoice no" autocomplete="off">
</form>

<div id="saleOrderResults">
<div class="space-y-2">
@if($section === 'visits')
    @forelse($visitCustomers as $vc)
        <a href="{{ route('sale.orders', array_filter(['section' => 'orders', 'contact_id' => $vc->id, 'period' => $period, 'month' => $period === 'monthly' ? $monthKey : null])) }}" class="sale-order-row no-underline text-inherit">
            <span class="sale-order-row__ico">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5"/><path d="M4 20c1.6-3.6 14.4-3.6 16 0"/></svg>
            </span>
            <div class="sale-order-row__body min-w-0 flex-1">
                <div class="font-extrabold text-[15px] truncate">{{ $vc->supplier_business_name ?: $vc->name }}</div>
                <div class="text-xs text-slate-400 mt-0.5">{{ (int) ($vc->visit_orders ?? 0) }} order(s)</div>
            </div>
            <div class="sale-order-row__meta">
                <div class="font-extrabold tabular-nums">${{ number_format((float) ($vc->visit_total ?? 0), 2) }}</div>
            </div>
        </a>
    @empty
        <div class="sale-card sale-empty">
            <p class="text-slate-500 text-sm">No visits in this period.</p>
        </div>
    @endforelse
    @if($visitCustomers->hasPages())
        <div class="mt-4">{{ $visitCustomers->onEachSide(1)->links('sale.partials.pagination') }}</div>
    @endif
@else
    @forelse($orders as $order)
        @php
            $displayStatus = $order->sale_status ?? 'sale';
            if ($displayStatus === 'invoiced') {
                $badge = 'sale-badge--completed';
                $badgeLabel = 'Invoiced';
            } elseif ($displayStatus === 'estimate') {
                $badge = 'sale-badge--draft';
                $badgeLabel = 'Estimate';
            } else {
                $badge = 'sale-badge--ordered';
                $badgeLabel = $section === 'payments' ? 'Payment' : 'Sale';
            }
            $no = $section === 'invoices' && !empty($order->converted_invoice_no) ? $order->converted_invoice_no : $order->invoice_no;
        @endphp
        <div class="sale-order-row !items-stretch">
            <a href="{{ route('sale.orders.show', $order->id) }}" class="sale-order-row__ico shrink-0 self-center">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/></svg>
            </a>
            <a href="{{ route('sale.orders.show', $order->id) }}" class="sale-order-row__body min-w-0 flex-1 no-underline text-inherit">
                <div class="font-extrabold text-[15px] truncate">{{ $no }}</div>
                @if($section === 'invoices' && !empty($order->invoice_no) && $no !== $order->invoice_no)
                    <div class="text-xs text-slate-400">SO {{ $order->invoice_no }}</div>
                @endif
                <div class="text-sm text-slate-500 truncate mt-0.5">
                    {{ optional($order->contact)->supplier_business_name ?: optional($order->contact)->name ?: 'Customer' }}
                </div>
                <div class="text-xs text-slate-400 mt-1">
                    {{ \Carbon\Carbon::parse($order->transaction_date)->format('M j, Y g:i A') }}
                </div>
            </a>
            <div class="sale-order-row__meta shrink-0">
                <div class="font-extrabold tabular-nums">${{ number_format($order->sale_display_total ?? $order->final_total, 2) }}</div>
                <span class="sale-badge {{ $badge }} mt-1">{{ $badgeLabel }}</span>
            </div>
        </div>
    @empty
        <div class="sale-card sale-empty">
            <p class="text-slate-500 text-sm">No records in this period.</p>
            <a href="{{ route('sale.orders') }}" class="sale-btn inline-block w-auto px-6 mt-3">Back to Reports</a>
        </div>
    @endforelse
    @if($orders->hasPages())
        <div class="mt-4">{{ $orders->onEachSide(1)->links('sale.partials.pagination') }}</div>
    @endif
@endif
</div>
</div>
@endsection
