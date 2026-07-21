@extends('layouts.app')
@section('title', __('ecommerce::lang.customers'))
@section('content')
<section class="content-header"><h1>@lang('ecommerce::lang.customers')</h1></section>
<section class="content">
<div class="box"><div class="box-body table-responsive">
<table class="table table-bordered">
<thead><tr><th>Name</th><th>Email</th><th>Mobile</th></tr></thead>
<tbody>
@foreach($customers as $c)
<tr><td>{{ $c->name }}</td><td>{{ $c->email }}</td><td>{{ $c->mobile }}</td></tr>
@endforeach
</tbody></table>
{{ $customers->links() }}
</div></div>
</section>
@endsection
