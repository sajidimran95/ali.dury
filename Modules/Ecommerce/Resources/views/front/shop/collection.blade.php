@extends('ecommerce::front.layouts.store')
@section('title', $collection->name)
@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-semibold mb-2">{{ $collection->name }}</h1>
    <p class="text-slate-500 mb-8">{{ $collection->description }}</p>
    @include('ecommerce::front.partials.product_grid', ['products' => $collection->products])
</div>
@endsection
