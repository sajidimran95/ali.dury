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

        $product = Product::storefrontQuery((int) $business_id)
            ->where(function ($q) use ($slug) {
                $q->where('ecom_slug', $slug)->orWhere('id', $slug);
            })
            ->with(['variations', 'brand', 'category', 'unit', 'product_variations.variations'])
            ->firstOrFail();

        $stock = 0;
        $variation = $product->variations->first();
        $variant_stocks = [];
        foreach ($product->variations as $v) {
            if ($settings->location_id && $product->enable_stock) {
                $variant_stocks[$v->id] = $inventory->availableQty($product->id, $v->id, $settings->location_id);
            } else {
                $variant_stocks[$v->id] = $product->enable_stock ? 0 : 999;
            }
        }
        if ($variation) {
            $stock = (int) ($variant_stocks[$variation->id] ?? 0);
        } elseif (! $product->enable_stock) {
            $stock = 999;
        }

        $related = Product::storefrontQuery((int) $business_id)
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->with(['variations', 'category', 'brand', 'unit'])
            ->limit(4)
            ->get();

        $reviews = EcomReview::where('product_id', $product->id)->where('status', 'approved')->latest()->get();
        $rating_avg = $reviews->count() ? round((float) $reviews->avg('rating'), 1) : 0.0;
        $review_count = $reviews->count();

        $cart = $cartService->getOrCreateCart($business_id);
        $cart_totals = $cartService->totals($cart);
        $cart_product_ids = $cart->items->pluck('product_id')->unique()->values()->all();

        $recent = session()->get('ecom_recently_viewed', []);
        array_unshift($recent, $product->id);
        session()->put('ecom_recently_viewed', array_values(array_unique(array_slice($recent, 0, 8))));

        $recently_viewed = Product::storefrontQuery((int) $business_id)
            ->whereIn('id', session('ecom_recently_viewed', []))
            ->where('id', '!=', $product->id)
            ->with(['variations', 'category', 'brand', 'unit'])
            ->get();

        $brand_image = null;
        if ($product->brand_id) {
            $brandProduct = Product::storefrontQuery((int) $business_id)
                ->where('brand_id', $product->brand_id)
                ->whereNotNull('image')
                ->where('image', '!=', '')
                ->orderByDesc('id')
                ->first();
            $brand_image = $brandProduct?->image_url;
        }

        $product->loadMissing('category');

        return view('ecommerce::front.product.show', compact(
            'product',
            'variation',
            'stock',
            'variant_stocks',
            'related',
            'reviews',
            'rating_avg',
            'review_count',
            'cart_totals',
            'cart_product_ids',
            'brand_image',
            'recently_viewed'
        ));
    }

    public function stockAlert(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer',
            'email' => 'required|email',
        ]);
        if (\Illuminate\Support\Facades\Schema::hasTable('ecom_stock_alerts')) {
            \Modules\Ecommerce\Entities\EcomStockAlert::firstOrCreate([
                'business_id' => $request->attributes->get('ecommerce_business_id'),
                'product_id' => $data['product_id'],
                'email' => $data['email'],
            ]);
        }

        return back()->with('success', 'We’ll notify you when this item is available.');
    }
}
