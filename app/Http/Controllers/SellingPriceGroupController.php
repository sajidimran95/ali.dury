<?php

namespace App\Http\Controllers;

use App\SellingPriceGroup;
use App\Utils\Util;
use App\Variation;
use App\VariationGroupPrice;
use DB;
use Excel;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class SellingPriceGroupController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $commonUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $price_groups = SellingPriceGroup::where('business_id', $business_id)
                        ->select(['name', 'description', 'id', 'is_active']);

            return Datatables::of($price_groups)
                ->addColumn(
                    'action',
                    '<button data-href="{{action(\'App\Http\Controllers\SellingPriceGroupController@edit\', [$id])}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary btn-modal" data-container=".view_modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                        &nbsp;
                        <button data-href="{{action(\'App\Http\Controllers\SellingPriceGroupController@destroy\', [$id])}}" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error delete_spg_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                        &nbsp;
                        <button data-href="{{action(\'App\Http\Controllers\SellingPriceGroupController@activateDeactivate\', [$id])}}" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs  @if($is_active) tw-dw-btn-error @else tw-dw-btn-success @endif activate_deactivate_spg"><i class="fas fa-power-off"></i> @if($is_active) @lang("messages.deactivate") @else @lang("messages.activate") @endif</button>'
                )
                ->removeColumn('is_active')
                ->removeColumn('id')
                ->rawColumns([2])
                ->make(false);
        }

        return view('selling_price_group.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('selling_price_group.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'description']);
            $business_id = $request->session()->get('user.business_id');
            $input['business_id'] = $business_id;

            DB::beginTransaction();

            $spg = SellingPriceGroup::create($input);

            //Create a new permission related to the created selling price group
            Permission::create(['name' => 'selling_price_group.'.$spg->id]);

            // Inherit full inventory — one price row per variation (starts at default sell price)
            $seeded = SellingPriceGroup::seedAllInventoryPrices((int) $business_id, (int) $spg->id);

            DB::commit();

            $msg = __('lang_v1.added_success');
            if ($seeded > 0) {
                $msg .= ' '.__('lang_v1.price_group_inventory_seeded', ['count' => $seeded]);
            }

            $output = ['success' => true,
                'data' => $spg,
                'msg' => $msg,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function show(SellingPriceGroup $sellingPriceGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $spg = SellingPriceGroup::where('business_id', $business_id)->find($id);

            return view('selling_price_group.edit')
                ->with(compact('spg'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'description']);
                $business_id = $request->session()->get('user.business_id');

                $spg = SellingPriceGroup::where('business_id', $business_id)->findOrFail($id);
                $spg->name = $input['name'];
                $spg->description = $input['description'];
                $spg->save();

                $output = ['success' => true,
                    'msg' => __('lang_v1.updated_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;

                $spg = SellingPriceGroup::where('business_id', $business_id)->findOrFail($id);
                $spg->delete();

                $output = ['success' => true,
                    'msg' => __('lang_v1.deleted_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Show interface to download product price excel file.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateProductPrice(){
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        return view('selling_price_group.update_product_price');
    }

    /**
     * Exports selling price group prices for all the products in xls format
     *
     * @return \Illuminate\Http\Response
     */
    public function export()
    {
        $business_id = request()->user()->business_id;
        $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->get();

        $variations = Variation::join('products as p', 'variations.product_id', '=', 'p.id')
                            ->join('product_variations as pv', 'variations.product_variation_id', '=', 'pv.id')
                            ->where('p.business_id', $business_id)
                            ->whereIn('p.type', ['single', 'variable'])
                            ->select('sub_sku', 'p.name as product_name', 'variations.name as variation_name', 'p.type', 'variations.id', 'pv.name as product_variation_name', 'sell_price_inc_tax')
                            ->with(['group_prices'])
                            ->get();
        $export_data = [];
        foreach ($variations as $variation) {
            $temp = [];
            $temp['product'] = $variation->type == 'single' ? $variation->product_name : $variation->product_name.' - '.$variation->product_variation_name.' - '.$variation->variation_name;
            $temp['sku'] = $variation->sub_sku;
            $temp['Selling Price Including Tax'] = $variation->sell_price_inc_tax;

            foreach ($price_groups as $price_group) {
                $price_group_id = $price_group->id;
                $variation_pg = $variation->group_prices->filter(function ($item) use ($price_group_id) {
                    return $item->price_group_id == $price_group_id;
                });

                $group_price = $variation_pg->first();
                $temp[$price_group->name] = ! empty($group_price) ? $group_price->price_inc_tax : '';
                $price_type_labels = [
                    'fixed' => 'Fixed',
                    'percentage_adjustment' => 'Percentage Adjustment',
                    'percentage' => 'Legacy Percentage',
                ];
                $temp[$price_group->name.' Price Type'] = ! empty($group_price)
                    ? ($price_type_labels[$group_price->price_type] ?? 'Fixed')
                    : 'Fixed';
            }
            $export_data[] = $temp;
        }

        if (ob_get_contents()) {
            ob_end_clean();
        }
        ob_start();

        return collect($export_data)->downloadExcel(
            'product_prices.xlsx',
            null,
            true
        );
    }

    /**
     * Imports the uploaded file to database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        try {
            $notAllowed = $this->commonUtil->notAllowedInDemo();
            if (! empty($notAllowed)) {
                return $notAllowed;
            }

            //Set maximum php execution time
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', -1);

            if ($request->hasFile('product_group_prices')) {
                $file = $request->file('product_group_prices');

                $parsed_array = Excel::toArray([], $file);

                $headers = $parsed_array[0][0];

                //Remove header row
                $imported_data = array_splice($parsed_array[0], 1);

                $business_id = $request->session()->get('user.business_id');
                $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->get();

                // Map each price group to its value and optional price-type columns.
                // Older exports only have the value column and remain supported.
                $imported_pgs = [];
                $recognized_columns = [];
                foreach ($price_groups as $price_group) {
                    $price_column = null;
                    $type_column = null;

                    foreach ($headers as $key => $header) {
                        if ($key <= 2 || empty($header)) {
                            continue;
                        }

                        $normalized_header = strtolower(trim($header));
                        if ($normalized_header === strtolower($price_group->name)) {
                            $price_column = $key;
                            $recognized_columns[] = $key;
                        } elseif ($normalized_header === strtolower($price_group->name.' Price Type')) {
                            $type_column = $key;
                            $recognized_columns[] = $key;
                        }
                    }

                    if (! is_null($price_column)) {
                        $imported_pgs[] = [
                            'group' => $price_group,
                            'price_column' => $price_column,
                            'type_column' => $type_column,
                        ];
                    }
                }

                foreach ($headers as $key => $header) {
                    if ($key > 2 && ! empty($header) && ! in_array($key, $recognized_columns, true)) {
                        throw new \Exception(__('lang_v1.price_group_not_found_exception', [
                            'pg' => $header,
                            'row' => 1,
                        ]));
                    }
                }

                $error_msg = '';
                DB::beginTransaction();

                foreach ($imported_data as $key => $value) {
                    $variation = Variation::where('sub_sku', $value[1])
                                        ->join('products', 'products.id', '=', 'variations.product_id')
                                        ->where('products.business_id', $business_id)
                                        ->select('variations.*')
                                        ->first();
                    if (empty($variation)) {
                        $row = $key + 1;
                        $error_msg = __('lang_v1.product_not_found_exception', ['sku' => $value[1], 'row' => $row]);

                        throw new \Exception($error_msg);
                    }

                    //Check if product base price is changed
                    if($variation->sell_price_inc_tax != $value[2]){
                        //update price for base selling price, adjust default_sell_price, profit %
                        $variation->sell_price_inc_tax = $value[2];
                        $tax = $variation->product->product_tax()->get();
                        $tax_percent = !empty($tax) && !empty($tax->first()) ? $tax->first()->amount : 0;
                        $variation->default_sell_price = $this->commonUtil->calc_percentage_base($value[2], $tax_percent);
                        $variation->profit_percent = $this->commonUtil
                                        ->get_percent($variation->default_purchase_price, $variation->default_sell_price);
                        $variation->update();
                    }

                    //update selling price
                    foreach ($imported_pgs as $imported_pg) {
                        $price = $value[$imported_pg['price_column']] ?? null;

                        if (! is_null($price) && $price !== '' && ! is_numeric($price)) {
                            $row = $key + 1;
                            $error_msg = __('lang_v1.price_group_non_numeric_exception', ['row' => $row]);

                            throw new \Exception($error_msg);
                        }

                        if (! is_null($price) && $price !== '') {
                            $price_type = 'fixed';
                            if (! is_null($imported_pg['type_column'])) {
                                $raw_price_type = strtolower(trim($value[$imported_pg['type_column']] ?? 'fixed'));
                                $price_type_aliases = [
                                    'fixed' => 'fixed',
                                    'percentage' => 'percentage',
                                    'percent' => 'percentage',
                                    '%' => 'percentage',
                                    'legacy percentage' => 'percentage',
                                    'percentage adjustment' => 'percentage_adjustment',
                                    'percent adjustment' => 'percentage_adjustment',
                                    '% adjustment' => 'percentage_adjustment',
                                    'adjustment' => 'percentage_adjustment',
                                    '+/- %' => 'percentage_adjustment',
                                ];

                                if (! isset($price_type_aliases[$raw_price_type])) {
                                    $row = $key + 1;
                                    throw new \Exception(__('lang_v1.price_group_invalid_price_type_exception', [
                                        'type' => $value[$imported_pg['type_column']] ?? '',
                                        'row' => $row,
                                    ]));
                                }

                                $price_type = $price_type_aliases[$raw_price_type];
                            }

                            if (($price_type === 'fixed' && $price < 0) ||
                                ($price_type === 'percentage_adjustment' && $price < -100)) {
                                throw new \Exception(__('lang_v1.invalid_group_price_adjustment'));
                            }

                            VariationGroupPrice::updateOrCreate(
                                [
                                    'variation_id' => $variation->id,
                                    'price_group_id' => $imported_pg['group']->id,
                                ],
                                [
                                    'price_inc_tax' => $price,
                                    'price_type' => $price_type,
                                ]
                            );
                        }
                    }
                }
                DB::commit();
            }
            $output = ['success' => 1,
                'msg' => __('lang_v1.product_prices_imported_successfully'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => $e->getMessage(),
            ];

            return redirect('update-product-price')->with('notification', $output);
        }

        return redirect('update-product-price')->with('status', $output);
    }

    /**
     * Activate/deactivate selling price group.
     */
    public function activateDeactivate($id)
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $spg = SellingPriceGroup::where('business_id', $business_id)->find($id);
            $spg->is_active = $spg->is_active == 1 ? 0 : 1;
            $spg->save();

            $output = ['success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];

            return $output;
        }
    }
}
