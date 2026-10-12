<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ecommerce\Support\StaticCatalog;
use Modules\Ecommerce\Support\StaticCart;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $products = StaticCatalog::paginate($request, 16);
        $categories = StaticCatalog::categories();
        $brands = StaticCatalog::brands();
        $sort = $request->get('sort', 'newest');
        $cart_totals = StaticCart::totals();

        return view('ecommerce::front.shop.index', compact('products', 'categories', 'brands', 'cart_totals', 'sort'));
    }

    public function category($slug, Request $request)
    {
        $category = StaticCatalog::categories()->first(function ($c) use ($slug) {
            return (string) $c->id === (string) $slug || $c->slug === $slug || strcasecmp($c->name, (string) $slug) === 0;
        });

        if (! $category) {
            return redirect()->route('ecommerce.shop')->with('error', 'That category is no longer available. Browse the full catalog instead.');
        }

        $request->merge(['category' => $category->id]);

        return $this->index($request);
    }

    public function promotions(Request $request)
    {
        $request->merge(['featured' => 1]);

        return $this->index($request);
    }

    public function brands()
    {
        $brands = StaticCatalog::brands();
        $cart_totals = StaticCart::totals();

        return view('ecommerce::front.shop.brands', compact('brands', 'cart_totals'));
    }
}
