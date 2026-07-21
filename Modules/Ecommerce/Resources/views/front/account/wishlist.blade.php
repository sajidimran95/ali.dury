@extends('ecommerce::front.layouts.store')
@section('title', 'Wishlist')
@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-semibold mb-6">Wishlist</h1>
    @include('ecommerce::front.partials.product_grid', ['products' => $items->pluck('product')->filter()])
</div>
@endsection
