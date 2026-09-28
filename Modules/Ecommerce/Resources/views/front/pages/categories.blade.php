@extends('ecommerce::front.layouts.store')
@section('title', 'Categories')
@section('content')
@php use Modules\Ecommerce\Support\Storefront; @endphp
<div class="max-w-screen-xl mx-auto px-4 py-10 md:py-14">
    <p class="text-xs font-bold uppercase tracking-widest mb-1" style="color:#4CAF79">Browse by Type</p>
    <h1 class="font-display text-3xl font-bold mb-8">Categories</h1>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($categories as $cat)
            @php
                $img = optional($cat->sub_categories->first())->name;
                $photos = Storefront::heroPhotos();
                $photo = $photos[$loop->index % count($photos)];
            @endphp
            <a href="{{ route('ecommerce.category', $cat->id) }}" class="group rounded-xl overflow-hidden border border-[#e8e8e8] bg-white hover:shadow-lg hover:-translate-y-0.5 transition-all">
                <div class="h-28 overflow-hidden relative bg-[#f0f0f0]">
                    <img src="{{ Storefront::photo($photo) }}" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                </div>
                <div class="p-4">
                    <p class="font-semibold">{{ $cat->name }}</p>
                    <p class="text-xs mt-1" style="color:#888">{{ (int) ($counts[$cat->id] ?? 0) }} products</p>
                    @if($cat->sub_categories->count())
                        <p class="text-xs mt-2 text-[#666] line-clamp-2">{{ $cat->sub_categories->take(4)->pluck('name')->join(', ') }}</p>
                    @endif
                </div>
            </a>
        @empty
            <p class="col-span-full text-sm text-[#666]">No categories yet.</p>
        @endforelse
    </div>
</div>
@endsection
