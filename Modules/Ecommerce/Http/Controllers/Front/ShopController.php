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
        $query = Product::storefrontQuery((int) $business_id)->with(['variations', 'brand', 'category']);

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
        if ($request->filled('min_price')) {
            $query->whereRaw('COALESCE(ecom_discount_price, ecom_online_price, 0) >= ?', [(float) $request->min_price]);
        }
        if ($request->filled('max_price')) {
            $query->whereRaw('COALESCE(ecom_discount_price, ecom_online_price, 0) <= ?', [(float) $request->max_price]);
        }
        if ($request->boolean('on_sale') || $request->get('sort') === 'sale') {
            $query->whereNotNull('ecom_discount_price')
                ->whereColumn('ecom_discount_price', '<', 'ecom_online_price');
        }
        if ($request->boolean('new_only')) {
            $query->where(function ($q) {
                $q->where('ecom_is_new_arrival', 1)->orWhere('created_at', '>=', now()->subDays(21));
            });
        }

        $sort = $request->get('sort', 'featured');
        match ($sort) {
            'price_asc' => $query->orderByRaw('COALESCE(ecom_discount_price, ecom_online_price, 0) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(ecom_discount_price, ecom_online_price, 0) DESC'),
            'name' => $query->orderBy('name'),
            'bestselling' => $query->orderByDesc('id'),
            'newest' => $query->orderByDesc('id'),
            default => $query->orderByDesc('ecom_is_featured')->orderByDesc('id'),
        };

        $products = $query->paginate(24)->withQueryString();
        $categories = Category::where('business_id', $business_id)
            ->where('category_type', 'product')
            ->where(function ($q) {
                $q->whereNull('parent_id')->orWhere('parent_id', 0);
            })
            ->orderBy('name')
            ->get();
        $brands = Brands::where('business_id', $business_id)->get();
        $cart = $cartService->getOrCreateCart($business_id);
        $cart_totals = $cartService->totals($cart);
        $hub = $request->attributes->get('ecom_hub');

        return view('ecommerce::front.shop.index', compact('products', 'categories', 'brands', 'cart_totals', 'sort', 'hub'));
    }

    public function category($slug, Request $request, CartService $cartService)
    {
        $business_id = $request->attributes->get('ecommerce_business_id');
        $category = Category::where('business_id', $business_id)
            ->where(function ($q) use ($slug) {
                $q->where('slug', $slug)->orWhere('id', $slug)->orWhere('name', $slug);
            })
            ->first();

        $hub = \Modules\Ecommerce\Support\Storefront::hub(strtolower((string) $slug));
        if ($hub) {
            $request->attributes->set('ecom_hub', $hub);
        }

        if ($category) {
            $request->merge(['category' => $category->id]);
        } elseif (! $hub) {
            // Stale banner/menu links (deleted category IDs) → soft-land on shop
            return redirect()
                ->route('ecommerce.shop')
                ->with('error', 'That category is no longer available. Browse the full catalog instead.');
        }

        return $this->index($request, $cartService);
    }
}
