<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ecommerce\Support\StaticCatalog;
use Modules\Ecommerce\Support\StaticCart;

class ProductDetailController extends Controller
{
    public function show($slug)
    {
        $product = StaticCatalog::find($slug);
        abort_unless($product, 404);

        $variation = $product->variations->first();
        $stock = 999;
        $variant_stocks = [$variation->id => 999];
        $related = StaticCatalog::related($product, 4);
        $reviews = collect();
        $rating_avg = 0.0;
        $review_count = 0;
        $cart_totals = StaticCart::totals();
        $cart_product_ids = StaticCart::items()->pluck('product_id')->all();
        $brand_image = $product->image_url;
        $recently_viewed = StaticCatalog::all()->where('id', '!=', $product->id)->take(4)->values();

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
        $request->validate([
            'product_id' => 'required',
            'email' => 'required|email',
        ]);

        return back()->with('success', 'We’ll notify you when this item is available.');
    }
}
