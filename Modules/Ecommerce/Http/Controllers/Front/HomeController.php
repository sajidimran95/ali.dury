<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Category;
use App\Http\Controllers\Controller;
use App\Product;
use Illuminate\Support\Facades\DB;
use Modules\Ecommerce\Entities\EcomCollection;
use Modules\Ecommerce\Entities\EcomReview;
use Modules\Ecommerce\Services\CartService;
use Modules\Ecommerce\Support\Storefront;

class HomeController extends Controller
{
    public function index(CartService $cartService)
    {
        $business_id = request()->attributes->get('ecommerce_business_id');
        $banners = Storefront::videos();

        $featured = Product::storefrontQuery((int) $business_id)->where('ecom_is_featured', 1)->with(['variations', 'category', 'brand', 'unit'])->limit(12)->get();
        if ($featured->isEmpty()) {
            $featured = Product::storefrontQuery((int) $business_id)->with(['variations', 'category', 'brand', 'unit'])->orderByDesc('id')->limit(12)->get();
        }

        $new_arrivals = Product::storefrontQuery((int) $business_id)->where('ecom_is_new_arrival', 1)->with(['variations', 'category', 'brand', 'unit'])->limit(8)->get();
        if ($new_arrivals->isEmpty()) {
            $new_arrivals = Product::storefrontQuery((int) $business_id)->with(['variations', 'category', 'brand', 'unit'])->orderByDesc('id')->limit(8)->get();
        }

        $best_seller_ids = DB::table('transaction_sell_lines as tsl')
            ->join('transactions as t', 't.id', '=', 'tsl.transaction_id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->select('tsl.product_id', DB::raw('SUM(tsl.quantity) as qty_sold'))
            ->groupBy('tsl.product_id')
            ->orderByDesc('qty_sold')
            ->limit(8)
            ->pluck('product_id');

        $best_sellers = Product::storefrontQuery((int) $business_id)
            ->whereIn('id', $best_seller_ids)
            ->with(['variations', 'category', 'brand', 'unit'])
            ->get();

        if ($best_sellers->isEmpty()) {
            $best_sellers = $featured;
        }

        $categories = Category::where('business_id', $business_id)
            ->where('category_type', 'product')
            ->where(function ($q) {
                $q->whereNull('parent_id')->orWhere('parent_id', 0);
            })
            ->orderBy('name')
            ->limit(12)
            ->get();
        $collections = EcomCollection::where('business_id', $business_id)->where('is_active', 1)->orderBy('sort_order')->limit(4)->get();
        $reviews = EcomReview::where('business_id', $business_id)->where('status', 'approved')->with('product')->latest()->limit(6)->get();
        $cart = $cartService->getOrCreateCart($business_id);
        $cart_totals = $cartService->totals($cart);

        $category_covers = [];
        $coverFile = storage_path('app/ecom_category_covers.json');
        if (is_file($coverFile)) {
            $category_covers = json_decode(file_get_contents($coverFile), true) ?: [];
        }

        return view('ecommerce::front.home.index', compact(
            'banners',
            'featured',
            'new_arrivals',
            'best_sellers',
            'categories',
            'collections',
            'reviews',
            'cart_totals',
            'category_covers'
        ));
    }
}
