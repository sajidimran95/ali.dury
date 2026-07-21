@extends('layouts.app')
@section('title', __('ecommerce::lang.orders'))
@section('content')
<section class="content-header"><h1>@lang('ecommerce::lang.orders')</h1></section>
<section class="content">
<div class="box"><div class="box-body table-responsive">
<table class="table table-bordered">
<thead><tr><th>Invoice</th><th>Customer</th><th>Total</th><th>Payment</th><th>Fulfillment</th><th>Date</th></tr></thead>
<tbody>
@foreach($orders as $order)
@php $meta = $metas[$order->id] ?? null; @endphp
<tr>
<td><a href="{{ route('ecommerce.admin.orders.show', $order->id) }}">{{ $order->invoice_no }}</a></td>
<td>{{ optional($order->contact)->name }}</td>
<td>{{ number_format($order->final_total, 2) }}</td>
<td>{{ $order->payment_status }}</td>
<td>{{ $meta->fulfillment_status ?? '-' }}</td>
<td>{{ $order->transaction_date }}</td>
</tr>
@endforeach
</tbody>
</table>
{{ $orders->links() }}
</div></div>
</section>
@endsection
