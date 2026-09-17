{{-- Figma Products.tsx sidebar filters --}}
@php
    $currentSort = $sort ?? request('sort', 'featured');
    $hasFilters = request()->filled('category')
        || request()->filled('brand')
        || request()->boolean('on_sale')
        || request()->boolean('new_only')
        || request()->filled('min_price')
        || request()->filled('max_price')
        || request()->filled('q');
@endphp

<div class="space-y-6">
    <div>
        <h3 class="font-display font-semibold text-sm uppercase tracking-wide mb-3" style="color:#2E6A49">Quick Filters</h3>
        <div class="space-y-2">
            <label class="flex items-center gap-2 cursor-pointer group">
                <input type="checkbox" name="on_sale" value="1" @checked(request('on_sale'))
                       class="w-4 h-4 rounded accent-[#4CAF79]" onchange="this.form.submit()">
                <span class="text-sm group-hover:text-[#4CAF79] transition-colors" style="color:#1a1a1a">On Sale</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer group">
                <input type="checkbox" name="new_only" value="1" @checked(request('new_only'))
                       class="w-4 h-4 rounded accent-[#4CAF79]" onchange="this.form.submit()">
                <span class="text-sm group-hover:text-[#4CAF79] transition-colors" style="color:#1a1a1a">New Arrivals</span>
            </label>
        </div>
    </div>

    @if($brands->count())
    <div>
        <h3 class="font-display font-semibold text-sm uppercase tracking-wide mb-3" style="color:#2E6A49">Brand</h3>
        <div class="space-y-1.5 max-h-52 overflow-y-auto pr-1">
            <label class="flex items-center gap-2 cursor-pointer group">
                <input type="radio" name="brand" value="" @checked(! request()->filled('brand'))
                       class="accent-[#4CAF79]" onchange="this.form.submit()">
                <span class="text-sm" style="color:#1a1a1a">All Brands</span>
            </label>
            @foreach($brands as $brand)
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input type="radio" name="brand" value="{{ $brand->id }}" @checked((string) request('brand') === (string) $brand->id)
                           class="accent-[#4CAF79]" onchange="this.form.submit()">
                    <span class="text-sm group-hover:text-[#4CAF79] transition-colors" style="color:#1a1a1a">{{ $brand->name }}</span>
                </label>
            @endforeach
        </div>
    </div>
    @endif

    <div>
        <h3 class="font-display font-semibold text-sm uppercase tracking-wide mb-3" style="color:#2E6A49">Category</h3>
        <div class="space-y-1.5 max-h-52 overflow-y-auto pr-1">
            <label class="flex items-center gap-2 cursor-pointer group">
                <input type="radio" name="category" value="" @checked(! request()->filled('category'))
                       class="accent-[#4CAF79]" onchange="this.form.submit()">
                <span class="text-sm" style="color:#1a1a1a">All Categories</span>
            </label>
            @foreach($categories as $cat)
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input type="radio" name="category" value="{{ $cat->id }}" @checked((string) request('category') === (string) $cat->id)
                           class="accent-[#4CAF79]" onchange="this.form.submit()">
                    <span class="text-sm group-hover:text-[#4CAF79] transition-colors" style="color:#1a1a1a">{{ $cat->name }}</span>
                </label>
            @endforeach
        </div>
    </div>

    @if(request()->filled('q'))
        <input type="hidden" name="q" value="{{ request('q') }}">
    @endif
    <input type="hidden" name="sort" value="{{ $currentSort }}">

    @if($hasFilters)
        <a href="{{ route('ecommerce.shop', array_filter(['sort' => $currentSort !== 'featured' ? $currentSort : null])) }}"
           class="text-sm font-semibold transition-colors hover:underline inline-block" style="color:#e53e3e">Clear All Filters</a>
    @endif
</div>
