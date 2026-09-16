@extends('layouts.app')
@section('title', __('delivery.todays_routes'))

@section('css')
<style>
.dlv-summary {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(7.5rem, 1fr));
    gap: 0.45rem;
    margin-bottom: 1rem;
}
.dlv-summary div { background: #fff; border: 1px solid #e2e8f0; padding: 0.5rem 0.65rem; border-radius: 4px; }
.dlv-summary strong { display: block; font-size: 11px; color: #64748b; text-transform: uppercase; }
.dlv-summary span { font-size: 15px; font-weight: 700; color: #0f172a; }
.dlv-progress { height: 8px; background: #e2e8f0; border-radius: 999px; overflow: hidden; min-width: 8rem; }
.dlv-progress span { display: block; height: 100%; background: #0f766e; border-radius: 999px; }
.dlv-pill { display: inline-block; font-size: 12px; font-weight: 700; padding: 0.12rem 0.5rem; border-radius: 999px; }
.dlv-pill.is-delivered { background: #d1fae5; color: #065f46; }
.dlv-pill.is-en_route { background: #fef3c7; color: #92400e; }
.dlv-pill.is-failed { background: #fee2e2; color: #991b1b; }
.dlv-pill.is-pending { background: #e2e8f0; color: #475569; }
.dlv-muted { color: #64748b; font-size: 12px; }
</style>
@endsection

@section('content')
<section class="content-header">
    <h1>{{ __('delivery.todays_routes') }}</h1>
</section>
<section class="content">
    @if(session('status'))
        @php $st = session('status'); @endphp
        <div class="alert {{ !empty($st['success']) ? 'alert-success' : 'alert-danger' }}">{{ $st['msg'] ?? '' }}</div>
    @endif

    <div class="box box-solid">
        <div class="box-header with-border" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
            <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin:0;flex:1;">
                <select name="listFilter" class="form-control" style="width:auto;" onchange="this.form.submit()">
                    <option value="today" @selected($listFilter==='today')>Today</option>
                    <option value="pending" @selected($listFilter==='pending')>Pending</option>
                    <option value="range" @selected($listFilter==='range')>Date range</option>
                </select>
                @if ($listFilter === 'range')
                    <input type="date" name="date_from" value="{{ $date_from }}" class="form-control" style="width:auto;">
                    <span class="text-muted">to</span>
                    <input type="date" name="date_to" value="{{ $date_to }}" class="form-control" style="width:auto;">
                    <button class="btn btn-default" type="submit">{{ __('messages.filter') }}</button>
                @elseif ($listFilter === 'today')
                    <input type="date" name="date" value="{{ $date }}" class="form-control" style="width:auto;" onchange="this.form.submit()">
                @endif
            </form>
            <form method="POST" action="{{ route('deliveries.generate') }}" style="display:flex;gap:8px;align-items:center;margin:0;flex-wrap:wrap;">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <select name="driver_id" class="form-control" required style="width:auto;min-width:12rem;">
                    <option value="">{{ __('delivery.driver') }}…</option>
                    @foreach($drivers as $d)
                        <option value="{{ $d->id }}">{{ $d->user_full_name }}</option>
                    @endforeach
                </select>
                <button class="btn btn-success" type="submit">{{ __('delivery.generate_route') }}</button>
                <a href="{{ route('deliveries.assign', ['date' => $date]) }}" class="btn btn-primary">{{ __('delivery.assign_orders') }}</a>
            </form>
        </div>
        <div class="box-body">
            <div class="dlv-summary">
                <div><strong>Drivers</strong><span>{{ $routes->count() }}</span></div>
                <div><strong>Orders</strong><span>{{ $totals['orders'] }}</span></div>
                <div><strong>Delivered</strong><span>{{ $totals['delivered'] }}</span></div>
                <div><strong>Remaining</strong><span>{{ $totals['remaining'] }}</span></div>
                <div><strong>Failed</strong><span>{{ $totals['failed'] }}</span></div>
                <div><strong>Distance</strong><span>{{ $totals['miles'] }} mi</span></div>
            </div>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>{{ __('delivery.driver') }}</th>
                        <th>{{ __('delivery.status') }}</th>
                        <th style="min-width:9rem">Progress</th>
                        <th class="text-right">Orders</th>
                        <th class="text-right">Delivered</th>
                        <th class="text-right">Left</th>
                        <th class="text-right">Miles</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($routes as $route)
                        @php
                            $delivered = $route->stops->where('status', 'delivered')->count();
                            $left = $route->remainingCount();
                            $total = max(1, (int) $route->total_orders);
                            $pct = (int) round(($delivered / $total) * 100);
                            $miles = round($route->total_distance / 1609.34, 1);
                            $status = $route->status ?: 'planned';
                            $pill = match ($status) {
                                'completed' => 'delivered',
                                'started' => 'en_route',
                                'cancelled' => 'failed',
                                default => 'pending',
                            };
                        @endphp
                        <tr>
                            <td>{{ $route->route_date?->format('n/j/Y') ?: '—' }}</td>
                            <td>
                                <strong>{{ optional($route->driver)->user_full_name ?: 'Driver' }}</strong>
                                <div class="dlv-muted">{{ $route->start_name }}</div>
                            </td>
                            <td><span class="dlv-pill is-{{ $pill }}">{{ ucfirst($status) }}</span></td>
                            <td>
                                <div class="dlv-progress" title="{{ $delivered }}/{{ $route->total_orders }}">
                                    <span style="width: {{ $pct }}%"></span>
                                </div>
                                <div class="dlv-muted">{{ $pct }}%</div>
                            </td>
                            <td class="text-right">{{ $route->total_orders }}</td>
                            <td class="text-right">{{ $delivered }}</td>
                            <td class="text-right">{{ $left }}</td>
                            <td class="text-right">{{ $miles }}</td>
                            <td class="text-right" style="white-space:nowrap">
                                <a class="btn btn-sm btn-primary" href="{{ route('deliveries.routes.show', ['id' => $route->id, 'listFilter' => 'all']) }}">View Route</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">
                                @if ($listFilter === 'pending')
                                    No pending routes.
                                @elseif ($listFilter === 'today')
                                    No routes created today. Assign invoices, then Generate route.
                                @else
                                    No routes in this date range.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
