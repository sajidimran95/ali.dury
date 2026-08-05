@extends('layouts.app')
@section('title', __('ecommerce::lang.coupons'))

@section('css')
    <link rel="stylesheet" href="{{ asset('css/product-list-metrics.css?v=' . ($asset_v ?? '') . '&plm=4') }}">
@endsection

@section('content')
<section class="content-header"><h1>@lang('ecommerce::lang.coupons')</h1></section>
<section class="content">
@if (!empty($coupon_stats))
    @include('ecommerce::admin.coupons.partials.list_metrics')
@endif
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
<table class="table table-bordered table-striped" style="margin-top:20px">
<thead>
    <tr>
        <th>@lang('ecommerce::lang.coupon_code')</th>
        <th>@lang('ecommerce::lang.coupon_type')</th>
        <th>@lang('ecommerce::lang.value')</th>
        <th>@lang('ecommerce::lang.times_applied')</th>
        <th>@lang('ecommerce::lang.usage_limit')</th>
        <th></th>
    </tr>
</thead>
<tbody>
@forelse($coupons as $coupon)
@php
    $applied = (int) ($coupon->times_applied ?? $coupon->used_count ?? 0);
    $limit = $coupon->usage_limit;
@endphp
<tr>
    <td><strong>{{ $coupon->code }}</strong></td>
    <td>{{ ucfirst($coupon->type) }}</td>
    <td>
        @if($coupon->type === 'percentage')
            {{ @num_format($coupon->value) }}%
        @else
            {{ @num_format($coupon->value) }}
        @endif
    </td>
    <td>
        <strong>{{ @num_format($applied) }}</strong>
        @if($applied === 0)
            <span class="label label-default">@lang('ecommerce::lang.never_used')</span>
        @endif
    </td>
    <td>
        @if(!is_null($limit))
            {{ @num_format($applied) }} / {{ @num_format($limit) }}
        @else
            —
        @endif
    </td>
    <td>
        <form method="post" action="{{ route('ecommerce.admin.coupons.destroy',$coupon->id) }}">
            @csrf @method('DELETE')
            <button class="btn btn-xs btn-danger">Delete</button>
        </form>
    </td>
</tr>
@empty
<tr>
    <td colspan="6" class="text-center text-muted">@lang('ecommerce::lang.no_coupons_yet')</td>
</tr>
@endforelse
</tbody>
</table>
</div></div>
</section>
@endsection
