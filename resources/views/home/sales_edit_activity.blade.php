@extends('layouts.app')
@section('title', __('home.edit_activity'))

@section('content')
<div class="japs-dashboard-page japs-sales-dash" style="padding: 20px 24px 40px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;">
        <div>
            <h1 class="japs-dashboard-welcome" style="margin:0;font-size:22px;">{{ __('home.edit_activity') }}</h1>
            <p style="margin:6px 0 0;color:#64748b;font-size:13px;">All invoice and sales-order activity. Click the eye to open full details.</p>
        </div>
        <a href="{{ $dashboard_url }}" class="japs-dash-period-chip" style="text-decoration:none;">← {{ __('home.sales_fulfillment_analytics') }}</a>
    </div>

    <div class="japs-dash-chart-card" style="min-height:0;padding:0;overflow:auto;">
        <table class="table table-striped" style="margin:0;font-size:13px;">
            <thead>
                <tr>
                    <th>Date &amp; time</th>
                    <th>Type</th>
                    <th>Invoice / SO</th>
                    <th>Product</th>
                    <th>Change</th>
                    <th>Edited by</th>
                    <th style="width:60px;">View</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $act)
                    <tr>
                        <td>{{ $act->when }}</td>
                        <td>{{ $act->kind }}</td>
                        <td><b>{{ $act->invoice_no }}</b></td>
                        <td>{{ $act->product }}</td>
                        <td>{{ $act->change }}</td>
                        <td>{{ $act->editor }}</td>
                        <td>
                            <a href="{{ $act->view_url }}" title="{{ __('messages.view') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8 11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">{{ __('lang_v1.no_data') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
