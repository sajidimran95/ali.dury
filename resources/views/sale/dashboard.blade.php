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
    <div class="sale-dash-greeting mb-4 flex items-start justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <div class="sale-order-row__ico !w-12 !h-12">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9.5z"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm text-slate-500 mb-0.5">Your sales only</p>
                <h2 class="text-xl lg:text-2xl font-extrabold text-ink truncate">{{ $displayName }}</h2>
            </div>
        </div>
        <a href="{{ route('sale.account') }}" class="sale-loc-pill shrink-0" title="Change location in Account">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
            <span class="truncate max-w-[140px] lg:max-w-[220px]">{{ $current_location_name }}</span>
        </a>
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
            <div class="sale-stat__value text-base lg:text-lg">{{ $stats['paid_orders'] }} completed</div>
            <div class="sale-stat__sub">{{ $stats['due_orders'] }} ordered / partial</div>
        </div>
    </div>

    <div class="sale-dash-actions mb-5">
        <a href="{{ route('sale.orders.create') }}" class="sale-action-tile sale-action-tile--main">
            <span class="sale-action-tile__icon">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.4a2 2 0 0 0 2-1.5L21 8H7"/></svg>
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

    {{-- New products --}}
    <div class="sale-sec-title !mb-3">
        <span class="sale-sec-title__ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.3 7 12 12l8.7-5M12 22V12"/></svg>
        </span>
        New products
        <a href="{{ route('sale.products') }}" class="ml-auto text-xs font-bold text-sale no-underline">View all</a>
    </div>
    <div class="sale-prod-grid sale-prod-grid--row mb-5">
        @forelse($newProducts as $p)
            <a href="{{ $p['variation_id'] ? route('sale.orders.create', ['add' => $p['variation_id']]) : route('sale.products') }}" class="sale-prod-tile">
                <div class="sale-prod-tile__media {{ empty($p['has_image']) ? 'is-placeholder' : '' }}">
                    @if(!empty($p['has_image']))
                        <img src="{{ $p['image'] }}" alt="" loading="lazy">
                    @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10.5" r="1.5"/><path d="m21 15-4.5-4.5L9 18"/></svg>
                    @endif
                </div>
                <div class="sale-prod-tile__name">{{ $p['name'] }}</div>
                <div class="sale-prod-tile__price">${{ number_format($p['price'], 2) }}</div>
            </a>
        @empty
            <div class="sale-card col-span-full text-sm text-slate-400 text-center py-6">No products yet</div>
        @endforelse
    </div>

    {{-- Top products --}}
    <div class="sale-sec-title !mb-3">
        <span class="sale-sec-title__ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 3v18M8 8l4-4 4 4M7 14h10"/></svg>
        </span>
        Top products
        <a href="{{ route('sale.products') }}" class="ml-auto text-xs font-bold text-sale no-underline">View all</a>
    </div>
    <div class="sale-prod-grid sale-prod-grid--row mb-5">
        @forelse($topProducts as $p)
            <a href="{{ $p->variation_id ? route('sale.orders.create', ['add' => $p->variation_id]) : route('sale.products') }}" class="sale-prod-tile">
                <div class="sale-prod-tile__media {{ empty($p->has_image) ? 'is-placeholder' : '' }}">
                    @if(!empty($p->has_image))
                        <img src="{{ $p->image_url }}" alt="" loading="lazy">
                    @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10.5" r="1.5"/><path d="m21 15-4.5-4.5L9 18"/></svg>
                    @endif
                </div>
                <div class="sale-prod-tile__name">{{ $p->name }}</div>
                <div class="sale-prod-tile__meta">Sold {{ number_format((float) $p->qty_sold, 0) }}</div>
                <div class="sale-prod-tile__price">${{ number_format((float) $p->price, 2) }}</div>
            </a>
        @empty
            <div class="sale-card col-span-full text-sm text-slate-400 text-center py-6">No sales yet — top products appear after orders</div>
        @endforelse
    </div>

    <div class="sale-sec-title !mb-3">
        <span class="sale-sec-title__ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/></svg>
        </span>
        Recent orders
        <a href="{{ route('sale.orders') }}" class="ml-auto text-xs font-bold text-sale no-underline">View all</a>
    </div>

    <div class="space-y-2">
        @forelse($recent as $order)
            @php
                $badge = match($order->status) {
                    'completed' => 'sale-badge--completed',
                    'partial' => 'sale-badge--partial',
                    default => 'sale-badge--ordered',
                };
                $badgeLabel = ucfirst($order->status ?? 'ordered');
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
                    <span class="sale-badge {{ $badge }} mt-1">{{ $badgeLabel }}</span>
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
