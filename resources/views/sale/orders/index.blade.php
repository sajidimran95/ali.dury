@extends('sale.layout')
@section('title', 'Orders')
@section('header', 'Order list')
@section('content')
<div class="sale-page-tool">
    <span class="sale-chip">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
        {{ $orders->total() }} order(s)
    </span>
    <a href="{{ route('sale.orders.create') }}" class="sale-btn-sm lg:hidden">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
        Create
    </a>
</div>
<p class="text-xs text-slate-400 mb-3">Sales orders you created — same list as admin Sales Orders.</p>

<div class="space-y-2">
    @forelse($orders as $order)
        @php
            $badge = match($order->status) {
                'completed' => 'sale-badge--completed',
                'partial' => 'sale-badge--partial',
                default => 'sale-badge--ordered',
            };
            $badgeLabel = ucfirst($order->status ?? 'ordered');
        @endphp
        <div class="sale-order-row !items-stretch">
            <a href="{{ route('sale.orders.show', $order->id) }}" class="sale-order-row__ico shrink-0 self-center">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/></svg>
            </a>
            <a href="{{ route('sale.orders.show', $order->id) }}" class="sale-order-row__body min-w-0 flex-1 no-underline text-inherit">
                <div class="font-extrabold text-[15px] truncate">{{ $order->invoice_no }}</div>
                <div class="text-sm text-slate-500 truncate mt-0.5">
                    {{ optional($order->contact)->supplier_business_name ?: optional($order->contact)->name ?: 'Customer' }}
                </div>
                @if(optional($order->contact)->mobile)
                    <div class="text-xs text-slate-400 mt-0.5">{{ $order->contact->mobile }}</div>
                @endif
                <div class="text-xs text-slate-400 mt-1">
                    {{ \Carbon\Carbon::parse($order->transaction_date)->format('M j, Y g:i A') }}
                </div>
            </a>
            <div class="sale-order-row__meta shrink-0">
                <div class="font-extrabold tabular-nums">${{ number_format($order->final_total, 2) }}</div>
                <span class="sale-badge {{ $badge }} mt-1">{{ $badgeLabel }}</span>
                <div class="sale-order-actions">
                    <a href="{{ route('sale.orders.show', $order->id) }}" class="sale-act sale-act--view" title="View">View</a>
                    <a href="{{ route('sale.orders.invoice', $order->id) }}" target="_blank" class="sale-act sale-act--dl" title="Invoice">Invoice</a>
                    <form method="POST" action="{{ route('sale.orders.destroy', $order->id) }}" class="inline" onsubmit="return confirm('Delete order {{ $order->invoice_no }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="sale-act sale-act--del" title="Delete">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="sale-card sale-empty">
            <div class="sale-empty__ico">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
            </div>
            <p class="text-slate-500 text-sm mb-4">No orders yet.</p>
            <a href="{{ route('sale.orders.create') }}" class="sale-btn inline-block w-auto px-6">Create first order</a>
        </div>
    @endforelse
</div>

@if($orders->hasPages())
    <div class="mt-4">
        {{ $orders->onEachSide(1)->links('sale.partials.pagination') }}
    </div>
@endif
@endsection
