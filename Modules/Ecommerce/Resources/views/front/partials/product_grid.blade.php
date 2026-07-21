@php
    $products = $products ?? collect();
@endphp
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
    @forelse($products as $product)
        @php
            $variation = $product->variations->first();
            $price = $product->ecom_price;
            $compare = $product->ecom_online_price;
            $slug = $product->ecom_slug ?: $product->id;
        @endphp
        <article class="product-card rounded-2xl bg-white border border-slate-200 overflow-hidden">
            <a href="{{ route('ecommerce.product', $slug) }}" class="block aspect-square bg-slate-50">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
            </a>
            <div class="p-4">
                <a href="{{ route('ecommerce.product', $slug) }}" class="font-medium text-sm line-clamp-2 min-h-[2.5rem]">{{ $product->name }}</a>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-semibold">${{ number_format($price, 2) }}</span>
                    @if($compare && $compare > $price)
                        <span class="text-xs text-slate-400 line-through">${{ number_format($compare, 2) }}</span>
                    @endif
                </div>
                @if($variation)
                <form action="{{ route('ecommerce.cart.add') }}" method="post" class="mt-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="variation_id" value="{{ $variation->id }}">
                    <button class="w-full rounded-full bg-ink text-white text-xs py-2.5 hover:bg-accent transition">Add to cart</button>
                </form>
                @endif
            </div>
        </article>
    @empty
        <p class="col-span-full text-slate-500 text-sm">No products yet. Mark products as POS + Ecommerce in the admin.</p>
    @endforelse
</div>
