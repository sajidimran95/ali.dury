@extends('ecommerce::front.layouts.store')
@section('title', 'Cart')
@section('content')
@php
    $empty = $cart->items->isEmpty();
@endphp
<div class="cart-main-area">
    <div class="shopping-row">
        <div class="product-section">
            @if($empty)
                <div class="cart-empty-wrap">
                    <div class="empty-cart">
                        <img src="{{ asset('img/gs-empty-cart.svg') }}" alt="">
                    </div>
                    <span class="cart-para">Cart is empty!</span>
                    <span class="cart-text">Look like you have no items in your shopping cart.</span>
                    <a href="{{ route('ecommerce.shop') }}" style="text-decoration:none;display:flex;justify-content:center">
                        <button type="button" class="goto-shopBtn"><span class="btn-label"> Go to Shop</span></button>
                    </a>
                </div>
            @else
                <h1 class="shopping-cart-heading">Cart</h1>
                <div class="cart-table-content product-table">
                    <table>
                        <thead>
                            <tr>
                                <th class="product-thumbnail">Product</th>
                                <th class="product-name"></th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cart->items as $item)
                                @php $product = $item->product; @endphp
                                <tr>
                                    <td class="product-thumbnail">
                                        <a href="{{ $product ? route('ecommerce.product', $product->ecom_slug ?: $product->id) : '#' }}">
                                            <img src="{{ $product->image_url ?? \Modules\Ecommerce\Support\Storefront::placeholder() }}" alt=""
                                                 onerror="this.onerror=null;this.src='{{ \Modules\Ecommerce\Support\Storefront::placeholder() }}';">
                                        </a>
                                    </td>
                                    <td class="product-name">
                                        <a class="pro-name-cart" href="{{ $product ? route('ecommerce.product', $product->ecom_slug ?: $product->id) : '#' }}">{{ optional($product)->name }}</a>
                                        <span class="cart-sku">{{ optional($product)->sku }}</span>
                                    </td>
                                    <td class="product-price-cart">
                                        <span>${{ number_format($item->unit_price, 2) }}</span>
                                    </td>
                                    <td class="product-quantity">
                                        <form action="{{ route('ecommerce.cart.update', absolute: false) }}" method="post" class="cart-qty-form">
                                            @csrf
                                            <input type="hidden" name="item_id" value="{{ $item->id }}">
                                            <div class="cart-plus-minus">
                                                <button type="button" class="dec qtybutton qty-minus" aria-label="Decrease">−</button>
                                                <input type="number" name="quantity" value="{{ (int) $item->quantity }}" min="1" class="cart-plus-minus-box qty-input">
                                                <button type="button" class="inc qtybutton qty-plus" aria-label="Increase">+</button>
                                            </div>
                                        </form>
                                    </td>
                                    <td class="product-subtotal">
                                        ${{ number_format($item->quantity * $item->unit_price, 2) }}
                                    </td>
                                    <td class="product-remove">
                                        <form action="{{ route('ecommerce.cart.remove', absolute: false) }}" method="post">
                                            @csrf
                                            <input type="hidden" name="item_id" value="{{ $item->id }}">
                                            <button type="submit" aria-label="Remove">×</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <aside class="quick-cart">
            <div class="cart-head">Summary</div>
            <ul class="quick-cart-items">
                @foreach($cart->items as $item)
                    @php $product = $item->product; @endphp
                    <li>
                        <img src="{{ $product->image_url ?? \Modules\Ecommerce\Support\Storefront::placeholder() }}" alt="">
                        <div>
                            <div class="q-name">{{ optional($product)->name }}</div>
                            <div class="q-meta">Qty {{ (int) $item->quantity }}</div>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="cart-added-product">
                <div class="cart-lineItems">
                    <div class="cart-labels">Subtotal</div>
                    <div class="cart-values">${{ number_format((float) ($totals['subtotal'] ?? 0), 2) }}</div>
                </div>
            </div>
            <div class="checkout-btn-container">
                <a href="{{ route('ecommerce.checkout') }}" class="checkout-button">Proceed To Checkout</a>
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.cart-qty-form').forEach(form => {
    const input = form.querySelector('.qty-input');
    const minus = form.querySelector('.qty-minus');
    const plus = form.querySelector('.qty-plus');
    if (!input || !minus || !plus) return;
    function setQty(v) {
        const n = Math.max(1, parseInt(v, 10) || 1);
        input.value = n;
        form.submit();
    }
    minus.addEventListener('click', () => setQty((parseInt(input.value, 10) || 1) - 1));
    plus.addEventListener('click', () => setQty((parseInt(input.value, 10) || 1) + 1));
});
</script>
@endpush
