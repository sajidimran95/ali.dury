<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ecommerce\Support\StaticCart;

class CartController extends Controller
{
    public function index()
    {
        $cart = StaticCart::cart();
        $totals = StaticCart::totals();
        $freeShipMin = 500.0;
        $taxRateLabel = '5';

        return view('ecommerce::front.cart.index', compact('cart', 'totals', 'freeShipMin', 'taxRateLabel'));
    }

    public function clear()
    {
        StaticCart::clear();

        return redirect()->route('ecommerce.cart')->with('success', 'Cart cleared.');
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer',
            'variation_id' => 'nullable|integer',
            'quantity' => 'nullable|numeric|min:0.01',
        ]);
        StaticCart::add((int) $data['product_id'], (float) ($data['quantity'] ?? 1));

        if ($request->boolean('buy_now')) {
            return redirect()->route('ecommerce.checkout');
        }

        return back()->with('success', __('ecommerce::lang.added_to_cart'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'item_id' => 'required|integer',
            'quantity' => 'required|numeric',
        ]);
        StaticCart::setQty((int) $data['item_id'], (float) $data['quantity']);

        return back()->with('success', __('ecommerce::lang.cart_updated'));
    }

    public function remove(Request $request)
    {
        StaticCart::remove((int) $request->input('item_id'));

        return back()->with('success', __('ecommerce::lang.item_removed'));
    }

    public function applyCoupon(Request $request)
    {
        return back()->with('error', 'Coupons are not available on the demo catalog.');
    }
}
