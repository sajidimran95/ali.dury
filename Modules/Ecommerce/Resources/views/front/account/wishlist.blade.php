@extends('ecommerce::front.layouts.store')
@section('title', 'Wishlist')
@section('content')
<div class="ecom-acc max-w-5xl mx-auto px-4 py-10">
    @include('ecommerce::front.account.partials.mobile_topbar', ['title' => 'Wishlist'])
    <h1 class="text-2xl font-semibold mb-6 ecom-acc-desktop-only">Wishlist</h1>
    <div class="px-1 lg:px-0">
        @if($items->isEmpty())
            <div class="wn-panel rounded-xl p-8 text-center">
                <p class="text-white/70 mb-4">Your wishlist is empty.</p>
                <a href="{{ route('ecommerce.shop') }}" class="btn-brand inline-flex">Shop products</a>
            </div>
        @else
            @include('ecommerce::front.partials.product_grid', [
                'products' => $items->pluck('product')->filter(),
                'minCards' => 0,
            ])
        @endif
    </div>
</div>
@endsection
