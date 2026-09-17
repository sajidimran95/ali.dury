@php
    $products = $products ?? collect();
    $gridClass = $gridClass ?? 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4';
@endphp
<div class="{{ $gridClass }}">
    @forelse($products as $product)
        @php
            $variation = $product->variations->first();
            $price = $product->ecom_price;
            $compare = $product->ecom_online_price;
            $slug = $product->ecom_slug ?: $product->id;
            $onSale = $compare && $compare > $price;
            $displayPrice = $onSale ? $price : $price;
            $brandName = optional($product->brand)->name;
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
                    <a href="{{ route('ecommerce.shop', ['brand' => $product->brand_id]) }}"
                       class="text-xs font-semibold uppercase tracking-wide hover:underline" style="color:#2E6A49">{{ $brandName }}</a>
                @endif
                <a href="{{ route('ecommerce.product', $slug) }}"
                   class="font-medium text-sm leading-snug line-clamp-2 hover:text-[#4CAF79] transition-colors" style="color:#1a1a1a">
                    {{ $product->name }}
                </a>
                @if($product->sku)
                    <p class="text-xs" style="color:#999">SKU {{ $product->sku }}</p>
                @endif
                <div class="mt-auto pt-2 flex items-end justify-between gap-2">
                    <div>
                        <span class="text-base font-bold" style="color: {{ $onSale ? '#e53e3e' : '#1a1a1a' }}">${{ number_format($displayPrice, 2) }}</span>
                        @if($onSale)
                            <span class="text-xs line-through ml-1" style="color:#999">${{ number_format($compare, 2) }}</span>
                        @endif
                    </div>
                    @if($variation)
                    <form action="{{ route('ecommerce.cart.add', absolute: false) }}" method="post" class="shrink-0">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="variation_id" value="{{ $variation->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white" style="background:#4CAF79">+ Add</button>
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
