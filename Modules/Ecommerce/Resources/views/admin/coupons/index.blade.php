@extends('layouts.app')
@section('title', __('ecommerce::lang.coupons'))
@section('content')
<section class="content-header"><h1>@lang('ecommerce::lang.coupons')</h1></section>
<section class="content">
<div class="box"><div class="box-body">
{!! Form::open(['url'=>route('ecommerce.admin.coupons.store'),'method'=>'post','class'=>'form-inline']) !!}
<input name="code" class="form-control" placeholder="CODE" required>
<select name="type" class="form-control"><option value="fixed">Fixed</option><option value="percentage">Percentage</option></select>
<input name="value" class="form-control" placeholder="Value" required>
<input name="min_order_amount" class="form-control" placeholder="Min order">
<input name="usage_limit" class="form-control" placeholder="Usage limit">
<input type="date" name="expires_at" class="form-control">
<button class="btn btn-primary">Add</button>
{!! Form::close() !!}
<table class="table table-bordered" style="margin-top:20px">
<thead><tr><th>Code</th><th>Type</th><th>Value</th><th>Used</th><th></th></tr></thead>
<tbody>
@foreach($coupons as $coupon)
<tr>
<td>{{ $coupon->code }}</td><td>{{ $coupon->type }}</td><td>{{ $coupon->value }}</td><td>{{ $coupon->used_count }}</td>
<td><form method="post" action="{{ route('ecommerce.admin.coupons.destroy',$coupon->id) }}">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Delete</button></form></td>
</tr>
@endforeach
</tbody></table>
</div></div>
</section>
@endsection
