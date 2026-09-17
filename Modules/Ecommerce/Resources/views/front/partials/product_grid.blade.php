@php
    $products = $products ?? collect();
    $gridClass = $gridClass ?? 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4';
    $cartProductIds = collect($cart_product_ids ?? [])->map(fn ($id) => (int) $id)->all();

    $reviewStats = collect();
    if ($products->isNotEmpty() && \Illuminate\Support\Facades\Schema::hasTable('ecom_reviews')) {
        $reviewStats = \Illuminate\Support\Facades\DB::table('ecom_reviews')
            ->whereIn('product_id', $products->pluck('id'))
            ->where('status', 'approved')
            ->groupBy('product_id')
            ->selectRaw('product_id, AVG(rating) as avg_rating, COUNT(*) as review_count')
            ->get()
            ->keyBy('product_id');
    }
@endphp
<div class="{{ $gridClass }}">
    @forelse($products as $product)
        @php
            $variation = $product->variations->first();
            $listPrice = (float) ($product->ecom_online_price ?: 0);
            $salePrice = (float) ($product->ecom_discount_price ?: 0);
            $displayPrice = (float) $product->ecom_price;
            $onSale = $salePrice > 0 && $listPrice > 0 && $salePrice < $listPrice;
            if ($onSale) {
                $displayPrice = $salePrice;
            }
            $slug = $product->ecom_slug ?: $product->id;
            $brandName = optional($product->brand)->name;
            $inCart = in_array((int) $product->id, $cartProductIds, true);
            $minQty = max(1, (int) ($product->ecom_min_qty ?? 1));

            $packSize = null;
            $short = trim(strip_tags((string) ($product->ecom_short_description ?? '')));
            if ($short !== '' && mb_strlen($short) <= 48) {
                $packSize = $short;
            }
            if (! $packSize && $product->relationLoaded('unit') && $product->unit) {
                $unitLabel = trim((string) ($product->unit->actual_name ?? ''));
                if ($unitLabel !== '' && ! in_array(mb_strtolower($unitLabel), ['pieces', 'piece', 'pcs', 'pc(s)', 'each', 'unit'], true)) {
                    $packSize = $unitLabel;
                }
            }
            if (! $packSize && preg_match('/(\d+\s*(?:CT|PK|PCS|COUNT|PACK|BOOKLETS)(?:[^,]{0,24})?)/i', (string) $product->name, $m)) {
                $packSize = trim($m[1]);
            }

            $stats = $reviewStats->get($product->id);
            $rating = $stats ? round((float) $stats->avg_rating, 1) : 0.0;
            $reviewCount = $stats ? (int) $stats->review_count : 0;
            $roundedStars = (int) round($rating);
        @endphp
        <article class="tile-hover bg-white rounded-xl overflow-hidden border border-[#e8e8e8] flex flex-col h-full group">
            <a href="{{ route('ecommerce.product', $slug) }}" class="block relative overflow-hidden" aria-label="View {{ $product->name }}">
                <div class="w-full bg-[#f0f0f0] overflow-hidden h-48">
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy"
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                         onerror="this.onerror=null;this.src='{{ asset('img/default.png') }}';">
                </div>
                <div class="absolute top-2 left-2 flex flex-col gap-1">
                    @if($product->ecom_is_new_arrival)
                        <span class="text-xs font-bold text-white px-2 py-0.5 rounded" style="background:#4CAF79">NEW</span>
                    @endif
                    @if($onSale)
                        <span class="text-xs font-bold text-white px-2 py-0.5 rounded" style="background:#e53e3e">SALE</span>
                    @endif
                </div>
            </a>
            <div class="p-3 flex flex-col flex-1 gap-1">
                @if($brandName)
                    <a href="{{ route('ecommerce.brand.show', $product->brand_id) }}"
                       class="text-xs font-semibold uppercase tracking-wide hover:underline" style="color:#2E6A49">{{ $brandName }}</a>
                @endif
                <a href="{{ route('ecommerce.product', $slug) }}"
                   class="font-medium text-sm leading-snug line-clamp-2 hover:text-[#4CAF79] transition-colors" style="color:#1a1a1a">
                    {{ $product->name }}
                </a>
                @if($packSize)
                    <p class="text-xs" style="color:#999">{{ $packSize }}</p>
                @endif
                <div class="flex items-center gap-1 mt-0.5">
                    <div class="flex" aria-label="{{ $rating }} out of 5 stars">
                        @for($s = 1; $s <= 5; $s++)
                            <svg width="10" height="10" viewBox="0 0 12 12" fill="{{ $s <= $roundedStars ? '#F59E0B' : '#ddd' }}" aria-hidden="true">
                                <path d="M6 1l1.4 2.8L10.5 4.3l-2.2 2.2.5 3-2.8-1.5L3.2 9.5l.5-3L1.5 4.3l3.1-.5L6 1z"/>
                            </svg>
                        @endfor
                    </div>
                    <span class="text-xs" style="color:#999">({{ $reviewCount }})</span>
                </div>
                <div class="mt-auto pt-2 flex items-end justify-between gap-2">
                    <div>
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-base font-bold" style="color:{{ $onSale ? '#e53e3e' : '#1a1a1a' }}">${{ number_format($displayPrice, 2) }}</span>
                            @if($onSale)
                                <span class="text-xs line-through" style="color:#999">${{ number_format($listPrice, 2) }}</span>
                            @endif
                        </div>
                        <p class="text-xs" style="color:#999">Min. qty: {{ $minQty }} {{ $packSize ? 'display' : 'unit' }}</p>
                    </div>
                    @if($variation)
                    <form action="{{ route('ecommerce.cart.add', absolute: false) }}" method="post" class="shrink-0 product-card-add">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="variation_id" value="{{ $variation->id }}">
                        <input type="hidden" name="quantity" value="{{ $minQty }}">
                        <button type="submit"
                                class="shrink-0 px-3 py-1.5 rounded-lg text-xs font-semibold text-white cursor-pointer transition-all duration-200 active:scale-95 {{ $inCart ? '' : 'hover:opacity-90' }}"
                                style="background:{{ $inCart ? '#2E6A49' : '#4CAF79' }}"
                                aria-label="{{ $inCart ? $product->name.' is in cart' : 'Add '.$product->name.' to cart' }}">
                            {{ $inCart ? '✓ In Cart' : '+ Add' }}
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </article>
    @empty
        <div class="col-span-full rounded-xl bg-white border border-dashed border-[#e8e8e8] p-10 text-center text-sm" style="color:#666">
            No products found in this catalog.
        </div>
    @endforelse
</div>
