<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Brands;
use App\Category;
use App\Http\Controllers\Controller;
use App\Product;
use Illuminate\Http\Request;
use Modules\Ecommerce\Services\CartService;
use Modules\Ecommerce\Support\Storefront;

class ShopController extends Controller
{
    public function index(Request $request, CartService $cartService)
    {
        $business_id = $request->attributes->get('ecommerce_business_id');
        $query = Product::storefrontQuery((int) $business_id)->with(['variations', 'brand', 'category', 'unit']);

        if ($request->filled('category')) {
            $cat = Category::find((int) $request->category);
            if ($cat && Storefront::isGsCategory($cat)) {
                $catId = (int) $cat->id;
                $childIds = Category::where('parent_id', $catId)->pluck('id')->all();
                $ids = array_merge([$catId], $childIds);
                $query->where(function ($q) use ($ids) {
                    $q->whereIn('category_id', $ids)->orWhereIn('sub_category_id', $ids);
                });
            }
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
        if ($request->boolean('featured')) {
            $query->where('ecom_is_featured', 1);
        }
        if ($request->boolean('new_only')) {
            $query->where(function ($q) {
                $q->where('ecom_is_new_arrival', 1)->orWhere('created_at', '>=', now()->subDays(21));
            });
        }
        if ($request->boolean('hot')) {
            $query->where('ecom_is_featured', 1);
        }

        $sort = $request->get('sort', 'name_asc');
        match ($sort) {
            'name_desc' => $query->orderBy('name', 'desc'),
            'price_asc' => $query->orderByRaw('COALESCE(ecom_discount_price, ecom_online_price, 0) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(ecom_discount_price, ecom_online_price, 0) DESC'),
            'newest' => $query->orderByDesc('id'),
            default => $query->orderBy('name', 'asc'),
        };

        $perPage = (int) $request->get('product_count', 100);
        if (! in_array($perPage, [15, 25, 50, 100], true)) {
            $perPage = 100;
        }
        $brandIds = (clone $query)->reorder()->whereNotNull('brand_id')->distinct()->pluck('brand_id');
        $products = $query->paginate($perPage)->withQueryString();
        $categories = Category::where('business_id', $business_id)
            ->where('category_type', 'product')
            ->where(function ($q) {
                $q->whereNull('parent_id')->orWhere('parent_id', 0);
            })
            ->whereIn('name', Storefront::gsTopCategoryNames())
            ->with(['sub_categories' => function ($q) {
                $q->orderBy('name');
            }])
            ->orderBy('name')
            ->get();
        $brands = Brands::where('business_id', $business_id)
            ->whereIn('id', $brandIds)
            ->orderBy('name')
            ->get();
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
            ->first();

        if ($category && Storefront::isGsCategory($category)) {
            $request->merge(['category' => $category->id]);
        } else {
            return redirect()
                ->route('ecommerce.shop')
                ->with('error', 'That category is no longer available. Browse the full catalog instead.');
        }

        return $this->index($request, $cartService);
    }

    public function promotions(Request $request, CartService $cartService)
    {
        $request->merge(['featured' => 1]);

        return $this->index($request, $cartService);
    }

    public function brands(Request $request, CartService $cartService)
    {
        $business_id = $request->attributes->get('ecommerce_business_id');
        $brandIds = Product::storefrontQuery((int) $business_id)->whereNotNull('brand_id')->distinct()->pluck('brand_id');
        $brands = Brands::where('business_id', $business_id)->whereIn('id', $brandIds)->orderBy('name')->get();
        $cart = $cartService->getOrCreateCart($business_id);
        $cart_totals = $cartService->totals($cart);
        $brand_covers = is_file(storage_path('app/ecom_brand_covers.json'))
            ? (json_decode(file_get_contents(storage_path('app/ecom_brand_covers.json')), true) ?: [])
            : [];

        return view('ecommerce::front.shop.brands', compact('brands', 'cart_totals', 'brand_covers'));
    }
}
