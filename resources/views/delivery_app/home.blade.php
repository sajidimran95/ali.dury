@extends('delivery_app.layout')
@section('title', 'Dashboard')
@section('nav_active', 'home')
@section('content')
@php
    $first = explode(' ', $user->user_full_name ?: $user->username)[0];
    $initial = mb_strtoupper(mb_substr($first, 0, 1));
    $total = (int) ($route?->total_orders ?? 0);
    $pct = $total > 0 ? (int) round(($deliveredToday / $total) * 100) : 0;
@endphp
<div class="dash-hero">
    <div class="dash-hero-row">
        <div>
            <p class="dash-date">{{ \Illuminate\Support\Carbon::parse($date)->format('l, M j') }}</p>
            <h1 class="dash-hello">Hi, {{ $first }}</h1>
        </div>
        <div style="display:flex;align-items:center;gap:.5rem;">
            <div class="dash-avatar">{{ $initial }}</div>
            <form method="POST" action="{{ route('delivery.app.logout') }}">@csrf<button class="dash-logout" type="submit">Log out</button></form>
        </div>
    </div>
    <div class="dash-progress">
        <div style="display:flex;justify-content:space-between;align-items:baseline;">
            <strong>Today’s progress</strong>
            <span>{{ $pct }}%</span>
        </div>
        <div class="bar"><span style="width: {{ $pct }}%"></span></div>
        <p class="muted" style="color:rgba(255,255,255,.75);margin:.45rem 0 0;font-size:12px">
            {{ $deliveredToday }} delivered · {{ $left }} left · {{ $failedToday }} failed
        </p>
    </div>
</div>

<div class="wrap" style="padding-top:0;">
    <div class="dash-kpis">
        <div class="dash-kpi"><b><i class="dot" style="background:#0ea5e9"></i>Today’s stops</b><em>{{ $total }}</em></div>
        <div class="dash-kpi"><b><i class="dot" style="background:#ffd000"></i>Pending</b><em>{{ $left }}</em></div>
        <div class="dash-kpi"><b><i class="dot" style="background:#22c55e"></i>Delivered today</b><em>{{ $deliveredToday }}</em></div>
        <div class="dash-kpi"><b><i class="dot" style="background:#64748b"></i>All time</b><em>{{ $allTimeDelivered }}</em></div>
    </div>

    @if (! $route)
        <div class="dash-next empty-dash" style="margin-top:1rem;">
            <div class="ico">🚚</div>
            <p style="margin:0 0 .3rem;font-weight:800">No route today</p>
            <p class="muted" style="margin:0">Dispatch will assign invoices to your route.</p>
        </div>
    @else
        <div class="dash-next">
            <span class="tag">{{ ucfirst($route->status) }}</span>
            @if ($suggested)
                <h2>Next stop {{ $suggested->stop_no }}</h2>
                <p style="margin:0;font-weight:700">{{ $suggested->ship_to_name }}</p>
                <p class="muted" style="margin:.2rem 0 .8rem">Invoice #{{ $suggested->order?->invoice_no }}</p>
                <div class="row" style="margin-top:0">
                    <a class="btn btn-p" style="flex:1" href="{{ route('delivery.app.route', ['stop' => $suggested->id]) }}">Open route</a>
                    <a class="btn btn-w" style="flex:1" href="{{ $suggested->navigateUrl() }}" target="_blank" rel="noopener">Navigate</a>
                </div>
            @else
                <h2>Route complete</h2>
                <p class="muted">All stops on this route are finished.</p>
                <a class="btn btn-w" style="width:100%;margin-top:.5rem" href="{{ route('delivery.app.history') }}">View delivered</a>
            @endif
            @if ($route->status === 'planned')
                <form method="POST" action="{{ route('delivery.app.start') }}" style="margin-top:.65rem">
                    @csrf
                    <input type="hidden" name="route_id" value="{{ $route->id }}">
                    <button class="btn btn-g" type="submit" style="width:100%">Start route</button>
                </form>
            @endif
        </div>
    @endif

    <div class="dash-actions">
        <a class="dash-act" href="{{ route('delivery.app.route') }}">
            <i style="background:#ecfeff;color:#0f766e">📍</i>
            Route map
            <span>Stops & navigate</span>
        </a>
        <a class="dash-act" href="{{ route('delivery.app.assigned') }}">
            <i style="background:#fff7ed;color:#c2410c">📦</i>
            Assigned
            <span>Open invoices</span>
        </a>
        <a class="dash-act" href="{{ route('delivery.app.all') }}">
            <i style="background:#f1f5f9;color:#334155">📋</i>
            All invoices
            <span>By date</span>
        </a>
        <a class="dash-act" href="{{ route('delivery.app.history') }}">
            <i style="background:#f0fdf4;color:#15803d">✅</i>
            Delivered
            <span>History</span>
        </a>
    </div>
</div>
@endsection
