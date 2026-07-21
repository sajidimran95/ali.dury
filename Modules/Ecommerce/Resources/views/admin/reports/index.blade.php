@extends('layouts.app')
@section('title', __('ecommerce::lang.reports'))
@section('content')
<section class="content-header"><h1>@lang('ecommerce::lang.reports')</h1></section>
<section class="content">
<div class="row">
<div class="col-md-4"><div class="info-box"><div class="info-box-content" style="margin-left:0;padding:15px"><span class="info-box-text">Orders</span><span class="info-box-number">{{ $summary['orders'] }}</span></div></div></div>
<div class="col-md-4"><div class="info-box"><div class="info-box-content" style="margin-left:0;padding:15px"><span class="info-box-text">Revenue</span><span class="info-box-number">{{ number_format($summary['revenue'] ?? 0, 2) }}</span></div></div></div>
<div class="col-md-4"><div class="info-box"><div class="info-box-content" style="margin-left:0;padding:15px"><span class="info-box-text">Avg Order</span><span class="info-box-number">{{ number_format($summary['avg_order'] ?? 0, 2) }}</span></div></div></div>
</div>
<div class="box"><div class="box-header"><h3 class="box-title">Top selling products</h3></div>
<div class="box-body table-responsive">
<table class="table table-bordered"><thead><tr><th>Product</th><th>Qty</th><th>Revenue</th></tr></thead>
<tbody>
@foreach($top_products as $row)
<tr><td>{{ $row->name }}</td><td>{{ $row->qty }}</td><td>{{ number_format($row->revenue, 2) }}</td></tr>
@endforeach
</tbody></table>
</div></div>
</section>
@endsection
