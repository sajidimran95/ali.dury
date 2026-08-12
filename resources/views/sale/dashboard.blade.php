@extends('sale.layout')
@section('title', 'Dashboard')
@section('header', 'Dashboard')
@section('content')
@php
    $displayName = trim((auth()->user()->first_name ?? '').' '.(auth()->user()->last_name ?? ''));
    if ($displayName === '') {
        $displayName = auth()->user()->username;
    }
@endphp

<div class="sale-dash">
    <div class="sale-dash-greeting mb-4 flex items-center gap-3">
        <div class="sale-order-row__ico !w-12 !h-12">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9.5z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-sm text-slate-500 mb-0.5">Welcome back</p>
            <h2 class="text-xl lg:text-2xl font-extrabold text-ink truncate">{{ $displayName }}</h2>
        </div>
    </div>

    <div class="sale-dash-stats">
        <div class="sale-stat sale-stat--primary">
            <div class="flex items-center gap-2 mb-1">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                <div class="sale-stat__label">Today sales</div>
            </div>
            <div class="sale-stat__value">${{ number_format($stats['today_total'], 2) }}</div>
            <div class="sale-stat__sub">{{ $stats['today_orders'] }} order(s)</div>
        </div>
        <div class="sale-stat">
            <div class="flex items-center gap-2 mb-1">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-sale"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
                <div class="sale-stat__label">This month</div>
            </div>
            <div class="sale-stat__value">${{ number_format($stats['month_total'], 2) }}</div>
            <div class="sale-stat__sub">{{ $stats['month_orders'] }} order(s)</div>
        </div>
        <div class="sale-stat">
            <div class="flex items-center gap-2 mb-1">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-sale"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" stroke-linecap="round"/></svg>
                <div class="sale-stat__label">All orders</div>
            </div>
            <div class="sale-stat__value">{{ $stats['total_orders'] }}</div>
            <div class="sale-stat__sub">Lifetime</div>
        </div>
        <div class="sale-stat">
            <div class="flex items-center gap-2 mb-1">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-sale"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18"/></svg>
                <div class="sale-stat__label">Payment</div>
            </div>
            <div class="sale-stat__value text-base lg:text-lg">{{ $stats['paid_orders'] }} paid</div>
            <div class="sale-stat__sub">{{ $stats['due_orders'] }} due / partial</div>
        </div>
    </div>

    <div class="sale-dash-actions mb-5">
        <a href="{{ route('sale.orders.create') }}" class="sale-action-tile sale-action-tile--main">
            <span class="sale-action-tile__icon">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            </span>
            <span class="sale-action-tile__text">
                <strong>Create order</strong>
                <small>New sale for customer</small>
            </span>
        </a>
        <a href="{{ route('sale.orders') }}" class="sale-action-tile">
            <span class="sale-action-tile__icon sale-action-tile__icon--soft">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
            </span>
            <span class="sale-action-tile__text">
                <strong>Order list</strong>
                <small>View all</small>
            </span>
        </a>
    </div>

    <div class="sale-sec-title !mb-3">
        <span class="sale-sec-title__ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/></svg>
        </span>
        Recent orders
        <a href="{{ route('sale.orders') }}" class="ml-auto text-xs font-bold text-sale no-underline">See all</a>
    </div>

    <div class="space-y-2">
        @forelse($recent as $order)
            @php
                $badge = match($order->payment_status) {
                    'paid' => 'sale-badge--paid',
                    'partial' => 'sale-badge--partial',
                    default => 'sale-badge--due',
                };
            @endphp
            <a href="{{ route('sale.orders.show', $order->id) }}" class="sale-order-row">
                <span class="sale-order-row__ico">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/></svg>
                </span>
                <div class="sale-order-row__body">
                    <div class="font-extrabold text-[15px] truncate">{{ $order->invoice_no }}</div>
                    <div class="text-sm text-slate-500 truncate mt-0.5">
                        {{ optional($order->contact)->supplier_business_name ?: optional($order->contact)->name ?: 'Customer' }}
                    </div>
                    <div class="text-xs text-slate-400 mt-1">
                        {{ \Carbon\Carbon::parse($order->transaction_date)->format('M j, Y g:i A') }}
                    </div>
                </div>
                <div class="sale-order-row__meta">
                    <div class="font-extrabold tabular-nums">${{ number_format($order->final_total, 2) }}</div>
                    <span class="sale-badge {{ $badge }} mt-1">{{ ucfirst($order->payment_status ?? 'due') }}</span>
                </div>
            </a>
        @empty
            <div class="sale-card sale-empty">
                <div class="sale-empty__ico">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/></svg>
                </div>
                <p class="text-slate-500 text-sm mb-4">No orders yet.</p>
                <a href="{{ route('sale.orders.create') }}" class="sale-btn inline-block w-auto px-6">Create first order</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
