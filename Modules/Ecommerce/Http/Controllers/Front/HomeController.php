<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Category;
use App\Http\Controllers\Controller;
use App\Product;
use Modules\Ecommerce\Entities\EcomBanner;
use Modules\Ecommerce\Entities\EcomCollection;
use Modules\Ecommerce\Entities\EcomReview;
use Modules\Ecommerce\Services\CartService;

class HomeController extends Controller
{
    public function index(CartService $cartService)
    {
        $business_id = request()->attributes->get('ecommerce_business_id');
        $banners = EcomBanner::where('business_id', $business_id)->where('is_active', 1)->orderBy('sort_order')->get();
        $featured = Product::where('business_id', $business_id)->active()->forEcommerce()->where('ecom_is_featured', 1)->with('variations')->limit(8)->get();
        $new_arrivals = Product::where('business_id', $business_id)->active()->forEcommerce()->where('ecom_is_new_arrival', 1)->with('variations')->limit(8)->get();
        if ($new_arrivals->isEmpty()) {
            $new_arrivals = Product::where('business_id', $business_id)->active()->forEcommerce()->with('variations')->orderByDesc('id')->limit(8)->get();
        }
        $categories = Category::where('business_id', $business_id)->where('category_type', 'product')->whereNull('parent_id')->limit(8)->get();
        $collections = EcomCollection::where('business_id', $business_id)->where('is_active', 1)->orderBy('sort_order')->limit(4)->get();
        $reviews = EcomReview::where('business_id', $business_id)->where('status', 'approved')->with('product')->latest()->limit(6)->get();
        $cart = $cartService->getOrCreateCart($business_id);
        $cart_totals = $cartService->totals($cart);

        return view('ecommerce::front.home.index', compact('banners', 'featured', 'new_arrivals', 'categories', 'collections', 'reviews', 'cart_totals'));
    }
}
