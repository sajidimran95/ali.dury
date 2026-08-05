<?php

namespace Modules\Ecommerce\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $business_id = (int) $request->session()->get('user.business_id');

        $products = Product::where('business_id', $business_id)
            ->forEcommerce()
            ->with(['category', 'brand', 'variations'])
            ->orderByDesc('id')
            ->paginate(25);

        $product_stats = $this->getEcommerceProductListStats($business_id);

        return view('ecommerce::admin.products.index', compact('products', 'product_stats'));
    }

    /**
     * KPI stats for ecommerce products list cards.
     */
    protected function getEcommerceProductListStats(int $businessId): array
    {
        $base = Product::where('business_id', $businessId)->forEcommerce();

        $total = (int) (clone $base)->count();
        $posEcom = (int) (clone $base)->where('sales_channel', 'pos_ecommerce')->count();
        $ecomOnly = (int) (clone $base)->where('sales_channel', 'ecommerce_only')->count();
        $featured = (int) (clone $base)->where('ecom_is_featured', 1)->count();
        $newArrival = (int) (clone $base)->where('ecom_is_new_arrival', 1)->count();
        $onDiscount = (int) (clone $base)
            ->whereNotNull('ecom_discount_price')
            ->where('ecom_discount_price', '>', 0)
            ->count();

        return [
            'total' => $total,
            'pos_ecommerce' => $posEcom,
            'ecommerce_only' => $ecomOnly,
            'featured' => $featured,
            'new_arrival' => $newArrival,
            'on_discount' => $onDiscount,
        ];
    }
}
