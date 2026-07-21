@extends('layouts.app')
@section('title', 'Order '.$order->invoice_no)
@section('content')
<section class="content-header"><h1>Order {{ $order->invoice_no }}</h1></section>
<section class="content">
<div class="row">
<div class="col-md-8">
<div class="box"><div class="box-body">
<p><strong>Customer:</strong> {{ optional($order->contact)->name }} ({{ optional($order->contact)->email }})</p>
<p><strong>Shipping:</strong> {{ $order->shipping_address }}</p>
<table class="table table-bordered">
<thead><tr><th>Product</th><th>Qty</th><th>Price</th></tr></thead>
<tbody>
@foreach($order->sell_lines as $line)
<tr>
<td>{{ optional($line->product)->name }}</td>
<td>{{ $line->quantity }}</td>
<td>{{ number_format($line->unit_price_inc_tax, 2) }}</td>
</tr>
@endforeach
</tbody>
</table>
<p><strong>Total:</strong> {{ number_format($order->final_total, 2) }}</p>
</div></div>
</div>
<div class="col-md-4">
<div class="box"><div class="box-body">
{!! Form::open(['url' => route('ecommerce.admin.orders.status', $order->id), 'method' => 'post']) !!}
<div class="form-group">
<label>Fulfillment status</label>
<select name="fulfillment_status" class="form-control">
@foreach(['pending','confirmed','processing','packed','shipped','delivered','cancelled','returned'] as $st)
<option value="{{ $st }}" @selected(($meta->fulfillment_status ?? '')===$st)>{{ ucfirst($st) }}</option>
@endforeach
</select>
</div>
<div class="form-group">
<label>Note</label>
<input type="text" name="note" class="form-control">
</div>
<button class="btn btn-primary">Update</button>
{!! Form::close() !!}
@if($meta)
<hr>
<p>Track: <code>{{ $meta->tracking_token }}</code></p>
<ul>
@foreach(($meta->timeline ?? []) as $step)
<li>{{ $step['label'] ?? $step['status'] }} — {{ $step['at'] ?? '' }}</li>
@endforeach
</ul>
@endif
</div></div>
</div>
</div>
</section>
@endsection
