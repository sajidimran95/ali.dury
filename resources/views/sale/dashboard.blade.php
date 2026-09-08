@extends('sale.layout')
@section('title', 'Main')
@section('header', config('app.name', 'Sales'))
@section('content')
@php
    $todayOrders = (int) ($stats['today_orders'] ?? 0);
    $todayTotal = (float) ($stats['today_total'] ?? 0);
    $monthOrders = max(1, (int) ($stats['month_orders'] ?? 0));
    $orderPct = min(100, round(($todayOrders / $monthOrders) * 100));
    $payPct = min(100, $todayTotal > 0 ? 100 : 0);
@endphp

<div class="sale-home-welcome">
    <h1>Welcome</h1>
    <div class="font-extrabold text-2xl mt-2">{{ $userName }}</div>
</div>

<div class="flex justify-between items-end mb-2">
    <div class="font-extrabold">Today</div>
    <div class="text-xs text-slate-400">{{ now()->format('l j F Y') }}</div>
</div>
<div class="sale-home-today mb-5">
    <div class="sale-card text-center">
        <div class="text-2xl font-extrabold text-violet-600">{{ $todayOrders }} <span class="text-sm font-bold text-slate-500">Orders</span></div>
        <div class="sale-gauge mt-2" style="--p: {{ $orderPct }}"></div>
        <div class="text-xs text-amber-500 mt-1">{{ $orderPct }}% Goal</div>
        <div class="text-xs text-slate-400 mt-1">{{ (int) ($stats['due_orders'] ?? 0) }} Pending</div>
    </div>
    <div class="sale-card text-center">
        <div class="text-2xl font-extrabold text-sale">{{ number_format((float) ($stats['today_cash_total'] ?? $todayTotal), 0) }} <span class="text-sm font-bold text-slate-500">Cash</span></div>
        <div class="sale-gauge mt-2" style="--p: {{ $payPct }}; background: conic-gradient(#e53935 calc(var(--p, 0) * 1%), #e5e7eb 0);"></div>
        <div class="text-xs text-sale mt-1">{{ $payPct }}% Goal</div>
        <div class="text-xs text-rose-500 mt-1 font-semibold">Credit {{ (int) ($stats['today_credit_orders'] ?? 0) }} · ${{ number_format((float) ($stats['today_credit_total'] ?? 0), 0) }}</div>
    </div>
</div>

<div class="flex justify-between items-center mb-2">
    <div class="font-extrabold">New Products</div>
    <a href="{{ route('sale.products') }}" class="sale-viewmore">View More &gt;</a>
</div>
<div class="sale-prod-grid mb-5">
    @forelse($newProducts as $p)
        <a class="sale-prod-tile" href="{{ $p['variation_id'] ? route('sale.orders.create', ['add' => $p['variation_id']]) : route('sale.products') }}">
            @if(!empty($p['has_image']))
                <img src="{{ $p['image'] }}" alt="">
            @else
                <div class="sale-prod-tile__ph">No image</div>
            @endif
            <div class="sale-prod-tile__name">@if(!empty($p['sku']))[{{ $p['sku'] }}] @endif{{ $p['name'] }}</div>
            <div class="sale-prod-tile__price">${{ number_format((float) $p['price'], 2) }}</div>
            <div class="sale-prod-tile__stock">{{ !empty($p['in_stock']) ? 'In Stock' : 'Out Stock' }}</div>
    @empty
        <div class="text-sm text-slate-400 col-span-2">No products</div>
    @endforelse
</div>

<div class="flex justify-between items-center mb-2">
    <div class="font-extrabold">Top Sellers</div>
    <a href="{{ route('sale.products') }}" class="sale-viewmore">View More &gt;</a>
</div>
<div class="sale-prod-grid">
    @forelse($topProducts as $p)
        <a class="sale-prod-tile" href="{{ !empty($p->variation_id) ? route('sale.orders.create', ['add' => $p->variation_id]) : route('sale.products') }}">
            @if(!empty($p->has_image))
                <img src="{{ $p->image_url }}" alt="">
            @else
                <div class="sale-prod-tile__ph">No image</div>
            @endif
            <div class="sale-prod-tile__name">@if(!empty($p->sku))[{{ $p->sku }}] @endif{{ $p->name }}</div>
            <div class="sale-prod-tile__price">${{ number_format((float) $p->price, 2) }}</div>
            <div class="sale-prod-tile__stock">{{ !empty($p->in_stock) ? 'In Stock' : 'Out Stock' }}</div>
        </a>
    @empty
        <div class="text-sm text-slate-400 col-span-2">No sales yet</div>
    @endforelse
</div>
@endsection
