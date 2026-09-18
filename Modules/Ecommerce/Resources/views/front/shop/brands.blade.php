@extends('ecommerce::front.layouts.store')
@section('title', 'Shop by Brand | Greencloud Wholesale')
@section('content')
<div class="max-w-[1400px] mx-auto px-4 py-12">
    <h1 class="gc-serif text-4xl mb-2">All Brands</h1>
    <p class="text-sm text-black/50 mb-8">Shop the wholesale catalog by manufacturer.</p>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
        @forelse($brands as $brand)
            <a href="{{ route('ecommerce.shop', ['brand' => $brand->id]) }}" class="gc-brand-tile">
                <div class="font-extrabold">{{ $brand->name }}</div>
                <div class="text-xs text-black/45 mt-1">View products</div>
            </a>
        @empty
            <p class="col-span-full text-sm text-black/50">Brands will appear after inventory is uploaded.</p>
        @endforelse
    </div>
</div>
@endsection
