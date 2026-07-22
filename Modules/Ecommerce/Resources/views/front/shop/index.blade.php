@extends('ecommerce::front.layouts.store')
@section('title', 'Shop')
@section('content')
@php
    $covers = [];
    $coverFile = storage_path('app/ecom_category_covers.json');
    if (is_file($coverFile)) {
        $covers = json_decode(file_get_contents($coverFile), true) ?: [];
    }
    $activeCat = $categories->firstWhere('id', (int) request('category'));
    $bannerImg = $activeCat && isset($covers[$activeCat->id])
        ? asset('uploads/img/'.$covers[$activeCat->id])
        : ($products->first()->image_url ?? null);
@endphp

<section class="relative overflow-hidden bg-ink text-white">
    @if($bannerImg)
        <img src="{{ $bannerImg }}" alt="" class="absolute inset-0 w-full h-full object-cover opacity-45">
    @endif
    <div class="absolute inset-0 bg-gradient-to-r from-ink via-ink/80 to-ink/40"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-14 md:py-20">
        <p class="text-[11px] uppercase tracking-[0.22em] text-white/55 mb-2">Catalog</p>
        <h1 class="font-display text-4xl md:text-6xl">{{ $activeCat->name ?? 'Shop all' }}</h1>
        <p class="text-white/65 text-sm mt-3 max-w-lg">{{ $products->total() }} products · live POS inventory · USA shipping</p>
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="grid lg:grid-cols-12 gap-8">
        <aside class="lg:col-span-3">
            <form method="get" class="rounded-2xl bg-white border border-black/5 p-5 space-y-5 sticky top-28 text-sm">
                <div>
                    <label class="font-semibold block mb-2">Category</label>
                    <select name="category" class="w-full rounded-xl border border-black/10 px-3 py-2.5 bg-mist">
                        <option value="">All categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if($brands->count())
                <div>
                    <label class="font-semibold block mb-2">Brand</label>
                    <select name="brand" class="w-full rounded-xl border border-black/10 px-3 py-2.5 bg-mist">
                        <option value="">All brands</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" @selected(request('brand') == $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label class="font-semibold block mb-2">Price</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" step="0.01" name="min_price" value="{{ request('min_price') }}" placeholder="Min" class="rounded-xl border border-black/10 px-3 py-2.5 bg-mist">
                        <input type="number" step="0.01" name="max_price" value="{{ request('max_price') }}" placeholder="Max" class="rounded-xl border border-black/10 px-3 py-2.5 bg-mist">
                    </div>
                </div>
                <div>
                    <label class="font-semibold block mb-2">Sort</label>
                    <select name="sort" class="w-full rounded-xl border border-black/10 px-3 py-2.5 bg-mist">
                        <option value="newest" @selected($sort==='newest')>Newest</option>
                        <option value="price_asc" @selected($sort==='price_asc')>Price: Low to High</option>
                        <option value="price_desc" @selected($sort==='price_desc')>Price: High to Low</option>
                    </select>
                </div>
                <button class="w-full rounded-full bg-ink text-white py-2.5 font-semibold">Apply filters</button>
            </form>
        </aside>

        <div class="lg:col-span-9">
            <div class="flex items-center justify-between mb-5 text-sm text-black/50">
                <span>{{ $products->total() }} products</span>
            </div>
            @include('ecommerce::front.partials.product_grid', ['products' => $products])
            <div class="mt-10">
                {{ $products->links('ecommerce::front.partials.pagination') }}
            </div>
        </div>
    </div>
</div>
@endsection
