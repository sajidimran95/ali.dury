<?php

namespace Modules\Ecommerce\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        $products = Product::where('business_id', $business_id)
            ->forEcommerce()
            ->with(['category', 'brand', 'variations'])
            ->orderByDesc('id')
            ->paginate(25);

        return view('ecommerce::admin.products.index', compact('products'));
    }
}
