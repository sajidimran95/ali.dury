@extends('layouts.app')
@section('title', __('ecommerce::lang.shipping_tax'))
@section('content')
<section class="content-header"><h1>@lang('ecommerce::lang.shipping_tax')</h1></section>
<section class="content">
<div class="row">
<div class="col-md-6">
<div class="box"><div class="box-header"><h3 class="box-title">Shipping rules</h3></div><div class="box-body">
{!! Form::open(['url'=>route('ecommerce.admin.shipping.store'),'method'=>'post']) !!}
<input name="name" class="form-control" placeholder="Name" required style="margin-bottom:8px">
<select name="type" class="form-control" style="margin-bottom:8px"><option value="flat">Flat</option><option value="free">Free</option><option value="weight">Weight</option><option value="state">State</option></select>
<input name="amount" class="form-control" placeholder="Amount" style="margin-bottom:8px">
<input name="min_order_amount" class="form-control" placeholder="Min order" style="margin-bottom:8px">
<input name="states" class="form-control" placeholder="States CSV e.g. CA,NY" style="margin-bottom:8px">
<button class="btn btn-primary">Add shipping</button>
{!! Form::close() !!}
<ul class="list-group" style="margin-top:15px">
@foreach($shipping_rules as $rule)
<li class="list-group-item">{{ $rule->name }} — {{ $rule->type }} — {{ $rule->amount }}</li>
@endforeach
</ul>
</div></div>
</div>
<div class="col-md-6">
<div class="box"><div class="box-header"><h3 class="box-title">Tax rules (USA)</h3></div><div class="box-body">
{!! Form::open(['url'=>route('ecommerce.admin.tax.store'),'method'=>'post']) !!}
<input name="name" class="form-control" placeholder="Name" required style="margin-bottom:8px">
<select name="level" class="form-control" style="margin-bottom:8px"><option value="state">State</option><option value="county">County</option><option value="city">City</option></select>
<input name="state" class="form-control" placeholder="State code" style="margin-bottom:8px">
<input name="city" class="form-control" placeholder="City" style="margin-bottom:8px">
<input name="rate" class="form-control" placeholder="Rate %" required style="margin-bottom:8px">
<button class="btn btn-primary">Add tax</button>
{!! Form::close() !!}
<ul class="list-group" style="margin-top:15px">
@foreach($tax_rules as $rule)
<li class="list-group-item">{{ $rule->name }} — {{ $rule->level }} {{ $rule->state }} — {{ $rule->rate }}%</li>
@endforeach
</ul>
</div></div>
</div>
</div>
</section>
@endsection
