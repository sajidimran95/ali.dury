<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ecommerce\Services\CartService;
use Modules\Ecommerce\Services\CheckoutService;
use Modules\Ecommerce\Services\CouponService;
use Modules\Ecommerce\Services\ShippingCalculator;
use Modules\Ecommerce\Services\TaxCalculator;

class CartController extends Controller
{
    public function index(CartService $cartService, ShippingCalculator $shipping, TaxCalculator $tax)
    {
        $business_id = request()->attributes->get('ecommerce_business_id');
        $cart = $cartService->getOrCreateCart($business_id);
        $sub = $cartService->totals($cart);
        $ship = $shipping->calculate($business_id, $sub['subtotal']);
        $tax_amt = $tax->calculate($business_id, $sub['subtotal']);
        $totals = $cartService->totals($cart, $ship, $tax_amt);

        return view('ecommerce::front.cart.index', compact('cart', 'totals'));
    }

    public function add(Request $request, CartService $cartService)
    {
        $business_id = $request->attributes->get('ecommerce_business_id');
        $data = $request->validate([
            'product_id' => 'required|integer',
            'variation_id' => 'required|integer',
            'quantity' => 'nullable|numeric|min:0.01',
        ]);
        try {
            $cartService->add($business_id, $data['product_id'], $data['variation_id'], $data['quantity'] ?? 1);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($request->boolean('buy_now')) {
            return redirect()->route('ecommerce.checkout');
        }

        return back()->with('success', __('ecommerce::lang.added_to_cart'));
    }

    public function update(Request $request, CartService $cartService)
    {
        $business_id = $request->attributes->get('ecommerce_business_id');
        $data = $request->validate([
            'item_id' => 'required|integer',
            'quantity' => 'required|numeric',
        ]);
        try {
            $cartService->updateQty($business_id, $data['item_id'], $data['quantity']);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('ecommerce::lang.cart_updated'));
    }

    public function remove(Request $request, CartService $cartService)
    {
        $business_id = $request->attributes->get('ecommerce_business_id');
        $cartService->remove($business_id, (int) $request->input('item_id'));

        return back()->with('success', __('ecommerce::lang.item_removed'));
    }

    public function applyCoupon(Request $request, CartService $cartService, CouponService $couponService)
    {
        $business_id = $request->attributes->get('ecommerce_business_id');
        $cart = $cartService->getOrCreateCart($business_id);
        $subtotal = $cart->items->sum(fn ($i) => $i->quantity * $i->unit_price);
        try {
            $couponService->findValid($business_id, $request->input('coupon_code'), $subtotal);
            $cart->coupon_code = $request->input('coupon_code');
            $cart->save();
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('ecommerce::lang.coupon_applied'));
    }
}
