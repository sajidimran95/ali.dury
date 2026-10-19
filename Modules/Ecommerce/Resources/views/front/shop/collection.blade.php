@extends('ecommerce::front.layouts.store')
@section('title', $collection->name)
@section('content')
<div class="gs-wrap py-10">
    <h1 class="text-2xl font-extrabold mb-2">{{ $collection->name }}</h1>
    <p class="text-[#8b95b8] mb-8">{{ $collection->description }}</p>
    @include('ecommerce::front.partials.product_grid', ['products' => $collection->products])
</div>
@endsection
