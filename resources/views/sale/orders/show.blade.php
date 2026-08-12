@extends('sale.layout')
@section('title', $order->invoice_no)
@section('header', 'Order detail')
@section('content')
<a href="{{ route('sale.orders') }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-sale mb-3 lg:mb-4">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
    Back to orders
</a>

@php
    $badge = match($order->payment_status) {
        'paid' => 'sale-badge--paid',
        'partial' => 'sale-badge--partial',
        default => 'sale-badge--due',
    };
@endphp

<div class="sale-layout-2 space-y-3 lg:space-y-0">
    <div class="sale-stack">
        <div class="sale-card">
            <div class="flex justify-between gap-3 items-start">
                <div class="flex items-start gap-3 min-w-0">
                    <span class="sale-order-row__ico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/></svg>
                    </span>
                    <div class="min-w-0">
                        <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Invoice</div>
                        <div class="text-xl lg:text-2xl font-extrabold truncate">{{ $order->invoice_no }}</div>
                        <div class="text-sm text-slate-500 mt-1 flex items-center gap-1.5">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                            {{ \Carbon\Carbon::parse($order->transaction_date)->format('M j, Y g:i A') }}
                        </div>
                    </div>
                </div>
                <span class="sale-badge {{ $badge }} shrink-0">{{ ucfirst($order->payment_status ?? 'due') }}</span>
            </div>
        </div>

        <div class="sale-card">
            <div class="sale-sec-title">
                <span class="sale-sec-title__ico">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.3 7 12 12l8.7-5M12 22V12"/></svg>
                </span>
                Items
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($order->sell_lines as $line)
                    <div class="py-3 flex justify-between gap-3 text-sm">
                        <div class="min-w-0 flex items-start gap-2">
                            <span class="mt-0.5 w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                            </span>
                            <div class="min-w-0">
                                <div class="font-semibold">{{ optional($line->product)->name }}</div>
                                <div class="text-xs text-slate-400">Qty {{ number_format($line->quantity, 2) }} × ${{ number_format($line->unit_price_inc_tax, 2) }}</div>
                            </div>
                        </div>
                        <div class="font-bold tabular-nums shrink-0">${{ number_format($line->quantity * $line->unit_price_inc_tax, 2) }}</div>
                    </div>
                @endforeach
            </div>
            <div class="border-t border-slate-100 mt-1 pt-3 flex justify-between font-extrabold text-base lg:text-lg">
                <span>Total</span>
                <span class="tabular-nums text-sale">${{ number_format($order->final_total, 2) }}</span>
            </div>
        </div>

        @if($order->additional_notes)
            <div class="sale-card">
                <div class="sale-sec-title !mb-2">
                    <span class="sale-sec-title__ico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/></svg>
                    </span>
                    Note
                </div>
                <div class="text-sm text-slate-600">{{ $order->additional_notes }}</div>
            </div>
        @endif
    </div>

    <div class="sale-stack">
        <div class="sale-card sale-sticky-panel">
            <div class="sale-sec-title">
                <span class="sale-sec-title__ico">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 20c1.5-4 14.5-4 16 0"/></svg>
                </span>
                Customer
            </div>
            <div class="font-bold text-base">{{ optional($order->contact)->supplier_business_name ?: optional($order->contact)->name }}</div>
            @if(optional($order->contact)->mobile)
                <div class="text-sm text-slate-500 mt-2 flex items-center gap-1.5">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.37 1.9.72 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.35 1.85.59 2.81.72A2 2 0 0 1 22 16.92z"/></svg>
                    {{ $order->contact->mobile }}
                </div>
            @endif
            @if(optional($order->contact)->email)
                <div class="text-sm text-slate-500 mt-1">{{ $order->contact->email }}</div>
            @endif
        </div>
        <p class="text-xs text-slate-400 px-1">This order is in admin Sales for management.</p>
    </div>
</div>
@endsection
