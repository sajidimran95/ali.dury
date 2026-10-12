@extends('ecommerce::front.layouts.store')
@section('title', 'Categories')
@section('content')
@php use Modules\Ecommerce\Support\Storefront; @endphp
<div class="max-w-screen-xl mx-auto px-4 py-10 md:py-14">
    <p class="text-xs font-bold uppercase tracking-widest mb-1 text-[#8b95b8]">Browse by Type</p>
    <h1 class="text-3xl font-extrabold mb-8">Categories</h1>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($categories as $cat)
            @php
                $photos = Storefront::heroPhotos();
                $photo = $photos[$loop->index % count($photos)];
            @endphp
            <a href="{{ route('ecommerce.category', $cat->id) }}" class="wn-panel overflow-hidden block hover:border-[#f23488]">
                <div class="h-28 overflow-hidden relative bg-[#0c1224]">
                    <img src="{{ Storefront::photo($photo) }}" alt="" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                </div>
                <div class="p-4">
                    <p class="font-semibold">{{ $cat->name }}</p>
                    <p class="text-xs mt-1 text-[#8b95b8]">{{ (int) ($counts[$cat->id] ?? 0) }} products</p>
                    @if($cat->sub_categories->count())
                        <p class="text-xs mt-2 text-[#8b95b8] line-clamp-2">{{ $cat->sub_categories->take(4)->pluck('name')->join(', ') }}</p>
                    @endif
                </div>
            </a>
        @empty
            <p class="col-span-full text-sm text-[#8b95b8]">No categories yet.</p>
        @endforelse
    </div>
</div>
@endsection
