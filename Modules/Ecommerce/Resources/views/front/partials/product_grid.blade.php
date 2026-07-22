@php
    $products = $products ?? collect();
@endphp
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-5">
    @forelse($products as $product)
        @php
            $variation = $product->variations->first();
            $price = $product->ecom_price;
            $compare = $product->ecom_online_price;
            $slug = $product->ecom_slug ?: $product->id;
            $onSale = $compare && $compare > $price;
        @endphp
        <article class="product-card group rounded-2xl bg-white border border-black/5 overflow-hidden">
            <a href="{{ route('ecommerce.product', $slug) }}" class="block relative aspect-[4/5] bg-mist overflow-hidden">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy"
                     class="w-full h-full object-cover transition duration-500 group-hover:scale-[1.04]">
                @if($onSale)
                    <span class="absolute top-3 left-3 rounded-full bg-accent text-white text-[10px] font-semibold tracking-wide uppercase px-2.5 py-1">Sale</span>
                @elseif($product->ecom_is_new_arrival)
                    <span class="absolute top-3 left-3 rounded-full bg-ink text-white text-[10px] font-semibold tracking-wide uppercase px-2.5 py-1">New</span>
                @endif
                <span class="absolute inset-x-3 bottom-3 opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition hidden md:block">
                    <span class="block text-center rounded-full bg-white/95 text-ink text-xs font-semibold py-2.5 shadow-soft">Quick view</span>
                </span>
            </a>
            <div class="p-3.5 md:p-4">
                @if(optional($product->category)->name)
                    <div class="text-[10px] uppercase tracking-[0.16em] text-black/35 mb-1">{{ $product->category->name }}</div>
                @endif
                <a href="{{ route('ecommerce.product', $slug) }}" class="font-medium text-sm leading-snug line-clamp-2 min-h-[2.5rem] hover:text-accent">
                    {{ $product->name }}
                </a>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-semibold tabular-nums">${{ number_format($price, 2) }}</span>
                    @if($onSale)
                        <span class="text-xs text-black/35 line-through tabular-nums">${{ number_format($compare, 2) }}</span>
                    @endif
                </div>
                @if($variation)
                <form action="{{ route('ecommerce.cart.add', absolute: false) }}" method="post" class="mt-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="variation_id" value="{{ $variation->id }}">
                    <button class="w-full rounded-full bg-ink text-white text-xs font-semibold py-2.5 hover:bg-accent transition">
                        Add to cart
                    </button>
                </form>
                @endif
            </div>
        </article>
    @empty
        <div class="col-span-full rounded-2xl bg-white border border-dashed border-black/10 p-10 text-center text-sm text-black/50">
            No products yet. Set product Sales Channel to POS + Ecommerce in admin.
        </div>
    @endforelse
</div>
