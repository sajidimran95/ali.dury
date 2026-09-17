@extends('ecommerce::front.layouts.store')
@php
    $activeCat = $categories->firstWhere('id', (int) request('category'));
    $pageTitle = $activeCat->name ?? 'All Products';
    $currentSort = $sort ?? request('sort', 'featured');
    $hasActiveFilters = request()->filled('category')
        || request()->filled('brand')
        || request()->boolean('on_sale')
        || request()->boolean('new_only')
        || request()->filled('q');
@endphp
@section('title', $activeCat ? ($activeCat->name.' · Shop') : 'All Products')

@section('content')

<main id="main-content" class="py-8 px-4 min-h-screen" style="background:#F4F4F4">
    <div class="max-w-screen-xl mx-auto">
        {{-- Breadcrumb --}}
        <nav class="text-sm mb-4" aria-label="Breadcrumb">
            <ol class="flex items-center gap-1.5 flex-wrap">
                <li><a href="{{ url('/') }}" class="hover:underline" style="color:#1a73e8">Home</a></li>
                <li style="color:#999" aria-hidden="true">›</li>
                @if($activeCat)
                    <li><a href="{{ route('ecommerce.shop') }}" class="hover:underline" style="color:#1a73e8">All Products</a></li>
                    <li style="color:#999" aria-hidden="true">›</li>
                    <li style="color:#666">{{ $activeCat->name }}</li>
                @else
                    <li style="color:#666">All Products</li>
                @endif
            </ol>
        </nav>

        @include('ecommerce::front.shop.partials.hub_banner', ['hub' => $hub ?? null])

        {{-- Title + sort --}}
        <div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
            <h1 class="font-display text-2xl font-bold" style="color:#1a1a1a">
                {{ $pageTitle }}
                <span class="ml-2 text-base font-body font-normal" style="color:#999">({{ $products->total() }})</span>
            </h1>
            <div class="flex items-center gap-3">
                <button type="button" id="openShopFilter"
                        class="lg:hidden flex items-center gap-2 px-4 py-2 rounded-lg border border-[#e0e0e0] bg-white text-sm font-medium"
                        aria-expanded="false" aria-controls="filter-sidebar">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M2 4h12M4 8h8M6 12h4" stroke="#2E6A49" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Filters
                    @if($hasActiveFilters)
                        <span class="inline-flex items-center justify-center w-4 h-4 rounded-full text-white text-xs" style="background:#4CAF79">!</span>
                    @endif
                </button>
                <form method="get" action="{{ route('ecommerce.shop') }}" class="inline-flex items-center gap-2">
                    @foreach(request()->except(['sort', 'page']) as $key => $value)
                        @if(is_scalar($value) && $value !== '' && $value !== null)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <label for="sort-select" class="sr-only">Sort products</label>
                    <select id="sort-select" name="sort"
                            class="border border-[#e0e0e0] bg-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4CAF79]"
                            onchange="this.form.submit()">
                        <option value="featured" @selected($currentSort === 'featured')>Featured</option>
                        <option value="bestselling" @selected($currentSort === 'bestselling')>Best Selling</option>
                        <option value="newest" @selected($currentSort === 'newest')>Newest</option>
                        <option value="name" @selected($currentSort === 'name')>Name A–Z</option>
                        <option value="price_asc" @selected($currentSort === 'price_asc')>Price: Low to High</option>
                        <option value="price_desc" @selected($currentSort === 'price_desc')>Price: High to Low</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="flex gap-6">
            {{-- Desktop sidebar --}}
            <div class="hidden lg:block w-56 shrink-0">
                <form method="get" action="{{ route('ecommerce.shop') }}" class="bg-white rounded-xl p-5 border border-[#e8e8e8] sticky top-24" aria-label="Product filters">
                    @include('ecommerce::front.shop.partials.filter_fields', ['sort' => $currentSort])
                </form>
            </div>

            {{-- Product grid --}}
            <div class="flex-1 min-w-0">
                @if($products->count())
                    @include('ecommerce::front.partials.product_grid', [
                        'products' => $products,
                        'gridClass' => 'grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4',
                    ])
                    <div class="mt-8">
                        {{ $products->links('ecommerce::front.partials.pagination') }}
                    </div>
                @else
                    <div class="text-center py-16 bg-white rounded-xl border border-[#e8e8e8]">
                        <p class="text-4xl mb-3" aria-hidden="true">🔍</p>
                        <p class="font-semibold text-base mb-2" style="color:#1a1a1a">No products match your filters</p>
                        <p class="text-sm mb-4" style="color:#666">Try adjusting or clearing your filters.</p>
                        <a href="{{ route('ecommerce.shop') }}"
                           class="inline-flex text-sm font-semibold px-5 py-2 rounded-lg text-white transition-all hover:opacity-90"
                           style="background:#4CAF79">Clear Filters</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</main>

{{-- Mobile filter drawer --}}
<div id="shopFilterSheet" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-black bg-opacity-40 z-40" data-close-shop-filter aria-hidden="true"></div>
    <div id="filter-sidebar" class="absolute left-0 top-0 h-full w-72 bg-white z-50 p-5 overflow-y-auto shadow-xl"
         role="dialog" aria-modal="true" aria-label="Product filters">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-display font-bold text-base" style="color:#2E6A49">Filter Products</h2>
            <button type="button" class="p-1 rounded hover:bg-gray-100" data-close-shop-filter aria-label="Close filters">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" aria-hidden="true">
                    <path d="M4 4l10 10M14 4L4 14" stroke="#666" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
        <form method="get" action="{{ route('ecommerce.shop') }}">
            @include('ecommerce::front.shop.partials.filter_fields', ['sort' => $currentSort])
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const sheet = document.getElementById('shopFilterSheet');
    const openBtn = document.getElementById('openShopFilter');
    if (!sheet || !openBtn) return;

    const open = () => {
        sheet.classList.remove('hidden');
        sheet.setAttribute('aria-hidden', 'false');
        openBtn.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    };
    const close = () => {
        sheet.classList.add('hidden');
        sheet.setAttribute('aria-hidden', 'true');
        openBtn.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    };

    openBtn.addEventListener('click', open);
    sheet.querySelectorAll('[data-close-shop-filter]').forEach((el) => el.addEventListener('click', close));
})();
</script>
@endpush
