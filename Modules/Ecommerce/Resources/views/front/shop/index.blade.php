@extends('ecommerce::front.layouts.store')
@section('title', 'Shop')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 grid lg:grid-cols-4 gap-8">
    <aside class="lg:col-span-1 space-y-6">
        <form method="get" class="rounded-2xl bg-white border border-slate-200 p-4 space-y-4 text-sm">
            <div>
                <label class="font-medium block mb-1">Category</label>
                <select name="category" class="w-full border rounded-lg px-3 py-2">
                    <option value="">All</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="font-medium block mb-1">Brand</label>
                <select name="brand" class="w-full border rounded-lg px-3 py-2">
                    <option value="">All</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" @selected(request('brand') == $brand->id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <input type="number" step="0.01" name="min_price" value="{{ request('min_price') }}" placeholder="Min $" class="border rounded-lg px-3 py-2">
                <input type="number" step="0.01" name="max_price" value="{{ request('max_price') }}" placeholder="Max $" class="border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="font-medium block mb-1">Sort</label>
                <select name="sort" class="w-full border rounded-lg px-3 py-2">
                    <option value="newest" @selected($sort==='newest')>Newest</option>
                    <option value="price_asc" @selected($sort==='price_asc')>Price: Low to High</option>
                    <option value="price_desc" @selected($sort==='price_desc')>Price: High to Low</option>
                </select>
            </div>
            <button class="w-full rounded-full bg-ink text-white py-2.5">Apply filters</button>
        </form>
    </aside>
    <div class="lg:col-span-3">
        <h1 class="text-2xl font-semibold mb-6">Shop</h1>
        @include('ecommerce::front.partials.product_grid', ['products' => $products])
        <div class="mt-8">
            {{ $products->links('ecommerce::front.partials.pagination') }}
        </div>
    </div>
</div>
@endsection
