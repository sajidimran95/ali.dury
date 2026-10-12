<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Modules\Ecommerce\Support\StaticCatalog;
use Modules\Ecommerce\Support\StaticCart;

class HomeController extends Controller
{
    public function index()
    {
        $all = StaticCatalog::all();
        $banners = StaticCatalog::videos();
        $featured = $all->where('ecom_is_featured', true)->take(6)->values();
        if ($featured->isEmpty()) {
            $featured = $all->take(6)->values();
        }
        $new_arrivals = $all->where('ecom_is_new_arrival', true)->take(8)->values();
        if ($new_arrivals->isEmpty()) {
            $new_arrivals = $all->take(8)->values();
        }
        $best_sellers = $all->sortByDesc('ecom_is_featured')->take(8)->values();
        $categories = StaticCatalog::categories();
        $collections = StaticCatalog::collections();
        $reviews = collect();
        $cart_totals = StaticCart::totals();
        $category_covers = [];

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
