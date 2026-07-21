<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Modules\Ecommerce\Entities\EcomCollection;
use Modules\Ecommerce\Services\CartService;

class CollectionFrontController extends Controller
{
    public function show($slug, CartService $cartService)
    {
        $business_id = request()->attributes->get('ecommerce_business_id');
        $collection = EcomCollection::where('business_id', $business_id)
            ->where('slug', $slug)
            ->where('is_active', 1)
            ->with(['products.variations'])
            ->firstOrFail();
        $cart = $cartService->getOrCreateCart($business_id);
        $cart_totals = $cartService->totals($cart);

        return view('ecommerce::front.shop.collection', compact('collection', 'cart_totals'));
    }
}
