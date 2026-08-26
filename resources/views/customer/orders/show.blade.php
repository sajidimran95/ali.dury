@extends('customer.layout')
@section('title', 'View Order')
@section('content')
<div class="flex items-start justify-between gap-3 mb-3">
    <div>
        <h1 class="text-2xl font-extrabold">View Order</h1>
        <div class="text-sm text-slate-500 font-semibold">{{ $initials }}</div>
        <div class="text-xs text-slate-400 font-semibold mt-0.5">#{{ $order->invoice_no }} · {{ \Carbon\Carbon::parse($order->transaction_date)->format('M d, Y') }}</div>
    </div>
    <div class="flex items-center gap-2 flex-shrink-0">
        @if($order->type === 'sell' && $order->status === 'final')
            <a href="{{ route('customer.documents.invoice', $order->id) }}"
               class="vo-icon" title="Invoice">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/><path d="M14 3v5h5"/></svg>
            </a>
        @endif
        <a href="{{ route('customer.orders') }}" class="vo-icon" title="Back to orders">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
        </a>
    </div>
</div>

<div class="grid grid-cols-3 gap-2 mb-4" id="fulfillGroup">
    <div class="fulfill-opt {{ $fulfill === 'pickup' ? 'is-on' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3"/><path d="M5 20c1.5-4 4-6 7-6s5.5 2 7 6"/></svg>
        PICKUP
    </div>
    <div class="fulfill-opt {{ $fulfill === 'delivery' ? 'is-on' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 8h11v10H4z"/><path d="M15 11h3l2 3v4h-5v-7z"/><circle cx="7.5" cy="19" r="1.5"/><circle cx="17.5" cy="19" r="1.5"/></svg>
        DELIVERY
    </div>
    <div class="fulfill-opt {{ $fulfill === 'shipping' ? 'is-on' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7z"/></svg>
        SHIPPING
    </div>
</div>

<div class="flex items-end justify-between mb-3">
    <div>
        <div class="font-extrabold text-lg">Added Items</div>
        <div class="text-sm text-slate-500 font-semibold">Total : <span class="text-slate-800 font-extrabold tabular-nums">${{ number_format($itemsTotal, 2) }}</span></div>
    </div>
    <div class="text-right text-xs text-slate-400 font-semibold">
        Order total<br>
        <span class="text-base text-red-600 font-extrabold tabular-nums">${{ number_format((float) $order->final_total, 2) }}</span>
    </div>
</div>

<div class="space-y-3 mb-6">
@forelse($lines as $line)
    <div class="ca-card !p-3">
        <div class="flex gap-3">
            <div class="w-20 h-20 rounded-xl bg-slate-100 border border-slate-100 overflow-hidden flex-shrink-0 flex items-center justify-center">
                <img src="{{ $line['image'] }}" alt="" class="w-full h-full object-cover"
                     onerror="this.style.display='none'; this.parentNode.innerHTML='<span class=&quot;text-slate-300 text-[10px]&quot;>No image</span>';">
            </div>
            <div class="min-w-0 flex-1">
                <div class="font-extrabold text-sm leading-snug uppercase">{{ $line['name'] }}</div>

                @if($line['has_discount'])
                    <div class="mt-1 flex items-center gap-1 text-red-600 text-xs font-bold">
                        <span class="inline-flex w-4 h-4 items-center justify-center rounded bg-red-50 text-[10px]">%</span>
                        ${{ number_format($line['discount'] > 0 ? $line['discount'] : max(0, ($line['unit_before'] - $line['unit_final']) * $line['qty']), 2) }} Off
                    </div>
                @endif

                <div class="mt-1.5 space-y-0.5 text-[11px] text-slate-500 font-semibold leading-relaxed">
                    <div>SKU ({{ $line['sku'] }})</div>
                    <div>Size ({{ $line['size'] }})</div>
                    <div>Retail UPC ({{ $line['upc'] }})</div>
                </div>

                <div class="mt-2 flex items-center gap-1.5 text-xs font-bold text-slate-700">
                    <span class="inline-flex w-5 h-5 items-center justify-center rounded bg-red-50 text-red-600 text-[10px]">▣</span>
                    {{ rtrim(rtrim(number_format($line['qty'], 2), '0'), '.') }} items
                </div>
                <div class="text-xs text-slate-500 font-semibold mt-0.5">Quantity: {{ rtrim(rtrim(number_format($line['qty'], 2), '0'), '.') }}</div>

                <div class="mt-2 flex items-center gap-2 flex-wrap">
                    <span class="inline-flex w-5 h-5 items-center justify-center rounded-full bg-red-600 text-white text-[10px] font-bold">$</span>
                    @if($line['has_discount'] && $line['unit_before'] > $line['unit_final'])
                        <span class="text-slate-400 line-through text-sm tabular-nums">${{ number_format($line['unit_before'], 2) }}</span>
                    @endif
                    <span class="font-extrabold text-base tabular-nums">${{ number_format($line['unit_final'], 2) }}</span>
                    <span class="text-xs text-slate-400 font-semibold ml-auto tabular-nums">Line ${{ number_format($line['line_total'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="ca-card text-center text-slate-400 py-10 text-sm">No items on this order</div>
@endforelse
</div>

@if($order->type === 'sell' && $order->status === 'final')
    <a href="{{ route('customer.documents.invoice', $order->id) }}" class="ca-btn mb-3">View Invoice</a>
@endif
<a href="{{ route('customer.orders') }}" class="block text-center text-sm font-bold text-slate-500 py-2">← Back to My Orders</a>
@endsection

@push('styles')
<style>
.fulfill-opt {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 6px;
    font-size: 11px;
    font-weight: 800;
    color: #64748b;
    background: #fff;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    pointer-events: none;
    opacity: .55;
}
.fulfill-opt.is-on {
    background: #dc2626;
    border-color: #dc2626;
    color: #fff;
    opacity: 1;
}
.vo-icon {
    width: 40px;
    height: 40px;
    border-radius: 999px;
    border: 1.5px solid #dc2626;
    color: #dc2626;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    text-decoration: none;
}
</style>
@endpush
