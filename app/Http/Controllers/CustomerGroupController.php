<?php

namespace App\Http\Controllers;

use App\Contact;
use App\CustomerGroup;
use App\SellingPriceGroup;
use App\Services\CustomerGroupService;
use App\Utils\Util;
use App\Variation;
use App\VariationGroupPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CustomerGroupController extends Controller
{
    /**
     * Constructor
     *
     * @param  Util  $commonUtil
     * @return void
     */
    public function __construct(Util $commonUtil, protected CustomerGroupService $customerGroupService)
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
        if (! auth()->user()->can('customer.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = (int) request()->session()->get('user.business_id');

        if (request()->ajax()) {

            $customer_group = CustomerGroup::where('customer_groups.business_id', $business_id)
                                    ->leftjoin('selling_price_groups as spg', 'spg.id', '=', 'customer_groups.selling_price_group_id')
                                ->select([
                                    'customer_groups.name as name',
                                    DB::raw('(SELECT COUNT(*) FROM contacts WHERE contacts.customer_group_id = customer_groups.id AND contacts.business_id = customer_groups.business_id) as customers_count'),
                                    'customer_groups.amount as amount',
                                    'spg.name as selling_price_group',
                                    'customer_groups.id as id',
                                    'customer_groups.price_calculation_type as price_calculation_type',
                                ]);

            return Datatables::of($customer_group)
                    ->addColumn(
                        'action',
                        function ($row) {
                            $open = '<a href="'.action([self::class, 'show'], [$row->id]).'" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-primary tw-m-0.5">'.e(__('lang_v1.open_customer_group')).'</a>';
                            $edit = '';
                            $delete = '';
                            if (auth()->user()->can('customer.update')) {
                                $edit = '<button data-href="'.action([self::class, 'edit'], [$row->id]).'" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary tw-m-0.5 edit_customer_group_button"><i class="glyphicon glyphicon-edit"></i> '.e(__('messages.edit')).'</button>';
                            }
                            if (auth()->user()->can('customer.delete')) {
                                $delete = '<button data-href="'.action([self::class, 'destroy'], [$row->id]).'" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error tw-m-0.5 delete_customer_group_button"><i class="glyphicon glyphicon-trash"></i> '.e(__('messages.delete')).'</button>';
                            }

                            return $open.' '.$edit.' '.$delete;
                        }
                    )
                    ->editColumn('selling_price_group', function ($row) {
                        return $row->price_calculation_type == 'selling_price_group' ? ($row->selling_price_group ?: '--') : '--';
                    })
                    ->editColumn('amount', function ($row) {
                        return $row->price_calculation_type == 'percentage' ? $row->amount : '--';
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }

        $business_id = (int) request()->session()->get('user.business_id');
        $customer_group_stats = $this->getCustomerGroupListStats($business_id);

        return view('customer_group.index')->with(compact('customer_group_stats'));
    }

    /**
     * KPI stats for customer groups list cards.
     */
    protected function getCustomerGroupListStats(int $businessId): array
    {
        $groupCounts = CustomerGroup::where('business_id', $businessId)
            ->selectRaw('COUNT(*) as total_groups')
            ->selectRaw("SUM(CASE WHEN price_calculation_type = 'percentage' THEN 1 ELSE 0 END) as percentage_type")
            ->selectRaw("SUM(CASE WHEN price_calculation_type = 'selling_price_group' THEN 1 ELSE 0 END) as price_group_type")
            ->first();

        $customerCounts = Contact::where('business_id', $businessId)
            ->whereIn('type', ['customer', 'both'])
            ->selectRaw('SUM(CASE WHEN customer_group_id IS NOT NULL AND customer_group_id > 0 THEN 1 ELSE 0 END) as customers_in_group')
            ->selectRaw('SUM(CASE WHEN customer_group_id IS NULL OR customer_group_id = 0 THEN 1 ELSE 0 END) as customers_no_group')
            ->selectRaw('COUNT(DISTINCT CASE WHEN customer_group_id IS NOT NULL AND customer_group_id > 0 THEN customer_group_id END) as groups_in_use')
            ->first();

        return [
            'total_groups' => (int) ($groupCounts->total_groups ?? 0),
            'percentage_type' => (int) ($groupCounts->percentage_type ?? 0),
            'price_group_type' => (int) ($groupCounts->price_group_type ?? 0),
            'groups_in_use' => (int) ($customerCounts->groups_in_use ?? 0),
            'customers_in_group' => (int) ($customerCounts->customers_in_group ?? 0),
            'customers_no_group' => (int) ($customerCounts->customers_no_group ?? 0),
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! auth()->user()->can('customer.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $price_groups = SellingPriceGroup::forDropdown($business_id, false);

        return view('customer_group.create')->with(compact('price_groups'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('customer.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'amount', 'price_calculation_type', 'selling_price_group_id']);
            $input['business_id'] = $request->session()->get('user.business_id');
            $input['created_by'] = $request->session()->get('user.id');
            $input['amount'] = ! empty($input['amount']) ? $this->commonUtil->num_uf($input['amount']) : 0;
            if (empty($input['price_calculation_type'])) {
                $input['price_calculation_type'] = 'percentage';
            }
            $input['inventory_slug'] = 'all_inventory';

            $customer_group = CustomerGroup::create($input);
            $this->customerGroupService->ensurePriceGroup($customer_group);
            $output = ['success' => true,
                'data' => $customer_group,
                'msg' => __('lang_v1.success'),
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
     * @param  \App\CustomerGroup  $customerGroup
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $customer_group = CustomerGroup::where('business_id', $business_id)->find($id);

            $business_id = request()->session()->get('user.business_id');
            $price_groups = SellingPriceGroup::forDropdown($business_id, false);

            return view('customer_group.edit')
                ->with(compact('customer_group', 'price_groups'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'amount', 'price_calculation_type', 'selling_price_group_id']);
                $business_id = $request->session()->get('user.business_id');

                $input['amount'] = ! empty($input['amount']) ? $this->commonUtil->num_uf($input['amount']) : 0;

                $customer_group = CustomerGroup::where('business_id', $business_id)->findOrFail($id);

                $customer_group->update($input);
                $this->customerGroupService->ensurePriceGroup($customer_group);

                $output = ['success' => true,
                    'msg' => __('lang_v1.success'),
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('customer.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;

                $cg = CustomerGroup::where('business_id', $business_id)->findOrFail($id);
                $cg->delete();

                $output = ['success' => true,
                    'msg' => __('lang_v1.success'),
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

    public function show($id)
    {
        if (! auth()->user()->can('customer.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = (int) request()->session()->get('user.business_id');
        $customer_group = CustomerGroup::where('business_id', $business_id)->findOrFail($id);
        $price_group = ! empty($customer_group->selling_price_group_id)
            ? SellingPriceGroup::find($customer_group->selling_price_group_id)
            : $this->customerGroupService->ensurePriceGroup($customer_group);

        $customers_count = Contact::where('business_id', $business_id)
            ->where('customer_group_id', $customer_group->id)
            ->count();

        $priced_count = $price_group
            ? (int) VariationGroupPrice::where('price_group_id', $price_group->id)->count()
            : 0;

        return view('customer_group.show')->with(compact(
            'customer_group',
            'price_group',
            'customers_count',
            'priced_count'
        ));
    }

    public function members($id)
    {
        if (! auth()->user()->can('customer.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = (int) request()->session()->get('user.business_id');
        $customer_group = CustomerGroup::where('business_id', $business_id)->findOrFail($id);

        $customers = Contact::where('business_id', $business_id)
            ->where('customer_group_id', $customer_group->id)
            ->whereIn('type', ['customer', 'both'])
            ->select(['id', 'name', 'supplier_business_name', 'mobile', 'contact_id']);

        return Datatables::of($customers)
            ->addColumn('display_name', function ($row) {
                $name = ! empty($row->supplier_business_name) ? $row->supplier_business_name : $row->name;

                return e($name).(! empty($row->contact_id) ? ' <small class="text-muted">('.e($row->contact_id).')</small>' : '');
            })
            ->addColumn('action', function ($row) use ($customer_group) {
                if (! auth()->user()->can('customer.update')) {
                    return '';
                }

                return '<button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-error js-untag-customer" data-id="'.$row->id.'" data-href="'.action([self::class, 'removeMember'], [$customer_group->id, $row->id]).'">'.e(__('lang_v1.remove_from_group')).'</button>';
            })
            ->rawColumns(['display_name', 'action'])
            ->make(true);
    }

    public function addMembers(Request $request, $id)
    {
        if (! auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = (int) $request->session()->get('user.business_id');
        $customer_group = CustomerGroup::where('business_id', $business_id)->findOrFail($id);
        $ids = array_filter(array_map('intval', (array) $request->input('customer_ids', [])));

        if (empty($ids)) {
            return ['success' => false, 'msg' => __('lang_v1.select_customers_to_tag')];
        }

        Contact::where('business_id', $business_id)
            ->whereIn('type', ['customer', 'both'])
            ->whereIn('id', $ids)
            ->update(['customer_group_id' => $customer_group->id]);

        return ['success' => true, 'msg' => __('lang_v1.customers_tagged')];
    }

    public function removeMember($id, $contactId)
    {
        if (! auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = (int) request()->session()->get('user.business_id');
        CustomerGroup::where('business_id', $business_id)->findOrFail($id);

        Contact::where('business_id', $business_id)
            ->where('id', $contactId)
            ->update(['customer_group_id' => null]);

        return ['success' => true, 'msg' => __('lang_v1.customer_removed_from_group')];
    }

    public function products($id)
    {
        if (! auth()->user()->can('customer.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = (int) request()->session()->get('user.business_id');
        $customer_group = CustomerGroup::where('business_id', $business_id)->findOrFail($id);
        $price_group_id = $customer_group->selling_price_group_id ?: 0;
        $percent = $customer_group->price_calculation_type === 'percentage' ? (float) $customer_group->amount : 0;

        $query = Variation::query()
            ->join('products as p', 'variations.product_id', '=', 'p.id')
            ->join('product_variations as pv', 'variations.product_variation_id', '=', 'pv.id')
            ->leftJoin('variation_group_prices as vgp', function ($join) use ($price_group_id) {
                $join->on('vgp.variation_id', '=', 'variations.id')
                    ->where('vgp.price_group_id', '=', $price_group_id);
            })
            ->where('p.business_id', $business_id)
            ->whereIn('p.type', ['single', 'variable'])
            ->select([
                'variations.id as variation_id',
                'p.name as product_name',
                'variations.name as variation_name',
                'pv.name as product_variation_name',
                'p.type',
                'variations.sub_sku',
                'variations.sell_price_inc_tax as default_price',
                'vgp.price_inc_tax as group_price',
            ]);

        return Datatables::of($query)
            ->addColumn('product', function ($row) {
                if ($row->type === 'single') {
                    return e($row->product_name);
                }

                return e($row->product_name.' - '.$row->product_variation_name.' - '.$row->variation_name);
            })
            ->editColumn('default_price', function ($row) {
                return $this->commonUtil->num_f($row->default_price);
            })
            ->addColumn('group_price_input', function ($row) use ($percent) {
                if ($row->group_price !== null && $row->group_price !== '') {
                    $value = $this->commonUtil->num_f($row->group_price);
                } else {
                    $value = $this->commonUtil->num_f(((float) $row->default_price) * (1 + $percent / 100));
                }

                return '<input type="text" class="form-control input_number js-group-price" data-variation-id="'.$row->variation_id.'" value="'.e($value).'">';
            })
            ->rawColumns(['group_price_input'])
            ->make(true);
    }

    public function saveProducts(Request $request, $id)
    {
        if (! auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = (int) $request->session()->get('user.business_id');
        $customer_group = CustomerGroup::where('business_id', $business_id)->findOrFail($id);
        $price_group = $this->customerGroupService->ensurePriceGroup($customer_group);
        $prices = (array) $request->input('prices', []);
        $saved = 0;

        foreach ($prices as $row) {
            $variationId = (int) ($row['variation_id'] ?? 0);
            if ($variationId < 1 || ! array_key_exists('price', $row) || $row['price'] === '' || $row['price'] === null) {
                continue;
            }

            $belongs = Variation::query()
                ->join('products as p', 'variations.product_id', '=', 'p.id')
                ->where('p.business_id', $business_id)
                ->where('variations.id', $variationId)
                ->exists();
            if (! $belongs) {
                continue;
            }

            VariationGroupPrice::updateOrCreate(
                [
                    'variation_id' => $variationId,
                    'price_group_id' => $price_group->id,
                ],
                [
                    'price_inc_tax' => $this->commonUtil->num_uf($row['price']),
                    'price_type' => 'fixed',
                ]
            );
            $saved++;
        }

        return [
            'success' => true,
            'msg' => __('lang_v1.group_prices_saved', ['count' => $saved]),
        ];
    }

    public function saveDiscount(Request $request, $id)
    {
        if (! auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = (int) $request->session()->get('user.business_id');
        $customer_group = CustomerGroup::where('business_id', $business_id)->findOrFail($id);
        $percent = $this->commonUtil->num_uf($request->input('amount', 0));
        $this->customerGroupService->applyPercentToGroup($customer_group, (float) $percent, false);

        return [
            'success' => true,
            'msg' => __('lang_v1.group_discount_all_inventory', ['percent' => $percent]),
        ];
    }

    public function applyAllProducts($id)
    {
        if (! auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = (int) request()->session()->get('user.business_id');
        $customer_group = CustomerGroup::where('business_id', $business_id)->findOrFail($id);
        $percent = (float) $customer_group->amount;
        $this->customerGroupService->applyPercentToGroup($customer_group, $percent, false);

        return [
            'success' => true,
            'msg' => __('lang_v1.group_discount_all_inventory', ['percent' => $percent]),
        ];
    }

    public function searchProducts(Request $request, $id)
    {
        if (! auth()->user()->can('customer.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = (int) $request->session()->get('user.business_id');
        CustomerGroup::where('business_id', $business_id)->findOrFail($id);
        $term = trim((string) $request->input('q', ''));

        $rows = Variation::query()
            ->join('products as p', 'variations.product_id', '=', 'p.id')
            ->join('product_variations as pv', 'variations.product_variation_id', '=', 'pv.id')
            ->where('p.business_id', $business_id)
            ->whereIn('p.type', ['single', 'variable'])
            ->when($term !== '', function ($q) use ($term) {
                $q->where(function ($inner) use ($term) {
                    $inner->where('p.name', 'like', '%'.$term.'%')
                        ->orWhere('variations.sub_sku', 'like', '%'.$term.'%')
                        ->orWhere('p.sku', 'like', '%'.$term.'%');
                });
            })
            ->orderBy('p.name')
            ->limit(20)
            ->get([
                'variations.id',
                'p.name as product_name',
                'variations.name as variation_name',
                'pv.name as product_variation_name',
                'p.type',
                'variations.sub_sku',
            ]);

        $results = $rows->map(function ($row) {
            $label = $row->type === 'single'
                ? $row->product_name
                : $row->product_name.' - '.$row->product_variation_name.' - '.$row->variation_name;
            if (! empty($row->sub_sku)) {
                $label .= ' ('.$row->sub_sku.')';
            }

            return ['id' => $row->id, 'text' => $label];
        });

        return response()->json($results);
    }

    public function addProductOverride(Request $request, $id)
    {
        if (! auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = (int) $request->session()->get('user.business_id');
        $customer_group = CustomerGroup::where('business_id', $business_id)->findOrFail($id);
        $price_group = $this->customerGroupService->ensurePriceGroup($customer_group);
        $variationId = (int) $request->input('variation_id');

        $variation = Variation::query()
            ->join('products as p', 'variations.product_id', '=', 'p.id')
            ->where('p.business_id', $business_id)
            ->where('variations.id', $variationId)
            ->select('variations.*')
            ->first();

        if (! $variation) {
            return ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }

        $factor = 1 + (((float) $customer_group->amount) / 100);
        VariationGroupPrice::firstOrCreate(
            [
                'variation_id' => $variation->id,
                'price_group_id' => $price_group->id,
            ],
            [
                'price_inc_tax' => round(((float) ($variation->sell_price_inc_tax ?? 0)) * $factor, 4),
                'price_type' => 'fixed',
            ]
        );

        $customer_group->inventory_slug = 'all_inventory';
        $customer_group->save();

        return ['success' => true, 'msg' => __('lang_v1.group_product_override_added')];
    }
}
