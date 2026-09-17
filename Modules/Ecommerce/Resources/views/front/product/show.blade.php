@extends('ecommerce::front.layouts.store')
@section('title', $product->ecom_seo_title ?: $product->name)
@section('meta_description', $product->ecom_seo_description ?: \Illuminate\Support\Str::limit(strip_tags($product->ecom_short_description ?? $product->ecom_long_description ?? ''), 160))

@section('content')
@php
    $listPrice = (float) ($product->ecom_online_price ?: 0);
    $salePrice = (float) ($product->ecom_discount_price ?: 0);
    $displayPrice = (float) $product->ecom_price;
    $onSale = $salePrice > 0 && $listPrice > 0 && $salePrice < $listPrice;
    if ($onSale) {
        $displayPrice = $salePrice;
    }

    $slug = $product->ecom_slug ?: $product->id;
    $brandName = optional($product->brand)->name;
    $minQty = max(1, (int) ($product->ecom_min_qty ?? 1));
    $enableStock = (bool) $product->enable_stock;
    $inStock = ! $enableStock || $stock > 0;
    $lowStock = $enableStock && $stock > 0 && $stock <= 10;
    $inCart = in_array((int) $product->id, collect($cart_product_ids ?? [])->map(fn ($id) => (int) $id)->all(), true);

    $packSize = null;
    $short = trim(strip_tags((string) ($product->ecom_short_description ?? '')));
    if ($short !== '' && mb_strlen($short) <= 64) {
        $packSize = $short;
    }
    if (! $packSize && $product->unit) {
        $unitLabel = trim((string) ($product->unit->actual_name ?? ''));
        if ($unitLabel !== '' && ! in_array(mb_strtolower($unitLabel), ['pieces', 'piece', 'pcs', 'pc(s)', 'each', 'unit'], true)) {
            $packSize = $unitLabel;
        }
    }
    if (! $packSize && preg_match('/(\d+\s*(?:CT|PK|PCS|COUNT|PACK|BOOKLETS)(?:[^,]{0,24})?)/i', (string) $product->name, $m)) {
        $packSize = trim($m[1]);
    }

    $description = trim(strip_tags((string) ($product->ecom_long_description ?: $product->product_description ?: '')));
    if ($description === '' && $short !== '' && $short !== $packSize) {
        $description = $short;
    }

    $details = collect(preg_split('/\r\n|\n|\r|•/', (string) ($product->product_description ?? '')))
        ->map(fn ($line) => trim($line))
        ->filter(fn ($line) => $line !== '' && mb_strlen($line) < 120)
        ->take(6)
        ->values()
        ->all();
    if (empty($details)) {
        $details = array_values(array_filter([
            $packSize ?: null,
            'Wholesale pricing for licensed retailers',
            $minQty > 1 ? 'Minimum order quantity: '.$minQty : 'Ready to ship to licensed retailers',
        ]));
    }

    $gallery = [];
    if (! empty($product->image_url)) {
        $gallery[] = $product->image_url;
    }
    foreach (($product->ecom_gallery ?? []) as $img) {
        $galleryUrl = function_exists('uploadAsset') ? uploadAsset($img, 'uploads/img') : null;
        if ($galleryUrl) {
            $gallery[] = $galleryUrl;
        }
    }
    $gallery = array_values(array_unique($gallery));
    if (empty($gallery)) {
        $gallery[] = asset('img/default.png');
    }

    $rating = (float) ($rating_avg ?? 0);
    $reviewCount = (int) ($review_count ?? 0);
    if ($reviewCount === 0 && isset($reviews) && $reviews->count()) {
        $reviewCount = $reviews->count();
        $rating = round((float) $reviews->avg('rating'), 1);
    }
    $roundedStars = (int) round($rating);

    $hasVariants = $product->variations->count() > 1
        && ! $product->variations->every(fn ($v) => strtoupper((string) $v->name) === 'DUMMY');

    $msrp = null;
    if ($packSize && preg_match('/(\d+)/', $packSize, $pm) && (int) $pm[1] > 1 && $displayPrice > 0) {
        $msrp = round($displayPrice / (int) $pm[1], 2);
    }
@endphp

<div class="min-h-screen py-8 px-4" style="background:#F4F4F4">
    <div class="max-w-screen-xl mx-auto">
        <nav class="text-sm mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center gap-1.5 flex-wrap">
                <li><a href="{{ url('/') }}" class="hover:underline" style="color:#1a73e8">Home</a></li>
                <li aria-hidden="true" style="color:#999">›</li>
                <li><a href="{{ route('ecommerce.shop') }}" class="hover:underline" style="color:#1a73e8">Products</a></li>
                @if($product->category)
                    <li aria-hidden="true" style="color:#999">›</li>
                    <li><a href="{{ route('ecommerce.category', $product->category->id) }}" class="hover:underline" style="color:#1a73e8">{{ $product->category->name }}</a></li>
                @endif
                <li aria-hidden="true" style="color:#999">›</li>
                <li class="line-clamp-1" style="color:#666">{{ $product->name }}</li>
            </ol>
        </nav>

        <div class="bg-white rounded-2xl border border-[#e8e8e8] p-6 md:p-8 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">
                {{-- Gallery --}}
                <div class="space-y-3">
                    <div class="aspect-square rounded-xl overflow-hidden bg-[#f5f5f5]">
                        <img id="pdpMainImage" src="{{ $gallery[0] }}" alt="{{ $product->name }}"
                             class="w-full h-full object-cover"
                             onerror="this.onerror=null;this.src='{{ asset('img/default.png') }}';">
                    </div>
                    @if(count($gallery) > 1)
                        <div class="flex gap-2" id="pdpThumbs" role="listbox" aria-label="Product images">
                            @foreach($gallery as $i => $url)
                                <button type="button"
                                        class="pdp-thumb w-16 h-16 rounded-lg overflow-hidden border-2 transition-all {{ $i === 0 ? 'border-[#4CAF79]' : 'border-transparent' }}"
                                        data-src="{{ $url }}"
                                        aria-label="View image {{ $i + 1 }}"
                                        aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                                    <img src="{{ $url }}" alt="" class="w-full h-full object-cover"
                                         onerror="this.onerror=null;this.src='{{ asset('img/default.png') }}';">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Buy box --}}
                <div class="flex flex-col">
                    <div class="flex gap-2 mb-3 flex-wrap">
                        @if($product->ecom_is_new_arrival)
                            <span class="text-xs font-bold text-white px-2 py-0.5 rounded" style="background:#4CAF79">NEW</span>
                        @endif
                        @if($onSale)
                            <span class="text-xs font-bold text-white px-2 py-0.5 rounded" style="background:#e53e3e">ON SALE</span>
                        @endif
                        @if(! $inStock)
                            <span class="text-xs font-bold text-white px-2 py-0.5 rounded bg-gray-400">OUT OF STOCK</span>
                        @endif
                    </div>

                    @if($brandName)
                        <a href="{{ route('ecommerce.brand.show', $product->brand_id) }}"
                           class="text-xs font-bold uppercase tracking-widest mb-1 hover:underline" style="color:#2E6A49">{{ $brandName }}</a>
                    @endif

                    <h1 class="font-display text-xl md:text-2xl font-bold mb-2 leading-snug" style="color:#1a1a1a">{{ $product->name }}</h1>

                    @if($product->sku)
                        <p class="text-xs mb-3" style="color:#999">SKU: {{ $product->sku }}</p>
                    @endif

                    <div class="mb-4">
                        @if(! $inStock)
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full" style="background:#fef2f2;color:#dc2626">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span>
                                Out of Stock
                            </span>
                        @elseif($lowStock)
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full" style="background:#fffbeb;color:#d97706">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block"></span>
                                Low Stock — {{ $stock }} left
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full" style="background:#f0fdf4;color:#16a34a">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                                @if($enableStock)
                                    In Stock — {{ $stock }} units available
                                @else
                                    In Stock — ready to ship
                                @endif
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 mb-4">
                        <div class="flex" aria-label="Rated {{ $rating }} out of 5">
                            @for($s = 1; $s <= 5; $s++)
                                <svg width="14" height="14" viewBox="0 0 12 12" fill="{{ $s <= $roundedStars ? '#F59E0B' : '#e0e0e0' }}" aria-hidden="true">
                                    <path d="M6 1l1.4 2.8L10.5 4.3l-2.2 2.2.5 3-2.8-1.5L3.2 9.5l.5-3L1.5 4.3l3.1-.5L6 1z"/>
                                </svg>
                            @endfor
                        </div>
                        <span class="text-sm" style="color:#666">{{ $rating > 0 ? $rating : '0' }} ({{ $reviewCount }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }})</span>
                    </div>

                    <div class="rounded-xl p-4 mb-5" style="background:#f0f9f4;border:1px solid #e8f5ee">
                        <div class="flex items-baseline gap-2 mb-1 flex-wrap">
                            <span class="text-3xl font-bold" style="color:{{ $onSale ? '#e53e3e' : '#2E6A49' }}">${{ number_format($displayPrice, 2) }}</span>
                            @if($onSale)
                                <span class="text-lg line-through" style="color:#999">${{ number_format($listPrice, 2) }}</span>
                            @endif
                            <span class="text-sm" style="color:#666">wholesale</span>
                        </div>
                        <p class="text-xs" style="color:#666">
                            @if($msrp)
                                MSRP: ${{ number_format($msrp, 2) }} per unit &nbsp;·&nbsp;
                            @endif
                            Min. order: {{ $minQty }} {{ $packSize ?: 'unit' }}
                        </p>
                        @if($packSize)
                            <p class="text-xs mt-0.5 font-medium" style="color:#2E6A49">Pack size: {{ $packSize }}</p>
                        @endif
                    </div>

                    @if($description !== '')
                        <p class="text-sm leading-relaxed mb-5" style="color:#444">{{ $description }}</p>
                    @endif

                    @if(! empty($details))
                        <ul class="mb-6 space-y-1.5">
                            @foreach($details as $detail)
                                <li class="flex items-start gap-2 text-sm" style="color:#444">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" class="mt-0.5 shrink-0" aria-hidden="true">
                                        <circle cx="7" cy="7" r="7" fill="#4CAF79"/>
                                        <path d="M4 7l2 2 4-4" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    {{ $detail }}
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if($inStock && $variation && ! $hasVariants)
                        <form action="{{ route('ecommerce.cart.add', absolute: false) }}" method="post" class="space-y-3" id="pdpAddForm">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="variation_id" value="{{ $variation->id }}">

                            <div class="flex items-center gap-3">
                                <label for="qty-input" class="text-sm font-semibold" style="color:#1a1a1a">Quantity:</label>
                                <div class="flex items-center border border-[#e0e0e0] rounded-lg overflow-hidden">
                                    <button type="button" id="qtyMinus" class="px-3 py-2 hover:bg-[#f5f5f5] transition-colors font-bold cursor-pointer" style="color:#2E6A49" aria-label="Decrease quantity">−</button>
                                    <input id="qty-input" type="number" name="quantity" value="{{ $minQty }}" min="{{ $minQty }}"
                                           class="w-14 text-center text-sm font-semibold focus:outline-none" style="color:#1a1a1a" aria-label="Quantity">
                                    <button type="button" id="qtyPlus" class="px-3 py-2 hover:bg-[#f5f5f5] transition-colors font-bold cursor-pointer" style="color:#2E6A49" aria-label="Increase quantity">+</button>
                                </div>
                                <span class="text-xs" style="color:#999">Min: {{ $minQty }}</span>
                            </div>

                            <div class="flex gap-3">
                                <button type="submit"
                                        class="flex-1 py-3 rounded-lg font-semibold text-white text-sm transition-all hover:opacity-90 active:scale-95 cursor-pointer"
                                        style="background:{{ $inCart ? '#2E6A49' : '#4CAF79' }}">
                                    {{ $inCart ? 'Add More to Cart' : 'Add to Cart' }}
                                </button>
                                <button type="submit" name="buy_now" value="1"
                                        class="flex-1 py-3 rounded-lg font-semibold text-white text-sm transition-all hover:opacity-90 active:scale-95 cursor-pointer"
                                        style="background:#2E6A49">
                                    Buy Now
                                </button>
                            </div>

                            <p class="text-xs text-center" style="color:#999" id="pdpSubtotal">
                                Subtotal: <strong style="color:#2E6A49">${{ number_format($displayPrice * $minQty, 2) }}</strong>
                                for <span id="pdpSubtotalQty">{{ $minQty }}</span>
                                <span id="pdpSubtotalUnit">{{ $minQty === 1 ? 'unit' : 'units' }}</span>
                            </p>
                        </form>
                    @elseif($inStock && $hasVariants)
                        <p class="text-sm font-medium" style="color:#666">Select variants and quantities below to add to cart.</p>
                    @elseif(! $inStock)
                        <div class="rounded-lg p-4 text-center border border-[#e0e0e0]">
                            <p class="font-semibold text-sm mb-2" style="color:#666">Currently Out of Stock</p>
                            <form method="post" action="{{ route('ecommerce.stock_alert') }}" class="inline-flex flex-col sm:flex-row gap-2 items-stretch sm:items-center justify-center">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="email" name="email" required placeholder="Email for restock alert"
                                       class="border border-[#e0e0e0] rounded-lg px-3 py-2 text-sm min-w-[200px]"
                                       value="{{ optional(auth('customer')->user())->email }}">
                                <button type="submit" class="text-sm font-semibold px-5 py-2 rounded-lg text-white cursor-pointer" style="background:#4CAF79">
                                    Notify Me When Available
                                </button>
                            </form>
                        </div>
                    @endif

                    @if($brandName)
                        <div class="mt-6 pt-5 border-t border-[#f0f0f0] flex items-center gap-3">
                            <img src="{{ $brand_image ?: asset('img/default.png') }}" alt="{{ $brandName }}"
                                 class="w-12 h-12 rounded-lg object-cover bg-[#f0f0f0]"
                                 onerror="this.onerror=null;this.src='{{ asset('img/default.png') }}';">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide" style="color:#999">Brand</p>
                                <a href="{{ route('ecommerce.brand.show', $product->brand_id) }}"
                                   class="font-bold text-sm hover:underline" style="color:#2E6A49">{{ $brandName }} — View all products →</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($inStock && $hasVariants)
            <div class="bg-white rounded-2xl border border-[#e8e8e8] p-6 md:p-8 mb-8">
                <div class="flex items-start justify-between gap-4 mb-2">
                    <div>
                        <h2 class="font-display text-xl font-bold" style="color:#1a1a1a">Bulk Order by Variant</h2>
                        <p class="text-sm mt-1" style="color:#666">Set quantities for each variant, then add all selections to cart at once.</p>
                    </div>
                    <span class="shrink-0 text-xs font-bold px-3 py-1 rounded-full" style="background:#e8f5ee;color:#2E6A49">
                        {{ $product->variations->count() }} variant{{ $product->variations->count() === 1 ? '' : 's' }}
                    </span>
                </div>

                <div class="hidden sm:grid grid-cols-[64px_1fr_100px_172px_80px] gap-3 px-4 mb-2 text-xs font-semibold uppercase tracking-wider" style="color:#999">
                    <span>Photo</span>
                    <span>Variant</span>
                    <span>Stock</span>
                    <span class="text-center">Quantity</span>
                    <span class="text-right">Price</span>
                </div>

                <form action="{{ route('ecommerce.cart.add', absolute: false) }}" method="post" id="bulkVariantForm" class="space-y-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    @foreach($product->variations as $v)
                        @php
                            $vStock = $variant_stocks[$v->id] ?? null;
                            $vPrice = (float) ($v->sell_price_inc_tax ?: $displayPrice);
                            $vOut = $enableStock && $vStock !== null && $vStock <= 0;
                        @endphp
                        <div class="flex flex-col sm:flex-row gap-3 p-4 rounded-xl border transition-all border-[#e8e8e8]">
                            <div class="shrink-0">
                                <img src="{{ $product->image_url }}" alt="" class="w-16 h-16 object-cover rounded-lg bg-[#f5f5f5]">
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-sm mb-0.5" style="color:#1a1a1a">{{ $v->name }}</div>
                                <p class="text-xs" style="color:#999">SKU: {{ $v->sub_sku ?: $product->sku }}</p>
                            </div>
                            <div class="flex items-center shrink-0">
                                @if($vOut)
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:#fef2f2;color:#dc2626">Out of Stock</span>
                                @elseif($enableStock && $vStock !== null && $vStock <= 10)
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:#fffbeb;color:#d97706">Low — {{ $vStock }} left</span>
                                @else
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:#f0fdf4;color:#16a34a">In Stock</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <div class="flex items-center border rounded-lg overflow-hidden {{ $vOut ? 'opacity-40 pointer-events-none' : '' }}" style="border-color:#e0e0e0">
                                    <button type="button" class="bulk-minus px-2.5 py-1.5 hover:bg-[#f5f5f5] transition-colors text-sm font-bold cursor-pointer" style="color:#2E6A49" data-target="bulk-qty-{{ $v->id }}">−</button>
                                    <input type="number" name="bulk_qty[{{ $v->id }}]" id="bulk-qty-{{ $v->id }}" value="0" min="0"
                                           class="bulk-qty w-12 text-center text-sm font-semibold focus:outline-none" style="color:#1a1a1a" {{ $vOut ? 'disabled' : '' }}>
                                    <button type="button" class="bulk-plus px-2.5 py-1.5 hover:bg-[#f5f5f5] transition-colors text-sm font-bold cursor-pointer" style="color:#2E6A49" data-target="bulk-qty-{{ $v->id }}">+</button>
                                </div>
                                <div class="w-20 text-right">
                                    <div class="font-bold text-sm" style="color:#2E6A49">${{ number_format($vPrice, 2) }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </form>

                <div class="mt-6 pt-5 border-t border-[#f0f0f0] flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold" style="color:#1a1a1a" id="bulkSummary">Set quantities above to build your bulk order.</p>
                        <p class="text-xs mt-0.5" style="color:#999">Lines with qty 0 are skipped.</p>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" id="bulkClear" class="px-4 py-2.5 rounded-lg text-sm font-semibold border border-[#e0e0e0] transition-all hover:border-red-300 hover:text-red-500 cursor-pointer">Clear All</button>
                        <button type="button" id="bulkAdd" class="px-6 py-2.5 rounded-lg text-sm font-bold text-white transition-all hover:opacity-90 active:scale-95 cursor-pointer flex items-center gap-2" style="background:#4CAF79">
                            Add Selected to Cart
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if($related->count())
            <section class="mb-8" aria-labelledby="related-heading">
                <h2 id="related-heading" class="font-display text-xl font-bold mb-6" style="color:#1a1a1a">Related Products</h2>
                @include('ecommerce::front.partials.product_grid', [
                    'products' => $related,
                    'gridClass' => 'grid grid-cols-2 md:grid-cols-4 gap-4',
                ])
            </section>
        @endif
    </div>
</div>

@push('scripts')
<script>
(function () {
    const unitPrice = {{ json_encode((float) $displayPrice) }};
    const minQty = {{ (int) $minQty }};
    const input = document.getElementById('qty-input');
    const minus = document.getElementById('qtyMinus');
    const plus = document.getElementById('qtyPlus');
    const subQty = document.getElementById('pdpSubtotalQty');
    const subUnit = document.getElementById('pdpSubtotalUnit');
    const subtotalEl = document.getElementById('pdpSubtotal');

    function setQty(v) {
        if (!input) return;
        const n = Math.max(minQty, parseInt(v, 10) || minQty);
        input.value = n;
        if (subQty) subQty.textContent = n;
        if (subUnit) subUnit.textContent = n === 1 ? 'unit' : 'units';
        if (subtotalEl) {
            const strong = subtotalEl.querySelector('strong');
            if (strong) strong.textContent = '$' + (unitPrice * n).toFixed(2);
        }
    }

    minus?.addEventListener('click', () => setQty((parseInt(input.value, 10) || minQty) - 1));
    plus?.addEventListener('click', () => setQty((parseInt(input.value, 10) || minQty) + 1));
    input?.addEventListener('change', () => setQty(input.value));

    const main = document.getElementById('pdpMainImage');
    document.querySelectorAll('.pdp-thumb').forEach(btn => {
        btn.addEventListener('click', () => {
            const src = btn.getAttribute('data-src');
            if (main && src) main.src = src;
            document.querySelectorAll('.pdp-thumb').forEach(b => {
                b.classList.toggle('border-[#4CAF79]', b === btn);
                b.classList.toggle('border-transparent', b !== btn);
                b.setAttribute('aria-selected', b === btn ? 'true' : 'false');
            });
        });
    });

    document.querySelectorAll('.bulk-minus').forEach(btn => {
        btn.addEventListener('click', () => {
            const el = document.getElementById(btn.dataset.target);
            if (!el) return;
            el.value = Math.max(0, (parseInt(el.value, 10) || 0) - 1);
            updateBulkSummary();
        });
    });
    document.querySelectorAll('.bulk-plus').forEach(btn => {
        btn.addEventListener('click', () => {
            const el = document.getElementById(btn.dataset.target);
            if (!el) return;
            el.value = (parseInt(el.value, 10) || 0) + 1;
            updateBulkSummary();
        });
    });
    document.querySelectorAll('.bulk-qty').forEach(el => el.addEventListener('change', updateBulkSummary));

    function updateBulkSummary() {
        const summary = document.getElementById('bulkSummary');
        if (!summary) return;
        let lines = 0, units = 0;
        document.querySelectorAll('.bulk-qty').forEach(el => {
            const q = parseInt(el.value, 10) || 0;
            if (q > 0) { lines++; units += q; }
        });
        summary.textContent = units
            ? `${units} unit${units === 1 ? '' : 's'} across ${lines} variant${lines === 1 ? '' : 's'} selected`
            : 'Set quantities above to build your bulk order.';
    }

    document.getElementById('bulkClear')?.addEventListener('click', () => {
        document.querySelectorAll('.bulk-qty').forEach(el => { el.value = 0; });
        updateBulkSummary();
    });

    document.getElementById('bulkAdd')?.addEventListener('click', async () => {
        const token = document.querySelector('#bulkVariantForm input[name="_token"]')?.value;
        const productId = document.querySelector('#bulkVariantForm input[name="product_id"]')?.value;
        const rows = [...document.querySelectorAll('.bulk-qty')];
        const selected = rows
            .map(el => ({ variation_id: el.id.replace('bulk-qty-', ''), quantity: parseInt(el.value, 10) || 0 }))
            .filter(r => r.quantity > 0);
        if (!selected.length) {
            alert('Set at least one variant quantity first.');
            return;
        }
        for (const row of selected) {
            const body = new FormData();
            body.append('_token', token);
            body.append('product_id', productId);
            body.append('variation_id', row.variation_id);
            body.append('quantity', row.quantity);
            await fetch(@json(route('ecommerce.cart.add', absolute: false)), {
                method: 'POST',
                body,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
        }
        window.location.href = @json(route('ecommerce.cart'));
    });
})();
</script>
@endpush
@endsection
