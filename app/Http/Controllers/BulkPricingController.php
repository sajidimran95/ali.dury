<?php

namespace App\Http\Controllers;

use App\Brands;
use App\BulkPriceUpdate;
use App\Category;
use App\Product;
use App\Variation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class BulkPricingController extends Controller
{
    public function index()
    {
        if (! auth()->user()->can('bulk_pricing.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $categories = Category::forDropdown($business_id, 'product');
        $brands = Brands::forDropdown($business_id);

        return view('bulk_pricing.index', compact('categories', 'brands'));
    }

    public function getProducts(Request $request)
    {
        if (! auth()->user()->can('bulk_pricing.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        $query = Product::leftjoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftjoin('categories as c1', 'products.category_id', '=', 'c1.id')
            ->leftjoin('categories as c2', 'products.sub_category_id', '=', 'c2.id')
            ->join('variations as v', 'products.id', '=', 'v.product_id')
            ->where('products.business_id', $business_id)
            ->where('products.type', '!=', 'modifier')
            ->whereNull('v.deleted_at');

        // Filters
        if ($request->has('category_id') && ! empty($request->category_id)) {
            $query->where(function ($q) use ($request) {
                $q->where('products.category_id', $request->category_id)
                  ->orWhere('products.sub_category_id', $request->category_id);
            });
        }

        if ($request->has('brand_id') && ! empty($request->brand_id)) {
            $query->where('products.brand_id', $request->brand_id);
        }

        if ($request->has('product_type') && ! empty($request->product_type)) {
            $query->where('products.type', $request->product_type);
        }

        $query->select([
            'products.id as product_id',
            'products.name as product_name',
            'products.type',
            'v.id as variation_id',
            'v.name as variation_name',
            'v.sub_sku',
            'v.default_sell_price',
            'v.sell_price_inc_tax',
            'brands.name as brand',
            'c1.name as category',
        ]);

        return DataTables::of($query)
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="row-select-bulk-price" value="'.$row->variation_id.'" data-product-id="'.$row->product_id.'">';
            })
            ->editColumn('product_name', function ($row) {
                $name = $row->product_name;
                if ($row->type === 'variable' && $row->variation_name !== 'DUMMY') {
                    $name .= ' - <strong>'.$row->variation_name.'</strong>';
                }

                return $name;
            })
            ->editColumn('default_sell_price', '@format_currency($default_sell_price)')
            ->editColumn('sell_price_inc_tax', '@format_currency($sell_price_inc_tax)')
            ->rawColumns(['checkbox', 'product_name'])
            ->make(true);
    }

    public function update(Request $request)
    {
        if (! auth()->user()->can('bulk_pricing.access')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'update_type' => 'required|in:percentage,flat',
            'update_value' => 'required|numeric',
            'price_field' => 'required|in:default_sell_price,sell_price_inc_tax',
            'variation_ids' => 'required|array|min:1',
        ]);

        try {
            $business_id = $request->session()->get('user.business_id');
            $user_id = auth()->id();

            DB::beginTransaction();

            $variation_ids = $request->variation_ids;
            $update_type = $request->update_type;
            $update_value = (float) $request->update_value;
            $price_field = $request->price_field;

            $updated_count = 0;

            foreach ($variation_ids as $variation_id) {
                $variation = Variation::find($variation_id);
                if (! $variation) {
                    continue;
                }

                $old_price = $variation->{$price_field};

                if ($update_type === 'percentage') {
                    $new_price = $old_price * (1 + ($update_value / 100));
                } else {
                    $new_price = $old_price + $update_value;
                }

                if ($new_price < 0) {
                    $new_price = 0;
                }

                $variation->{$price_field} = round($new_price, 4);
                $variation->save();

                $updated_count++;
            }

            // Log the bulk update
            $bulkUpdate = new BulkPriceUpdate();
            $bulkUpdate->business_id = $business_id;
            $bulkUpdate->created_by = $user_id;
            $bulkUpdate->update_type = $update_type;
            $bulkUpdate->update_value = $update_value;
            $bulkUpdate->price_field = $price_field;
            $bulkUpdate->filters = json_encode($request->only(['category_id', 'brand_id', 'product_type']));
            $bulkUpdate->products_count = $updated_count;
            $bulkUpdate->product_ids = implode(',', $variation_ids);
            $bulkUpdate->notes = $request->notes;
            $bulkUpdate->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'msg' => __('lang_v1.bulk_price_update_success', ['count' => $updated_count]),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());

            return response()->json([
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
    }

    public function history(Request $request)
    {
        if (! auth()->user()->can('bulk_pricing.access')) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->ajax()) {
            $business_id = $request->session()->get('user.business_id');

            $query = BulkPriceUpdate::where('business_id', $business_id)
                ->with('user:id,first_name,last_name')
                ->orderBy('created_at', 'desc');

            return DataTables::of($query)
                ->editColumn('created_at', function ($row) {
                    return \Carbon\Carbon::parse($row->created_at)->format('Y-m-d H:i');
                })
                ->editColumn('user', function ($row) {
                    return optional($row->user)->first_name.' '.optional($row->user)->last_name;
                })
                ->editColumn('update_type', function ($row) {
                    return ucfirst($row->update_type);
                })
                ->editColumn('update_value', function ($row) {
                    if ($row->update_type === 'percentage') {
                        return '<strong>'.($row->update_value > 0 ? '+' : '').$row->update_value.'%</strong>';
                    }

                    return '<strong>'.($row->update_value > 0 ? '+' : '').session('currency.symbol', '$').number_format($row->update_value, 2).'</strong>';
                })
                ->addColumn('action', function ($row) {
                    return '<button type="button" class="btn btn-xs btn-info view-bulk-update" data-id="'.$row->id.'" title="View Details">
                        <i class="fa fa-eye"></i>
                    </button>';
                })
                ->rawColumns(['update_value', 'action'])
                ->make(true);
        }

        return view('bulk_pricing.history');
    }

    public function details($id)
    {
        if (! auth()->user()->can('bulk_pricing.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        
        $bulkUpdate = BulkPriceUpdate::where('business_id', $business_id)
            ->where('id', $id)
            ->with('user')
            ->firstOrFail();

        // Get variation IDs
        $variation_ids = array_filter(explode(',', $bulkUpdate->product_ids));
        
        // Debug: Log the IDs
        \Log::info('Bulk Update ID: '.$id);
        \Log::info('Variation IDs from DB: '.json_encode($variation_ids));
        
        // Get variations with product details
        $variations = \App\Variation::whereIn('id', $variation_ids)
            ->with(['product' => function($q) {
                $q->select('id', 'name', 'sku', 'type');
            }, 'product_variation' => function($q) {
                $q->select('id', 'name', 'variation_template_id');
            }])
            ->select('id', 'product_id', 'variation_value_id', 'sub_sku', 'default_sell_price', 'sell_price_inc_tax')
            ->get();
            
        \Log::info('Variations found: '.$variations->count());

        $currency_symbol = session('currency.symbol', '$');
        
        $html = '<div class="row">
            <div class="col-md-12">
                <p><strong>Date:</strong> '.date('Y-m-d H:i', strtotime($bulkUpdate->created_at)).'</p>
                <p><strong>Updated By:</strong> '.optional($bulkUpdate->user)->first_name.' '.optional($bulkUpdate->user)->last_name.'</p>
                <p><strong>Update Type:</strong> '.ucfirst($bulkUpdate->update_type).'</p>
                <p><strong>Value:</strong> '.($bulkUpdate->update_type === 'percentage' ? $bulkUpdate->update_value.'%' : $currency_symbol.number_format($bulkUpdate->update_value, 2)).'</p>
                <p><strong>Products Updated:</strong> '.$bulkUpdate->products_count.'</p>';
        
        if ($bulkUpdate->notes) {
            $html .= '<p><strong>Notes:</strong> '.$bulkUpdate->notes.'</p>';
        }

        $html .= '<hr><h4>Updated Products: ('.$variations->count().' items)</h4>';
        
        if ($variations->isEmpty()) {
            $html .= '<div class="alert alert-warning">No product details available.</div>';
        } else {
            $html .= '<div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Variation</th>
                                <th>Price From</th>
                                <th>Price To</th>
                            </tr>
                        </thead>
                        <tbody>';

            foreach ($variations as $variation) {
                $product = $variation->product;
                
                // Calculate old price by reversing the update
                $current_price = $variation->default_sell_price;
                $old_price = $this->calculateOldPrice($current_price, $bulkUpdate->update_type, $bulkUpdate->update_value);
                
                $variation_name = '-';
                if ($product->type === 'variable' && $variation->product_variation) {
                    $variation_name = $variation->product_variation->name !== 'DUMMY' ? $variation->product_variation->name : '-';
                }
                
                $html .= '<tr>
                    <td>'.e($product->name).'</td>
                    <td>'.e($variation->sub_sku ?: $product->sku).'</td>
                    <td>'.$variation_name.'</td>
                    <td><span class="text-muted">'.$currency_symbol.number_format($old_price, 2).'</span></td>
                    <td><strong class="text-success">'.$currency_symbol.number_format($current_price, 2).'</strong></td>
                </tr>';
            }

            $html .= '</tbody></table></div>';
        }
        
        $html .= '</div></div>';

        return $html;
    }

    private function calculateOldPrice($current_price, $update_type, $update_value)
    {
        if ($update_type === 'percentage') {
            // Reverse percentage calculation: old_price * (1 + percentage/100) = current_price
            // So: old_price = current_price / (1 + percentage/100)
            return $current_price / (1 + ($update_value / 100));
        } else {
            // Reverse flat amount: old_price + flat_amount = current_price
            // So: old_price = current_price - flat_amount
            return $current_price - $update_value;
        }
    }
}
