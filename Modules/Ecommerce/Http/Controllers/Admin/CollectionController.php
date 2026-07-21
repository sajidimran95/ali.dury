<?php

namespace Modules\Ecommerce\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Ecommerce\Entities\EcomCollection;

class CollectionController extends Controller
{
    public function index()
    {
        $business_id = session('user.business_id');
        $collections = EcomCollection::where('business_id', $business_id)->withCount('products')->orderBy('sort_order')->get();
        $products = Product::where('business_id', $business_id)->forEcommerce()->pluck('name', 'id');

        return view('ecommerce::admin.collections.index', compact('collections', 'products'));
    }

    public function store(Request $request)
    {
        $business_id = session('user.business_id');
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
            'product_ids' => 'nullable|array',
        ]);
        $collection = EcomCollection::create([
            'business_id' => $business_id,
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::random(4),
            'description' => $data['description'] ?? null,
            'is_active' => true,
        ]);
        if (! empty($data['product_ids'])) {
            $collection->products()->sync($data['product_ids']);
        }

        return back()->with('status', ['success' => 1, 'msg' => __('ecommerce::lang.saved')]);
    }

    public function destroy($id)
    {
        $c = EcomCollection::where('business_id', session('user.business_id'))->findOrFail($id);
        $c->products()->detach();
        $c->delete();

        return back()->with('status', ['success' => 1, 'msg' => __('ecommerce::lang.deleted')]);
    }
}
