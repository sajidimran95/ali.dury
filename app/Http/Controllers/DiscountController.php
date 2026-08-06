<?php

namespace App\Http\Controllers;

use App\Brands;
use App\BusinessLocation;
use App\Category;
use App\Discount;
use App\SellingPriceGroup;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DiscountController extends Controller
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
        if (! auth()->user()->can('discount.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $discounts = Discount::where('discounts.business_id', $business_id)
                        ->leftjoin('brands as b', 'discounts.brand_id', '=', 'b.id')
                        ->leftjoin('categories as c', 'discounts.category_id', '=', 'c.id')
                        ->leftjoin('business_locations as l', 'discounts.location_id', '=', 'l.id')
                        ->select(['discounts.id', 'discounts.name', 'starts_at', 'ends_at',
                            'priority', 'b.name as brand', 'c.name as category', 'l.name as location', 'discounts.is_active', 'discounts.discount_amount', 'discount_type', ])
                        ->with(['variations', 'variations.product', 'variations.product_variation']);

            return Datatables::of($discounts)
                ->addColumn(
                    'action',
                    '<button data-href="{{action(\'App\Http\Controllers\DiscountController@edit\', [$id])}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary btn-modal" data-container=".discount_modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                        &nbsp;
                        <button data-href="{{action(\'App\Http\Controllers\DiscountController@destroy\', [$id])}}" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error delete_discount_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                        @if($is_active != 1)
                            &nbsp;
                            <button data-href="{{action(\'App\Http\Controllers\DiscountController@activate\', [$id])}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-accent activate-discount"><i class="fa fa-circle-o"></i> @lang("lang_v1.reactivate")</button>
                        @endif
                        '
                )
                ->addColumn('row_select', function ($row) {
                    return  '<input type="checkbox" class="row-select" value="'.$row->id.'">';
                })
                ->addColumn('products', function ($row) {
                    $products = [];

                    foreach ($row->variations as $variation) {
                        $products[] = $variation->full_name;
                    }

                    return empty($products)
                        ? '<span class="text-muted">—</span>'
                        : '<span class="label bg-primary">'.implode('</span>, <span class="label bg-primary">', $products).'</span>';
                })
                ->editColumn('name', function ($row) {
                    $name = $row->is_active != 1 ? $row->name.' <span class="label bg-yellow">'.__('lang_v1.inactive').'</sapn>' : $row->name;

                    return $name;
                })
                ->editColumn('brand', function ($row) {
                    return $row->brand ?: '—';
                })
                ->editColumn('category', function ($row) {
                    return $row->category ?: '—';
                })
                ->editColumn('location', function ($row) {
                    return $row->location ?: e(__('lang_v1.all_locations'));
                })
                ->editColumn('starts_at', function ($row) {
                    $starts_at = ! empty($row->starts_at) ? $this->commonUtil->format_date($row->starts_at->toDateTimeString(), true) : '';

                    return $starts_at;
                })
                ->editColumn('ends_at', function ($row) {
                    $ends_at = ! empty($row->ends_at) ? $this->commonUtil->format_date($row->ends_at->toDateTimeString(), true) : '';

                    return $ends_at;
                })
                ->editColumn('discount_amount', function ($row) {
                    $amount = $this->commonUtil->num_f($row->discount_amount);
                    $suffix = $row->discount_type == 'percentage' ? ' %' : '';

                    return '<span class="is-sum">'.$amount.$suffix.'</span>';
                })
                ->rawColumns(['name', 'action', 'row_select', 'products', 'discount_amount'])
                ->make(true);
        }

        $business_id = request()->session()->get('user.business_id');
        $discount_stats = $this->getDiscountListStats((int) $business_id);

        return view('discount.index')->with(compact('discount_stats'));
    }

    /**
     * KPI stats for discount list cards only.
     */
    protected function getDiscountListStats(int $businessId): array
    {
        $base = Discount::where('business_id', $businessId);
        $now = \Carbon\Carbon::now();

        $total = (int) (clone $base)->count();
        $active = (int) (clone $base)->where('is_active', 1)->count();
        $inactive = (int) (clone $base)->where(function ($q) {
            $q->where('is_active', '!=', 1)->orWhereNull('is_active');
        })->count();
        $running = (int) (clone $base)
            ->where('is_active', 1)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->count();
        $expired = (int) (clone $base)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', $now)
            ->count();
        $avgAmount = (float) (clone $base)->avg('discount_amount');
        $fixedSum = (float) (clone $base)->where('discount_type', 'fixed')->sum('discount_amount');
        $avgPercent = (float) (clone $base)->where('discount_type', 'percentage')->avg('discount_amount');

        return compact('total', 'active', 'inactive', 'running', 'expired', 'avgAmount', 'fixedSum', 'avgPercent');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! auth()->user()->can('discount.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $categories = $this->getProductCategoriesDropdown($business_id);

        $brands = Brands::forDropdown($business_id);

        $locations = BusinessLocation::forDropdown($business_id);
        if ($locations instanceof \Illuminate\Support\Collection) {
            $locations = $locations->toArray();
        }
        $locations = ['' => __('lang_v1.all_locations')] + $locations;

        $price_groups = SellingPriceGroup::forDropdown($business_id);

        $starts_at = $this->commonUtil->format_date(\Carbon\Carbon::now()->toDateTimeString(), true);
        $ends_at = $this->commonUtil->format_date(\Carbon\Carbon::now()->addMonths(1)->toDateTimeString(), true);

        return view('discount.create')
                ->with(compact('categories', 'brands', 'locations', 'price_groups', 'starts_at', 'ends_at'));
    }

    /**
     * Parent + subcategory labels for product discounts.
     */
    protected function getProductCategoriesDropdown(int $businessId): array
    {
        $all = Category::where('business_id', $businessId)
            ->where(function ($q) {
                $q->where('category_type', 'product')->orWhereNull('category_type');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        $byParent = $all->groupBy(function ($row) {
            return (int) ($row->parent_id ?? 0);
        });

        $dropdown = [];
        foreach ($byParent[0] ?? [] as $parent) {
            $dropdown[$parent->id] = $parent->name;
            foreach ($byParent[$parent->id] ?? [] as $child) {
                $dropdown[$child->id] = $parent->name.' → '.$child->name;
            }
        }

        // Orphans (parent missing)
        foreach ($all as $cat) {
            if (! isset($dropdown[$cat->id])) {
                $dropdown[$cat->id] = $cat->name;
            }
        }

        return $dropdown;
    }

    /**
     * Normalize create/update payload for discounts.
     */
    protected function sanitizeDiscountInput(array $input, Request $request): array
    {
        $input['brand_id'] = ! empty($input['brand_id']) ? $input['brand_id'] : null;
        $input['category_id'] = ! empty($input['category_id']) ? $input['category_id'] : null;
        $input['location_id'] = ! empty($input['location_id']) ? $input['location_id'] : null;
        $input['spg'] = (isset($input['spg']) && $input['spg'] !== '' && $input['spg'] !== null)
            ? (string) $input['spg']
            : null;
        $input['priority'] = $input['priority'] ?? 0;

        return $input;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('discount.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'brand_id', 'category_id',
                'location_id', 'priority', 'discount_type', 'discount_amount', 'spg', ]);

            $business_id = $request->session()->get('user.business_id');
            $input['business_id'] = $business_id;

            $variation_ids = $request->input('variation_ids');

            if (! empty($variation_ids)) {
                $input['brand_id'] = null;
                $input['category_id'] = null;
            }

            $input = $this->sanitizeDiscountInput($input, $request);

            $input['starts_at'] = $request->filled('starts_at') ? $this->commonUtil->uf_date($request->input('starts_at'), true) : null;
            $input['ends_at'] = $request->filled('ends_at') ? $this->commonUtil->uf_date($request->input('ends_at'), true) : null;
            $checkboxes = ['is_active', 'applicable_in_cg'];

            foreach ($checkboxes as $checkbox) {
                $input[$checkbox] = $request->has($checkbox) ? 1 : 0;
            }

            $discount = Discount::create($input);

            if (! empty($variation_ids)) {
                $discount->variations()->sync($variation_ids);
            }

            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! auth()->user()->can('discount.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $discount = Discount::where('business_id', $business_id)
                            ->with(['variations', 'variations.product', 'variations.product_variation'])
                            ->find($id);

            $starts_at = ! empty($discount->starts_at)
                ? $this->commonUtil->format_date($discount->starts_at->toDateTimeString(), true)
                : '';
            $ends_at = ! empty($discount->ends_at)
                ? $this->commonUtil->format_date($discount->ends_at->toDateTimeString(), true)
                : '';

            $categories = $this->getProductCategoriesDropdown($business_id);

            $brands = Brands::forDropdown($business_id);

            $locations = BusinessLocation::forDropdown($business_id);
            if ($locations instanceof \Illuminate\Support\Collection) {
                $locations = $locations->toArray();
            }
            $locations = ['' => __('lang_v1.all_locations')] + $locations;

            $variations = [];

            foreach ($discount->variations as $variation) {
                $variations[$variation->id] = $variation->full_name;
            }

            $price_groups = SellingPriceGroup::forDropdown($business_id);

            return view('discount.edit')
                ->with(compact('discount', 'starts_at', 'ends_at', 'brands', 'categories', 'locations', 'variations', 'price_groups'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('discount.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'brand_id', 'category_id',
                    'location_id', 'priority', 'discount_type', 'discount_amount', 'spg', ]);

                $business_id = $request->session()->get('user.business_id');

                $input['starts_at'] = $request->filled('starts_at') ? $this->commonUtil->uf_date($request->input('starts_at'), true) : null;
                $input['ends_at'] = $request->filled('ends_at') ? $this->commonUtil->uf_date($request->input('ends_at'), true) : null;
                $checkboxes = ['is_active', 'applicable_in_cg'];

                foreach ($checkboxes as $checkbox) {
                    $input[$checkbox] = $request->has($checkbox) ? 1 : 0;
                }

                $variation_ids = $request->input('variation_ids');

                if (! empty($variation_ids)) {
                    $input['brand_id'] = null;
                    $input['category_id'] = null;
                }

                $input = $this->sanitizeDiscountInput($input, $request);

                $discount = Discount::where('business_id', $business_id)
                            ->find($id);

                $discount->update($input);

                $discount->variations()->sync($variation_ids ?: []);

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
     * @param  \App\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('discount.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;

                $discount = Discount::where('business_id', $business_id)->findOrFail($id);
                $discount->delete();

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
     * Mass deactivates discounts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function massDeactivate(Request $request)
    {
        if (! auth()->user()->can('discount.access')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            if (! empty($request->input('selected_discounts'))) {
                $business_id = $request->session()->get('user.business_id');

                $selected_discounts = explode(',', $request->input('selected_discounts'));

                DB::beginTransaction();

                Discount::where('business_id', $business_id)
                            ->whereIn('id', $selected_discounts)
                            ->update(['is_active' => 0]);

                DB::commit();
            }

            $output = ['success' => 1,
                'msg' => __('lang_v1.deactivated_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * Activates the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function activate($id)
    {
        if (! auth()->user()->can('discount.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');
                Discount::where('id', $id)
                    ->where('business_id', $business_id)
                    ->update(['is_active' => 1]);

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
}
