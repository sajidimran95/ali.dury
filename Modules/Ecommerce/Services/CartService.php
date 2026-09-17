<?php

namespace Modules\Ecommerce\Services;

use App\Product;
use App\Variation;
use Illuminate\Support\Facades\Session;
use Modules\Ecommerce\Entities\EcomCart;
use Modules\Ecommerce\Entities\EcomCartItem;
use Modules\Ecommerce\Entities\EcommerceSetting;

class CartService
{
    public function __construct(protected InventoryGuard $inventory)
    {
    }

    public function getOrCreateCart(int $business_id): EcomCart
    {
        $contact_id = auth('customer')->id();
        $session_id = Session::getId();

        $cart = null;
        if ($contact_id) {
            $cart = EcomCart::where('business_id', $business_id)->where('contact_id', $contact_id)->first();
        }
        if (! $cart) {
            $cart = EcomCart::where('business_id', $business_id)->where('session_id', $session_id)->whereNull('contact_id')->first();
        }
        if (! $cart) {
            $cart = EcomCart::create([
                'business_id' => $business_id,
                'contact_id' => $contact_id,
                'session_id' => $session_id,
            ]);
        } elseif ($contact_id && ! $cart->contact_id) {
            $cart->contact_id = $contact_id;
            $cart->save();
        }

        return $cart->load(['items.product', 'items.variation']);
    }

    public function add(int $business_id, int $product_id, int $variation_id, float $qty = 1): EcomCart
    {
        $product = Product::storefrontQuery($business_id)->findOrFail($product_id);
        $variation = Variation::where('product_id', $product->id)->findOrFail($variation_id);
        $settings = EcommerceSetting::forBusiness($business_id);
        $location_id = $settings->location_id;

        if ($location_id) {
            $this->inventory->assertAvailable($product_id, $variation_id, $location_id, $qty);
        }

        $cart = $this->getOrCreateCart($business_id);
        $item = $cart->items()->where('variation_id', $variation_id)->first();
        $unit_price = $this->resolvePrice($product, $variation);

        if ($item) {
            $new_qty = $item->quantity + $qty;
            if ($location_id) {
                $this->inventory->assertAvailable($product_id, $variation_id, $location_id, $new_qty);
            }
            $item->quantity = $new_qty;
            $item->unit_price = $unit_price;
            $item->save();
        } else {
            EcomCartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product_id,
                'variation_id' => $variation_id,
                'quantity' => $qty,
                'unit_price' => $unit_price,
            ]);
        }

        return $this->getOrCreateCart($business_id);
    }

    public function updateQty(int $business_id, int $item_id, float $qty): EcomCart
    {
        $cart = $this->getOrCreateCart($business_id);
        $item = $cart->items()->where('id', $item_id)->firstOrFail();
        if ($qty <= 0) {
            $item->delete();

            return $this->getOrCreateCart($business_id);
        }

        $settings = EcommerceSetting::forBusiness($business_id);
        if ($settings->location_id) {
            $this->inventory->assertAvailable($item->product_id, $item->variation_id, $settings->location_id, $qty);
        }

        $item->quantity = $qty;
        $item->save();

        return $this->getOrCreateCart($business_id);
    }

    public function remove(int $business_id, int $item_id): EcomCart
    {
        $cart = $this->getOrCreateCart($business_id);
        $cart->items()->where('id', $item_id)->delete();

        return $this->getOrCreateCart($business_id);
    }

    public function clear(int $business_id): void
    {
        $cart = $this->getOrCreateCart($business_id);
        $cart->items()->delete();
        $cart->coupon_code = null;
        $cart->save();
    }

    public function totals(EcomCart $cart, float $shipping = 0, float $tax = 0, float $discount = 0): array
    {
        $subtotal = $cart->items->sum(fn ($i) => $i->quantity * $i->unit_price);

        return [
            'subtotal' => round($subtotal, 2),
            'shipping' => round($shipping, 2),
            'tax' => round($tax, 2),
            'discount' => round($discount, 2),
            'total' => round(max(0, $subtotal + $shipping + $tax - $discount), 2),
            'count' => (int) $cart->items->sum('quantity'),
        ];
    }

    protected function resolvePrice(Product $product, Variation $variation): float
    {
        if (! is_null($product->ecom_discount_price) && $product->ecom_discount_price > 0) {
            return (float) $product->ecom_discount_price;
        }
        if (! is_null($product->ecom_online_price) && $product->ecom_online_price > 0) {
            return (float) $product->ecom_online_price;
        }

        return (float) $variation->sell_price_inc_tax;
    }
}
