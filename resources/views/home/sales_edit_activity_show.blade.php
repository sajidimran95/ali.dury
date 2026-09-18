@extends('layouts.app')
@section('title', __('home.edit_activity'))

@section('content')
<div class="japs-dashboard-page japs-sales-dash" style="padding: 20px 24px 40px;max-width:920px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;">
        <h1 class="japs-dashboard-welcome" style="margin:0;font-size:22px;">Activity details</h1>
        <div style="display:flex;gap:8px;">
            <a href="{{ $list_url }}" class="japs-dash-period-chip" style="text-decoration:none;">← All activity</a>
            <a href="{{ $dashboard_url }}" class="japs-dash-period-chip" style="text-decoration:none;">Dashboard</a>
        </div>
    </div>

    <div class="japs-dash-chart-card" style="min-height:0;padding:18px 20px;">
        <table class="table" style="margin:0 0 16px;font-size:13px;">
            <tr><th style="width:180px;">Date &amp; time</th><td>{{ $activity->created_at ? $activity->created_at->format('M j, Y g:i A') : '—' }}</td></tr>
            <tr><th>Action</th><td>{{ $activity->description }}</td></tr>
            <tr><th>Document</th><td>{{ $kind }} <b>{{ $invoice_no }}</b></td></tr>
            <tr><th>Edited by</th><td>{{ $editor }}</td></tr>
            @if (!empty($doc_url))
                <tr><th>Open document</th><td><a href="{{ $doc_url }}">View invoice / sales order</a></td></tr>
            @endif
        </table>

        <h3 style="font-size:15px;margin:8px 0 10px;">Products</h3>
        <table class="table table-bordered" style="margin:0 0 16px;font-size:13px;">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Change</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lines as $line)
                    <tr>
                        <td>{{ is_array($line) ? ($line['product'] ?? '—') : $line }}</td>
                        <td>{{ is_array($line) ? ($line['change'] ?? 'edited') : 'removed' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2">No product lines stored for this edit.</td></tr>
                @endforelse
            </tbody>
        </table>

        @if (!empty($props['removed_items']) && is_array($props['removed_items']))
            <h3 style="font-size:15px;margin:8px 0 10px;">Removed items</h3>
            <ul>
                @foreach ($props['removed_items'] as $name)
                    <li>{{ $name }}</li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
