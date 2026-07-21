@extends('layouts.app')
@section('title', __('ecommerce::lang.dashboard'))
@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold">@lang('ecommerce::lang.dashboard')</h1>
</section>
<section class="content">
    @if(!$settings->is_enabled)
        <div class="alert alert-warning">Ecommerce is disabled. <a href="{{ route('ecommerce.admin.settings') }}">Enable it in settings</a>.</div>
    @endif
    <div class="row">
        @foreach([
            ['today_orders', 'today_orders'],
            ['total_sales', 'total_sales'],
            ['pending_orders', 'pending'],
            ['completed_orders', 'completed'],
            ['cancelled_orders', 'cancelled'],
        ] as [$label, $key])
            <div class="col-md-3 col-sm-6">
                <div class="info-box">
                    <div class="info-box-content" style="margin-left:0;padding:15px;">
                        <span class="info-box-text">@lang('ecommerce::lang.'.$label)</span>
                        <span class="info-box-number">
                            @if($key === 'total_sales')
                                {{ number_format($stats[$key] ?? 0, 2) }}
                            @else
                                {{ $stats[$key] ?? 0 }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="box box-solid">
        <div class="box-header"><h3 class="box-title">Recent orders</h3></div>
        <div class="box-body table-responsive">
            <table class="table table-bordered">
                <thead><tr><th>Invoice</th><th>Customer</th><th>Total</th><th>Date</th></tr></thead>
                <tbody>
                @foreach($recent_orders as $order)
                    <tr>
                        <td><a href="{{ route('ecommerce.admin.orders.show', $order->id) }}">{{ $order->invoice_no }}</a></td>
                        <td>{{ optional($order->contact)->name }}</td>
                        <td>{{ number_format($order->final_total, 2) }}</td>
                        <td>{{ $order->transaction_date }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
