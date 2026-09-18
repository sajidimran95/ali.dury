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
            $pack = $product->ecom_short_description ?: ($product->product_custom_field1 ? $product->product_custom_field1.' / display' : '1 display');
        @endphp
        <article class="gc-card product-card flex flex-col bg-white">
            <a href="{{ route('ecommerce.product', $slug) }}" class="block relative aspect-[4/3] bg-mist overflow-hidden">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy"
                     class="w-full h-full object-cover"
                     onerror="this.onerror=null;this.src='{{ asset('img/default.png') }}';">
                @if($product->ecom_is_new_arrival)
                    <span class="absolute top-2 left-2 rounded bg-[#4caf79] text-white text-[10px] font-bold uppercase px-2 py-1">New</span>
                @elseif($onSale)
                    <span class="absolute top-2 left-2 rounded bg-[#2e6a49] text-white text-[10px] font-bold uppercase px-2 py-1">Sale</span>
                @endif
            </a>
            <div class="p-3 md:p-3.5 flex flex-col flex-1">
                @if(optional($product->brand)->name)
                    <a href="{{ route('ecommerce.shop', ['brand' => $product->brand_id]) }}" class="text-[11px] font-bold tracking-wide text-[#2e6a49] uppercase">{{ $product->brand->name }}</a>
                @endif
                <a href="{{ route('ecommerce.product', $slug) }}" class="font-semibold text-sm leading-snug line-clamp-2 min-h-[2.5rem] mt-0.5 hover:text-brand">
                    {{ $product->name }}
                </a>
                <div class="mt-1 text-[11px] text-black/45">{{ $pack }}</div>
                <div class="mt-1 text-[11px] text-black/40">Min. qty: 1 display</div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-extrabold tabular-nums text-ink">${{ number_format($price, 2) }}</span>
                    @if($onSale)
                        <span class="text-xs text-black/35 line-through tabular-nums">${{ number_format($compare, 2) }}</span>
                    @endif
                </div>
                @if($variation)
                <form action="{{ route('ecommerce.cart.add', absolute: false) }}" method="post" class="mt-auto pt-3 product-card-form">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="variation_id" value="{{ $variation->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="ecom-add-cart w-full rounded-lg bg-[#4caf79] text-[#1a1a1a] text-xs font-bold py-2.5 hover:bg-[#3d9b68]"
                            aria-label="Add {{ $product->name }} to cart">Add to Cart</button>
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
