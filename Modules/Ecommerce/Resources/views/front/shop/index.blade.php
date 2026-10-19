@extends('ecommerce::front.layouts.store')
@section('title', 'Shop')
@section('content')
@php
    $activeCat = ($categories ?? collect())->firstWhere('id', (int) request('category'));
    if (! $activeCat) {
        $activeCat = ($categories ?? collect())->flatMap(fn ($c) => collect([$c])->merge($c->sub_categories ?? []))->firstWhere('id', (int) request('category'));
    }
    $sort = $sort ?? request('sort', 'name_asc');
    $count = (int) request('product_count', $products->perPage());
    $pageTitle = $activeCat->name ?? 'Shop';
    $viewMode = request('view', 'list') === 'grid' ? 'grid' : 'list';
    $keep = request()->except(['page']);
    $ico = 'https://gsdistributor.com/media/icons';
    $rightChevron = '<svg viewBox="64 64 896 896" width="12" height="12" fill="currentColor" aria-hidden="true"><path d="M765.7 486.8L314.9 134.7A7.97 7.97 0 00302 141v77.3c0 4.9 2.3 9.6 6.1 12.6l360 281.1-360 281.1c-3.9 3-6.1 7.7-6.1 12.6V883c0 6.7 7.7 10.4 12.9 6.3l450.8-352.1a31.96 31.96 0 000-50.4z"></path></svg>';
    $chipX = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIiIGhlaWdodD0iMTIiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNMTMuMzg3IDEyTDIwLjQyIDMuNjE4QzIwLjUzNiAzLjQ3OSAyMC40MzcgMy4yNjggMjAuMjU1IDMuMjY4SDE4LjExN0MxNy45OTEgMy4yNjggMTcuODcxIDMuMzI0IDE3Ljc4OCAzLjQyTDExLjk4OCAxMC4zMzNMNi4xODkgMy40MkM2LjEwOSAzLjMyNCA1Ljk4OCAzLjI2OCA1Ljg2IDMuMjY4SDMuNzIyQzMuNTQgMy4yNjggMy40NDEgMy40NzkgMy41NTkgMy42MThMMTAuNTkgMTJMMy41NTkgMjAuMzhDMy41MzMgMjAuNDEyIDMuNTE2IDIwLjQ1IDMuNTEgMjAuNDlDMy41MDUgMjAuNTMxIDMuNTExIDIwLjU3MiAzLjUyOCAyMC42MDlDMy41NDUgMjAuNjQ2IDMuNTczIDIwLjY3NyAzLjYwNyAyMC42OTlDMy42NDIgMjAuNzIxIDMuNjgyIDIwLjczMiAzLjcyMiAyMC43MzJINS44NkM1Ljk4NiAyMC43MzIgNi4xMDYgMjAuNjc2IDYuMTg5IDIwLjU3OUwxMS45ODggMTMuNjY2TDE3Ljc4OCAyMC41NzlDMTcuODY4IDIwLjY3NiAxNy45ODggMjAuNzMyIDE4LjExNyAyMC43MzJIMjAuMjU1QzIwLjQzNyAyMC43MzIgMjAuNTM2IDIwLjUyIDIwLjQxOCAyMC4zOEwxMy4zODcgMTJaIiBmaWxsPSJibGFjayIvPjwvc3ZnPg==';
@endphp

<div class="gs-shop-page" id="gsShopPage">
    <nav class="shop-breadcrumb">
        <a href="{{ route('ecommerce.home') }}">Home</a>
        <span>›</span>
        <a href="{{ route('ecommerce.shop') }}">Shop</a>
        @if($activeCat)
            <span>›</span>
            <span class="current">{{ $activeCat->name }}</span>
        @endif
    </nav>

    <div class="ant-row-shop">
        <aside class="sidebar">
            <div class="scroller-class">
                <div class="text-wrapper-filter">Filters</div>
                <img src="{{ $ico }}/black-left-line.png" alt="close" class="responsive-filter" id="gsCollapseFilters">
            </div>

            <div class="text-wrapper-6">Explore By Selection</div>
            <div class="explore-filters">
                <div class="explore-filters-item">
                    <span>New Arrivals <img class="new-badge" src="https://gsdistributor.com/_next/static/media/new-tag.aa10673b6b53cee1.gif" alt=""></span>
                    <input type="checkbox" {{ request('new_only') ? 'checked' : '' }}
                           onchange="location.href='{{ route('ecommerce.shop', array_merge($keep, ['new_only' => request('new_only') ? null : 1, 'page' => null])) }}'">
                </div>
                <div class="explore-filters-item">
                    <span>Featured Products</span>
                    <input type="checkbox" {{ request('featured') ? 'checked' : '' }}
                           onchange="location.href='{{ route('ecommerce.shop', array_merge($keep, ['featured' => request('featured') ? null : 1, 'page' => null])) }}'">
                </div>
                <div class="explore-filters-item">
                    <span>Hot Seller Products</span>
                    <input type="checkbox" {{ request('hot') ? 'checked' : '' }}
                           onchange="location.href='{{ route('ecommerce.shop', array_merge($keep, ['hot' => request('hot') ? null : 1, 'page' => null])) }}'">
                </div>
            </div>

            <div class="frame-13">
                <div class="text-wrapper-6" style="margin:0">Categories</div>
                @if($activeCat)
                    <a class="breadcrum-category" href="{{ route('ecommerce.shop', request()->except(['category', 'page'])) }}">
                        {{ strtoupper($activeCat->name) }}
                        <img src="{{ $chipX }}" alt="">
                    </a>
                @endif
            </div>
            <div class="sidebar-search">
                <input type="text" class="header-searchbar" id="gsCatSearch" placeholder="Search Categories">
                <div class="search-icon"><img src="{{ $ico }}/Searchwhite.svg" alt="Search" class="sidebar-search-icon"></div>
            </div>
            <div class="sidebar-widget-list" id="gsCatList">
                @foreach($categories as $cat)
                    <a class="category-name {{ (int) request('category') === (int) $cat->id ? 'active-category' : '' }}"
                       href="{{ route('ecommerce.shop', array_merge($keep, ['category' => $cat->id, 'page' => null])) }}" data-name="{{ strtolower($cat->name) }}">
                        <span>{{ in_array($cat->name, ['Tobacco', 'Vape Devices'], true) ? strtoupper($cat->name) : $cat->name }}</span>
                        {!! $rightChevron !!}
                    </a>
                @endforeach
            </div>

            <div class="text-wrapper-6" style="margin-top:22px">Brands</div>
            <div class="sidebar-search">
                <input type="text" class="header-searchbar" id="gsBrandSearch" placeholder="Search Brands">
                <div class="search-icon"><img src="{{ $ico }}/Searchwhite.svg" alt="Search" class="sidebar-search-icon"></div>
            </div>
            <div class="scroll-on-brand" id="gsBrandList">
                @foreach($brands as $brand)
                    <label class="gs-shop-brand" data-name="{{ strtolower($brand->name) }}">
                        <input type="checkbox" {{ (int) request('brand') === (int) $brand->id ? 'checked' : '' }}
                               onchange="location.href='{{ route('ecommerce.shop', array_merge($keep, ['brand' => (int) request('brand') === (int) $brand->id ? null : $brand->id, 'page' => null])) }}'">
                        <span>{{ $brand->name }}</span>
                    </label>
                @endforeach
            </div>
        </aside>

        <div class="shop-page-margin">
            <div class="shop-page-head">
                <div class="shop-page-head-left">
                    <button type="button" class="filter-display-reponsive" id="gsShowFilters" aria-label="Show filters">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M7.5 4.17a.83.83 0 1 1 0 1.66.83.83 0 0 1 0-1.66Zm3.33.83H16.67M3.33 5h.84M12.5 9.17a.83.83 0 1 1 0 1.66.83.83 0 0 1 0-1.66Zm3.33.83h.84M3.33 10h6.67M7.5 14.17a.83.83 0 1 1 0 1.66.83.83 0 0 1 0-1.66Zm3.33.83h6.67M3.33 15h.84" stroke="#1a1a1a" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        Filters
                    </button>
                    <h1 class="shop-page-main-cat">{{ $pageTitle }}</h1>
                </div>
                <div class="filter-mobile-display-reponsive">
                    <a class="filter-button {{ $viewMode === 'grid' ? 'is-on' : '' }}" href="{{ route('ecommerce.shop', array_merge($keep, ['view' => 'grid'])) }}">
                        <img src="{{ $ico }}/mi_filter.svg" alt="grid">
                    </a>
                    <a class="filter-button {{ $viewMode === 'list' ? 'is-on' : '' }}" href="{{ route('ecommerce.shop', array_merge($keep, ['view' => 'list'])) }}">
                        <img src="{{ $ico }}/bi_filter.svg" alt="list">
                    </a>
                </div>
            </div>

            <div class="shop-top-bar">
                <div>Showing {{ $products->count() }} of {{ $products->total() }} result</div>
                <form method="get" class="select-shoing-wrap">
                    @foreach(request()->except(['sort', 'product_count', 'page']) as $key => $value)
                        @if(is_scalar($value) && $value !== '')
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <label>Sort By:
                        <select name="sort" onchange="this.form.submit()">
                            <option value="name_asc" @selected($sort === 'name_asc')>Name (A to Z)</option>
                            <option value="name_desc" @selected($sort === 'name_desc')>Name (Z to A)</option>
                            <option value="price_desc" @selected($sort === 'price_desc')>Price - High to Low</option>
                            <option value="price_asc" @selected($sort === 'price_asc')>Price - Low to High</option>
                        </select>
                    </label>
                    <label>Product Count:
                        <select name="product_count" onchange="this.form.submit()">
                            @foreach([100, 50, 25, 15] as $n)
                                <option value="{{ $n }}" @selected($count === $n)>{{ $n }}</option>
                            @endforeach
                        </select>
                    </label>
                </form>
            </div>

            @include('ecommerce::front.partials.product_grid', [
                'products' => $products,
                'minCards' => 0,
                'variant' => $viewMode === 'grid' ? 'classic' : 'shop',
                'gridClass' => $viewMode === 'grid' ? 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4' : 'product-row',
            ])

            {{ $products->links('ecommerce::front.partials.pagination') }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const page = document.getElementById('gsShopPage');
    const setCollapsed = (on) => {
        page?.classList.toggle('is-filters-collapsed', on);
        try { localStorage.setItem('gs_shop_filters_hidden', on ? '1' : '0'); } catch (e) {}
    };
    if (localStorage.getItem('gs_shop_filters_hidden') === '1') setCollapsed(true);
    document.getElementById('gsCollapseFilters')?.addEventListener('click', () => setCollapsed(true));
    document.getElementById('gsShowFilters')?.addEventListener('click', () => setCollapsed(false));
    const bindSearch = (inputId, itemsSel) => {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('input', () => {
            const q = input.value.toLowerCase().trim();
            document.querySelectorAll(itemsSel).forEach((el) => {
                el.style.display = (!q || (el.dataset.name || '').includes(q)) ? '' : 'none';
            });
        });
    };
    bindSearch('gsCatSearch', '#gsCatList .category-name');
    bindSearch('gsBrandSearch', '#gsBrandList .gs-shop-brand');
})();
</script>
@endpush
