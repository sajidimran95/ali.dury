@extends('layouts.app')
@section('title', __('ecommerce::lang.banners'))
@section('content')
<section class="content-header"><h1>@lang('ecommerce::lang.banners')</h1></section>
<section class="content">
<div class="box"><div class="box-body">
{!! Form::open(['url' => route('ecommerce.admin.banners.store'), 'method' => 'post', 'files' => true, 'class' => 'form-inline']) !!}
<input name="title" class="form-control" placeholder="Title">
<input name="subtitle" class="form-control" placeholder="Subtitle">
<input name="button_text" class="form-control" placeholder="Button text">
<input name="button_link" class="form-control" placeholder="Button link">
<input type="file" name="image" class="form-control">
<button class="btn btn-primary">Add</button>
{!! Form::close() !!}
<hr>
<table class="table table-bordered">
<thead><tr><th>Title</th><th>Image</th><th></th></tr></thead>
<tbody>
@foreach($banners as $banner)
<tr>
<td>{{ $banner->title }}</td>
<td>@if($banner->image)<img src="{{ asset('uploads/img/'.$banner->image) }}" height="40">@endif</td>
<td>
<form method="post" action="{{ route('ecommerce.admin.banners.destroy', $banner->id) }}">@csrf @method('DELETE')
<button class="btn btn-danger btn-xs">Delete</button></form>
</td>
</tr>
@endforeach
</tbody>
</table>
</div></div>
</section>
@endsection
