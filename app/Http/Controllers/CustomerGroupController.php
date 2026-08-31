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
        $this->customerGroupService->renameLegacyLabels($business_id);

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
        $total = (int) CustomerGroup::where('business_id', $businessId)->count();
        $percentage = (int) CustomerGroup::where('business_id', $businessId)
            ->where('price_calculation_type', 'percentage')
            ->count();
        $priceGroup = (int) CustomerGroup::where('business_id', $businessId)
            ->where('price_calculation_type', 'selling_price_group')
            ->count();
        $customersInGroup = (int) Contact::where('business_id', $businessId)
            ->whereIn('type', ['customer', 'both'])
            ->whereNotNull('customer_group_id')
            ->where('customer_group_id', '>', 0)
            ->count();
        $customersNoGroup = (int) Contact::where('business_id', $businessId)
            ->whereIn('type', ['customer', 'both'])
            ->where(function ($q) {
                $q->whereNull('customer_group_id')->orWhere('customer_group_id', 0);
            })
            ->count();
        $groupsInUse = (int) Contact::where('business_id', $businessId)
            ->whereIn('type', ['customer', 'both'])
            ->whereNotNull('customer_group_id')
            ->where('customer_group_id', '>', 0)
            ->distinct()
            ->count('customer_group_id');

        return [
            'total_groups' => $total,
            'percentage_type' => $percentage,
            'price_group_type' => $priceGroup,
            'groups_in_use' => $groupsInUse,
            'customers_in_group' => $customersInGroup,
            'customers_no_group' => $customersNoGroup,
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
        $this->customerGroupService->renameLegacyLabels($business_id);

        $customer_group = CustomerGroup::where('business_id', $business_id)->findOrFail($id);
        $price_group = $this->customerGroupService->ensurePriceGroup($customer_group);

        $customers_count = Contact::where('business_id', $business_id)
            ->where('customer_group_id', $customer_group->id)
            ->count();

        $priced_count = (int) VariationGroupPrice::where('price_group_id', $price_group->id)->count();

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
        $price_group = $this->customerGroupService->ensurePriceGroup($customer_group);

        $query = Variation::query()
            ->join('products as p', 'variations.product_id', '=', 'p.id')
            ->join('product_variations as pv', 'variations.product_variation_id', '=', 'pv.id')
            ->leftJoin('variation_group_prices as vgp', function ($join) use ($price_group) {
                $join->on('vgp.variation_id', '=', 'variations.id')
                    ->where('vgp.price_group_id', '=', $price_group->id);
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
            ->addColumn('group_price_input', function ($row) {
                $value = $row->group_price !== null ? $this->commonUtil->num_f($row->group_price) : '';

                return '<input type="text" class="form-control input_number js-group-price" data-variation-id="'.$row->variation_id.'" value="'.e($value).'" placeholder="'.$this->commonUtil->num_f($row->default_price).'">';
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

    public function applyAllProducts($id)
    {
        if (! auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = (int) request()->session()->get('user.business_id');
        $customer_group = CustomerGroup::where('business_id', $business_id)->findOrFail($id);
        $price_group = $this->customerGroupService->ensurePriceGroup($customer_group);
        $customer_group->refresh();

        $updated = 0;
        if ($customer_group->price_calculation_type === 'percentage' && $customer_group->amount !== null) {
            $updated = $this->customerGroupService->writeAllProductPricesFromPercent(
                $business_id,
                (int) $price_group->id,
                (float) $customer_group->amount
            );
        } else {
            $updated = SellingPriceGroup::seedAllInventoryPrices($business_id, (int) $price_group->id);
        }

        return [
            'success' => true,
            'msg' => __('lang_v1.group_prices_applied_all', ['count' => $updated]),
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
        $updated = $this->customerGroupService->applyPercentToGroup($customer_group, (float) $percent, true);

        return [
            'success' => true,
            'msg' => __('lang_v1.group_discount_saved', ['percent' => $percent, 'count' => $updated]),
        ];
    }
}
