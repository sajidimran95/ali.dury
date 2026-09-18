<div>
    <label class="font-bold block mb-2">Category</label>
    <select name="category" class="input-w">
        <option value="">All categories</option>
        @forelse($categories ?? [] as $cat)
            <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
            @foreach(($cat->sub_categories ?? []) as $sub)
                <option value="{{ $sub->id }}" @selected(request('category') == $sub->id)>&nbsp;&nbsp;{{ $sub->name }}</option>
            @endforeach
        @empty
            <option value="" disabled>Category</option>
        @endforelse
    </select>
</div>
<div>
    <label class="font-bold block mb-2">Brand</label>
    <select name="brand" class="input-w">
        <option value="">All brands</option>
        @forelse($brands ?? [] as $brand)
            <option value="{{ $brand->id }}" @selected(request('brand') == $brand->id)>{{ $brand->name }}</option>
        @empty
            <option value="" disabled>Brand</option>
        @endforelse
    </select>
</div>
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
        <option value="newest" @selected(($sort ?? 'newest') === 'newest')>Newest</option>
        <option value="price_asc" @selected(($sort ?? '') === 'price_asc')>Price: Low to High</option>
        <option value="price_desc" @selected(($sort ?? '') === 'price_desc')>Price: High to Low</option>
    </select>
</div>
