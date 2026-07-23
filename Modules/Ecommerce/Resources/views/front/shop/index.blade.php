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

<section class="bg-white border-b border-line">
    <div class="max-w-[1400px] mx-auto px-4 py-8 md:py-10 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-[11px] uppercase tracking-[0.18em] text-black/40 font-bold mb-1">Wholesale catalog</p>
            <h1 class="text-3xl md:text-4xl font-extrabold">{{ $activeCat->name ?? 'Shop all' }}</h1>
            <p class="text-sm text-black/50 mt-2">{{ $products->total() }} products · live POS inventory</p>
        </div>
        @if($bannerImg)
            <div class="w-full sm:w-48 h-24 rounded-xl overflow-hidden border border-line">
                <img src="{{ $bannerImg }}" alt="" class="w-full h-full object-cover">
            </div>
        @endif
    </div>
</section>

<div class="max-w-[1400px] mx-auto px-4 py-8">
    <div class="grid lg:grid-cols-12 gap-6">
        <aside class="lg:col-span-3">
            <form method="get" class="rounded-xl bg-white border border-line p-5 space-y-4 sticky top-28 text-sm">
                <div>
                    <label class="font-bold block mb-2">Category</label>
                    <select name="category" class="input-w">
                        <option value="">All categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if($brands->count())
                <div>
                    <label class="font-bold block mb-2">Brand</label>
                    <select name="brand" class="input-w">
                        <option value="">All brands</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" @selected(request('brand') == $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label class="font-bold block mb-2">Price</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" step="0.01" name="min_price" value="{{ request('min_price') }}" placeholder="Min" class="input-w">
                        <input type="number" step="0.01" name="max_price" value="{{ request('max_price') }}" placeholder="Max" class="input-w">
                    </div>
                </div>
                <div>
                    <label class="font-bold block mb-2">Sort</label>
                    <select name="sort" class="input-w">
                        <option value="newest" @selected($sort==='newest')>Newest</option>
                        <option value="price_asc" @selected($sort==='price_asc')>Price: Low to High</option>
                        <option value="price_desc" @selected($sort==='price_desc')>Price: High to Low</option>
                    </select>
                </div>
                <button class="w-full btn-brand">Apply filters</button>
            </form>
        </aside>

        <div class="lg:col-span-9">
            @include('ecommerce::front.partials.product_grid', ['products' => $products])
            <div class="mt-8">
                {{ $products->links('ecommerce::front.partials.pagination') }}
            </div>
        </div>
    </div>
</div>
@endsection
