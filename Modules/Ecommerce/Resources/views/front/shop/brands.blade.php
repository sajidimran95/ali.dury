@extends('ecommerce::front.layouts.store')
@section('title', 'Shop by Brand')
@section('content')
<div class="gs-wrap py-12">
    <h1 class="text-4xl font-extrabold mb-2">All Brands</h1>
    <p class="text-sm text-[#8b95b8] mb-8">Shop the wholesale catalog by manufacturer.</p>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
        @forelse($brands as $brand)
            @php $logo = ($brand_covers[$brand->id] ?? $brand_covers[(string) $brand->id] ?? null); @endphp
            <a href="{{ route('ecommerce.shop', ['brand' => $brand->id]) }}" class="wn-tile text-center">
                @if($logo)
                    <img src="{{ $logo }}" alt="{{ $brand->name }}" class="h-12 w-auto mx-auto mb-2 object-contain">
                @endif
                <div class="font-extrabold">{{ $brand->name }}</div>
                <div class="text-xs text-[#8b95b8] mt-1">View products</div>
            </a>
        @empty
            <p class="col-span-full text-sm text-[#8b95b8]">Brands will appear after inventory is uploaded.</p>
        @endforelse
    </div>
</div>
@endsection
