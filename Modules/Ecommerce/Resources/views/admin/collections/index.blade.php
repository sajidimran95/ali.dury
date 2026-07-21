@extends('layouts.app')
@section('title', __('ecommerce::lang.collections'))
@section('content')
<section class="content-header"><h1>@lang('ecommerce::lang.collections')</h1></section>
<section class="content">
<div class="box"><div class="box-body">
{!! Form::open(['url' => route('ecommerce.admin.collections.store'), 'method' => 'post']) !!}
<div class="form-group">{!! Form::text('name', null, ['class'=>'form-control','placeholder'=>'Name','required']) !!}</div>
<div class="form-group">{!! Form::textarea('description', null, ['class'=>'form-control','rows'=>2]) !!}</div>
<div class="form-group">
{!! Form::select('product_ids[]', $products, null, ['class'=>'form-control select2','multiple']) !!}
</div>
<button class="btn btn-primary">Add collection</button>
{!! Form::close() !!}
<hr>
<table class="table"><thead><tr><th>Name</th><th>Slug</th><th>Products</th><th></th></tr></thead>
<tbody>
@foreach($collections as $c)
<tr>
<td>{{ $c->name }}</td><td>{{ $c->slug }}</td><td>{{ $c->products_count }}</td>
<td><form method="post" action="{{ route('ecommerce.admin.collections.destroy', $c->id) }}">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Delete</button></form></td>
</tr>
@endforeach
</tbody></table>
</div></div>
</section>
@endsection
