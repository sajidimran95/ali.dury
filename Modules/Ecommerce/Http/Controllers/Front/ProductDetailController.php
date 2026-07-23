<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Product;
use Illuminate\Http\Request;
use Modules\Ecommerce\Entities\EcomReview;
use Modules\Ecommerce\Services\CartService;
use Modules\Ecommerce\Services\InventoryGuard;

class ProductDetailController extends Controller
{
    public function show($slug, Request $request, CartService $cartService, InventoryGuard $inventory)
    {
        $business_id = $request->attributes->get('ecommerce_business_id');
        $settings = $request->attributes->get('ecommerce_settings');

        $product = Product::where('business_id', $business_id)
            ->active()
            ->forEcommerce()
            ->where(function ($q) use ($slug) {
                $q->where('ecom_slug', $slug)->orWhere('id', $slug);
            })
            ->with(['variations', 'brand', 'category', 'unit', 'product_variations.variations'])
            ->firstOrFail();

        $stock = 0;
        $variation = $product->variations->first();
        if ($variation && $settings->location_id) {
            $stock = $inventory->availableQty($product->id, $variation->id, $settings->location_id);
        } elseif (! $product->enable_stock) {
            $stock = 999;
        }

        $related = Product::where('business_id', $business_id)
            ->active()
            ->forEcommerce()
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->with(['variations', 'category'])
            ->limit(4)
            ->get();

        $reviews = EcomReview::where('product_id', $product->id)->where('status', 'approved')->latest()->get();
        $cart = $cartService->getOrCreateCart($business_id);
        $cart_totals = $cartService->totals($cart);

        $recent = session()->get('ecom_recently_viewed', []);
        array_unshift($recent, $product->id);
        session()->put('ecom_recently_viewed', array_values(array_unique(array_slice($recent, 0, 8))));

        $recently_viewed = Product::whereIn('id', session('ecom_recently_viewed', []))
            ->where('id', '!=', $product->id)
            ->forEcommerce()
            ->with(['variations', 'category'])
            ->get();

        $product->loadMissing('category');

        return view('ecommerce::front.product.show', compact('product', 'variation', 'stock', 'related', 'reviews', 'cart_totals', 'recently_viewed'));
    }
}
