@php
    $products = $products ?? collect();
    $gridClass = $gridClass ?? 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4';
    $minCards = (int) ($minCards ?? 8);
    $showPrices = auth('customer')->check();
@endphp
<div class="{{ $gridClass }}">
    @foreach($products as $product)
        @php
            $variation = $product->variations->first();
            $price = $product->ecom_price;
            $compare = $product->ecom_online_price;
            $slug = $product->ecom_slug ?: $product->id;
            $onSale = $compare && $compare > $price;
            $caseQty = $product->ecom_short_description
                ?: ($product->product_custom_field1 ?: null);
            $caseLabel = $caseQty ? strtoupper((string) $caseQty) : 'CASE: 1CT';
            if ($caseQty && ! str_contains(strtoupper((string) $caseQty), 'CASE')) {
                $caseLabel = 'CASE: '.strtoupper((string) $caseQty);
            }
        @endphp
        <article class="wn-pc product-card">
            <a href="{{ route('ecommerce.product', $slug) }}" class="wn-pc__img">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy"
                     onerror="this.onerror=null;this.src='{{ asset('img/default.png') }}';">
                @if($product->ecom_is_new_arrival)
                    <span class="wn-pc__new">NEW</span>
                @elseif($onSale)
                    <span class="wn-pc__new" style="background:#ff2d95;color:#fff">SALE</span>
                @endif
            </a>
            <div class="wn-pc__body">
                <a href="{{ route('ecommerce.product', $slug) }}" class="wn-pc__name">{{ $product->name }}</a>
                <div class="wn-pc__meta">{{ $caseLabel }}</div>
                <div class="wn-pc__stock">✓ IN STOCK</div>
                @if($showPrices)
                    <div class="wn-pc__price is-price">${{ number_format($price, 2) }}</div>
                @else
                    <a href="{{ route('ecommerce.login') }}" class="wn-pc__price">LOGIN TO SEE PRICE</a>
                @endif
                @if($variation)
                <form action="{{ route('ecommerce.cart.add', absolute: false) }}" method="post" class="wn-pc__row product-card-form">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="variation_id" value="{{ $variation->id }}">
                    <div class="wn-qty">
                        <button type="button" class="pc-qty-minus" aria-label="Decrease">−</button>
                        <input type="number" name="quantity" value="1" min="1" class="pc-qty-input">
                        <button type="button" class="pc-qty-plus" aria-label="Increase">+</button>
                    </div>
                    <button type="submit" class="wn-add ecom-add-cart">ADD CASE</button>
                </form>
                @else
                    <a href="{{ route('ecommerce.shop') }}" class="wn-add ecom-add-cart mt-auto">ADD CASE</a>
                @endif
            </div>
        </article>
    @endforeach
    @if($products->isEmpty())
        @for($i = 0; $i < $minCards; $i++)
        <article class="wn-pc product-card">
            <a href="{{ route('ecommerce.shop') }}" class="wn-pc__img">
                <img src="{{ asset('img/default.png') }}" alt="">
            </a>
            <div class="wn-pc__body">
                <a href="{{ route('ecommerce.shop') }}" class="wn-pc__name">Product</a>
                <div class="wn-pc__meta">CASE: 1CT</div>
                <div class="wn-pc__stock">✓ IN STOCK</div>
                <a href="{{ route('ecommerce.login') }}" class="wn-pc__price">LOGIN TO SEE PRICE</a>
                <a href="{{ route('ecommerce.shop') }}" class="wn-add ecom-add-cart mt-3">ADD CASE</a>
            </div>
        </article>
        @endfor
    @endif
</div>
