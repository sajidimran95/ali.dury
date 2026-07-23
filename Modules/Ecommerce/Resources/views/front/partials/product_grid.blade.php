@php
    $products = $products ?? collect();
    $gridClass = $gridClass ?? 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4';
@endphp
<div class="{{ $gridClass }}">
    @forelse($products as $product)
        @php
            $variation = $product->variations->first();
            $price = $product->ecom_price;
            $compare = $product->ecom_online_price;
            $slug = $product->ecom_slug ?: $product->id;
            $onSale = $compare && $compare > $price;
        @endphp
        <article class="product-card group rounded-xl bg-white border border-line overflow-hidden flex flex-col">
            <a href="{{ route('ecommerce.product', $slug) }}" class="block relative aspect-square bg-mist overflow-hidden">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy"
                     class="w-full h-full object-cover transition duration-500 group-hover:scale-[1.04]"
                     onerror="this.onerror=null;this.src='{{ asset('img/default.png') }}';">
                @if($onSale)
                    <span class="absolute top-2 left-2 rounded bg-brand text-white text-[10px] font-bold uppercase px-2 py-1">${{ number_format($price, 2) }}</span>
                @elseif($product->ecom_is_new_arrival)
                    <span class="absolute top-2 left-2 rounded bg-ink text-white text-[10px] font-bold uppercase px-2 py-1">New</span>
                @else
                    <span class="absolute top-2 left-2 rounded bg-brand text-white text-[10px] font-bold px-2 py-1">${{ number_format($price, 2) }}</span>
                @endif
            </a>
            <div class="p-3 md:p-3.5 flex flex-col flex-1">
                <a href="{{ route('ecommerce.product', $slug) }}" class="font-semibold text-sm leading-snug line-clamp-2 min-h-[2.5rem] hover:text-brand">
                    {{ $product->name }}
                </a>
                <div class="mt-1 text-[11px] text-black/40">SKU {{ $product->sku }}</div>
                <div class="mt-1.5 flex items-baseline gap-2">
                    <span class="font-extrabold tabular-nums text-brand">${{ number_format($price, 2) }}</span>
                    @if($onSale)
                        <span class="text-xs text-black/35 line-through tabular-nums">${{ number_format($compare, 2) }}</span>
                    @endif
                </div>

                @if($variation)
                <form action="{{ route('ecommerce.cart.add', absolute: false) }}" method="post" class="mt-auto pt-3 product-card-form">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="variation_id" value="{{ $variation->id }}">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <div class="inline-flex items-center justify-between sm:justify-start border border-line rounded-md bg-white w-full sm:w-auto shrink-0">
                            <button type="button" class="pc-qty-minus h-10 w-10 sm:h-9 sm:w-8 text-ink text-lg sm:text-base leading-none hover:bg-mist" aria-label="Decrease">−</button>
                            <input type="number" name="quantity" value="1" min="1" step="1"
                                   class="pc-qty-input w-12 sm:w-8 h-10 sm:h-9 border-0 text-center text-sm font-semibold tabular-nums outline-none appearance-none [-moz-appearance:textfield]">
                            <button type="button" class="pc-qty-plus h-10 w-10 sm:h-9 sm:w-8 text-ink text-lg sm:text-base leading-none hover:bg-mist" aria-label="Increase">+</button>
                        </div>
                        <button type="submit" class="w-full sm:flex-1 rounded-md bg-ink text-white text-xs font-bold py-2.5 sm:py-2.5 hover:bg-black transition">
                            Add to Cart
                        </button>
                    </div>
                </form>
                @endif
            </div>
        </article>
    @empty
        <div class="col-span-full rounded-xl bg-white border border-dashed border-line p-10 text-center text-sm text-black/50">
            No products found in this catalog.
        </div>
    @endforelse
</div>
