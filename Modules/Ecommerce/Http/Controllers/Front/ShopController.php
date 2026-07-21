<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Brands;
use App\Category;
use App\Http\Controllers\Controller;
use App\Product;
use Illuminate\Http\Request;
use Modules\Ecommerce\Services\CartService;

class ShopController extends Controller
{
    public function index(Request $request, CartService $cartService)
    {
        $business_id = $request->attributes->get('ecommerce_business_id');
        $query = Product::where('business_id', $business_id)->active()->forEcommerce()->with(['variations', 'brand', 'category']);

        if ($request->filled('category')) {
            $query->where(function ($q) use ($request) {
                $q->where('category_id', $request->category)->orWhere('sub_category_id', $request->category);
            });
        }
        if ($request->filled('brand')) {
            $query->where('brand_id', $request->brand);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%");
            });
        }

        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'price_asc' => $query->orderByRaw('COALESCE(ecom_discount_price, ecom_online_price, 0) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(ecom_discount_price, ecom_online_price, 0) DESC'),
            default => $query->orderByDesc('id'),
        };

        $products = $query->paginate(16)->withQueryString();
        $categories = Category::where('business_id', $business_id)->where('category_type', 'product')->whereNull('parent_id')->get();
        $brands = Brands::where('business_id', $business_id)->get();
        $cart = $cartService->getOrCreateCart($business_id);
        $cart_totals = $cartService->totals($cart);

        return view('ecommerce::front.shop.index', compact('products', 'categories', 'brands', 'cart_totals', 'sort'));
    }

    public function category($slug, Request $request, CartService $cartService)
    {
        $business_id = $request->attributes->get('ecommerce_business_id');
        $category = Category::where('business_id', $business_id)
            ->where(function ($q) use ($slug) {
                $q->where('slug', $slug)->orWhere('id', $slug)->orWhere('name', $slug);
            })
            ->firstOrFail();

        $request->merge(['category' => $category->id]);

        return $this->index($request, $cartService);
    }
}
