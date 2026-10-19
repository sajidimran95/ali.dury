@php
    $products = $products ?? collect();
    $variant = $variant ?? 'classic';
    $gridClass = $gridClass ?? ($variant === 'shop' ? 'gs-shop-list' : 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4');
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
        @endphp
        @if($variant === 'shop')
        @php
            $catLabel = strtoupper((string) optional($product->category)->name);
            if (strlen($catLabel) > 9) {
                $catLabel = rtrim(substr($catLabel, 0, 8)) . '...';
            }
        @endphp
        <div class="gutter-row">
            <div class="card-old-inactive">
                <a href="{{ route('ecommerce.product', $slug) }}" class="old-img">
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy"
                         onerror="this.onerror=null;this.src='{{ \Modules\Ecommerce\Support\Storefront::placeholder() }}';">
                </a>
                <div class="card-old-side-inactive">
                    <span class="card-old-brach-name">{{ $catLabel }}</span>
                    <a href="{{ route('ecommerce.product', $slug) }}" class="product-name-old-card">{{ $product->name }}</a>
                    <div class="shop-page-unit">Unit: Piece</div>
                    @if($variation)
                    <form action="{{ route('ecommerce.cart.add', absolute: false) }}" method="post" class="product-card-form">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="variation_id" value="{{ $variation->id }}">
                        <div class="increment-inactive">
                            <button type="button" class="pc-qty-minus" aria-label="Decrease">
                                <img src="https://gsdistributor.com/media/icons/minus-icon.svg" class="increment-class" alt="" draggable="false">
                            </button>
                            <input type="number" name="quantity" value="00" min="0" placeholder="00" class="new-input-card-inactive pc-qty-input">
                            <button type="button" class="pc-qty-plus" aria-label="Increase">
                                <img src="https://gsdistributor.com/media/icons/plus-icon.svg" class="increment-class" alt="" draggable="false">
                            </button>
                        </div>
                        <button type="submit" class="add-to-cart-button-inactive ecom-add-cart">Add to Cart</button>
                    </form>
                    @else
                    <button type="button" class="out-of-stock-shop" disabled>Out of Stock</button>
                    @endif
                    <div class="shop-card-sku-row">{{ $product->sku }}</div>
                </div>
            </div>
        </div>
        @else
        <article class="pc-classic">
            <a href="{{ route('ecommerce.product', $slug) }}" class="pc-classic__image-wrap">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="pc-classic__image pc-classic__image--contain" loading="lazy"
                     onerror="this.onerror=null;this.src='{{ \Modules\Ecommerce\Support\Storefront::placeholder() }}';">
            </a>
            <div class="pc-classic__badges">
                @if($product->ecom_is_new_arrival)
                    <span class="pc-classic__badge pc-classic__badge--primary">New</span>
                @elseif($onSale)
                    <span class="pc-classic__badge pc-classic__badge--primary">Sale</span>
                @endif
            </div>
            <a href="{{ route('ecommerce.product', $slug) }}" class="pc-classic__name">{{ $product->name }}</a>
            <div class="pc-classic__price-block">
                @if($showPrices)
                    <div class="pc-classic__price">${{ number_format($price, 2) }}</div>
                @else
                    <a href="{{ route('ecommerce.login') }}" class="pc-classic__price" style="font-size:14px">Login to see price</a>
                @endif
            </div>
            @if($variation)
            <form action="{{ route('ecommerce.cart.add', absolute: false) }}" method="post" class="pc-classic__controls product-card-form">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="variation_id" value="{{ $variation->id }}">
                <div class="pc-classic__stepper">
                    <button type="button" class="pc-classic__stepper-btn pc-qty-minus" aria-label="Decrease">−</button>
                    <input type="number" name="quantity" value="1" min="1" class="pc-classic__stepper-input pc-qty-input">
                    <button type="button" class="pc-classic__stepper-btn pc-qty-plus" aria-label="Increase">+</button>
                </div>
                <button type="submit" class="pc-classic__btn ecom-add-cart">Add to Cart</button>
            </form>
            @endif
        </article>
        @endif
    @endforeach
    @if($products->isEmpty() && $minCards > 0)
        @for($i = 0; $i < $minCards; $i++)
        <article class="pc-classic">
            <a href="{{ route('ecommerce.shop') }}" class="pc-classic__image-wrap">
                <img src="{{ asset('img/default.png') }}" alt="" class="pc-classic__image">
            </a>
            <a href="{{ route('ecommerce.shop') }}" class="pc-classic__name">Product</a>
        </article>
        @endfor
    @endif
</div>
