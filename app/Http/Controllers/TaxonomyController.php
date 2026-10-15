<?php

namespace App\Http\Controllers;

use App\Category;
use App\Product;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TaxonomyController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $category_type = request()->get('type');
        if ($category_type == 'product' && ! auth()->user()->can('category.view') && ! auth()->user()->can('category.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $can_edit = true;
            if ($category_type == 'product' && ! auth()->user()->can('category.update')) {
                $can_edit = false;
            }

            $can_delete = true;
            if ($category_type == 'product' && ! auth()->user()->can('category.delete')) {
                $can_delete = false;
            }

            $business_id = request()->session()->get('user.business_id');

            $all_categories = Category::where('business_id', $business_id)
                ->where('category_type', $category_type)
                ->get()
                ->keyBy('id');

            $grouped = $all_categories->groupBy('parent_id');
            $msa_ids = $category_type === 'product' ? Category::msaCategoryIds((int) $business_id) : [];

            $category = collect();

            // Get parents (those with parent_id = null or 0)
            $parents = $grouped[null] ?? $grouped[0] ?? [];

            foreach ($parents as $parent) {
                // Push parent
                $category->push($parent);

                // Get and push children with prefixed parent name
                foreach ($grouped[$parent->id] ?? [] as $child) {
                    // Attach parent name for use in editColumn
                    $child->parent_name = $parent->name;
                    $category->push($child);

                    foreach ($grouped[$child->id] ?? [] as $grandchild) {
                        $grandchild->parent_name = $parent->name.' -> '.$child->name;
                        $category->push($grandchild);
                    }
                }
            }

            return Datatables::of($category)
                ->addColumn('action', function ($row) use ($can_edit, $can_delete, $category_type) {
                    $html = '';
                    if ($can_edit) {
                        $html .= '<button data-href="' . action([\App\Http\Controllers\TaxonomyController::class, 'edit'], [$row->id]) . '?type=' . $category_type . '" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary edit_category_button"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</button>';
                    }

                    if ($can_delete) {
                        $html .= '&nbsp;<button data-href="' . action([\App\Http\Controllers\TaxonomyController::class, 'destroy'], [$row->id]) . '" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error delete_category_button"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                    }

                    return $html;
                })
                ->editColumn('name', function ($row) {
                    // If parent_name is set (means it's a child)
                    if (!empty($row->parent_name)) {
                        return $row->parent_name . ' -> ' . $row->name;
                    }
                    return $row->name;
                })
                ->addColumn('show_in_pos', function ($row) use ($category_type, $msa_ids) {
                    if ($category_type !== 'product') {
                        return '';
                    }
                    $visible = ! isset($row->show_in_pos) || (int) $row->show_in_pos === 1;

                    $html = $visible
                        ? '<span class="label label-success">'.e(__('lang_v1.visible_on_pos')).'</span>'
                        : '<span class="label label-default">'.e(__('lang_v1.hidden_on_pos')).'</span>';
                    if (in_array((int) $row->id, $msa_ids, true)) {
                        $html .= ' <span class="label label-warning">'.e(__('lang_v1.msa_report_label')).'</span>';
                    }

                    return $html;
                })
                ->removeColumn('id')
                ->removeColumn('parent_id')
                ->rawColumns(['action', 'show_in_pos'])
                ->make(true);

            }

        $module_category_data = $this->moduleUtil->getTaxonomyData($category_type);
        $business_id = (int) request()->session()->get('user.business_id');
        $taxonomy_stats = $this->getTaxonomyListStats($business_id, $category_type);

        return view('taxonomy.index')->with(compact('module_category_data', 'taxonomy_stats'));
    }

    /**
     * KPI stats for taxonomy / category list cards.
     */
    protected function getTaxonomyListStats(int $businessId, ?string $categoryType): array
    {
        $type = $categoryType ?: 'product';

        $query = Category::where('business_id', $businessId)->where('category_type', $type);
        $total = (int) (clone $query)->count();
        $parents = (int) (clone $query)->where(function ($q) {
            $q->whereNull('parent_id')->orWhere('parent_id', 0);
        })->count();
        $children = max(0, $total - $parents);

        $productsInCat = 0;
        $productsNoCat = 0;
        if ($type === 'product') {
            $productsInCat = (int) Product::where('business_id', $businessId)
                ->where('type', '!=', 'modifier')
                ->whereNotNull('category_id')
                ->where('category_id', '>', 0)
                ->count();
            $productsNoCat = (int) Product::where('business_id', $businessId)
                ->where('type', '!=', 'modifier')
                ->where(function ($q) {
                    $q->whereNull('category_id')->orWhere('category_id', 0);
                })
                ->count();
        }

        return [
            'total' => $total,
            'parents' => $parents,
            'children' => $children,
            'products_in_cat' => $productsInCat,
            'products_no_cat' => $productsNoCat,
            'category_type' => $type,
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $category_type = request()->get('type');
        if ($category_type == 'product' && ! auth()->user()->can('category.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');

        $module_category_data = $this->moduleUtil->getTaxonomyData($category_type);

        $parent_categories = Category::parentOptions($business_id, $category_type);

        $quick_add = ! empty(request()->input('quick_add'));
        $quick_add_parent_id = request()->input('parent_id');

        return view('taxonomy.create')
                    ->with(compact('parent_categories', 'module_category_data', 'category_type', 'quick_add', 'quick_add_parent_id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * Measurement unit fields (oz, mg, gram…) for a product category.
     */
    protected function measureUnitInput(Request $request, int $business_id): array
    {
        if (! Category::hasMeasureUnitColumns()) {
            return [];
        }

        $enabled = (int) $request->input('measure_unit_enabled') === 1;
        $name = trim((string) $request->input('measure_unit_name'));
        if (! $enabled || $name === '') {
            return ['measure_unit_enabled' => 0, 'measure_unit_name' => null, 'measure_unit_id' => null];
        }

        $unit = Category::resolveMeasureUnit($business_id, mb_substr($name, 0, 30), (int) $request->session()->get('user.id'));

        return [
            'measure_unit_enabled' => 1,
            'measure_unit_name' => $unit ? $unit->short_name : $name,
            'measure_unit_id' => $unit?->id,
        ];
    }

    public function store(Request $request)
    {
        $category_type = request()->input('category_type');
        if ($category_type == 'product' && ! auth()->user()->can('category.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'short_code', 'category_type', 'description']);
            $input['business_id'] = $request->session()->get('user.business_id');
            if (! empty($request->input('add_as_sub_cat')) && $request->input('add_as_sub_cat') == 1 && ! empty($request->input('parent_id'))) {
                if (! $this->isAllowedParent((int) $input['business_id'], (int) $request->input('parent_id'))) {
                    return ['success' => false, 'msg' => __('category.max_three_levels')];
                }
                $input['parent_id'] = $request->input('parent_id');
            } else {
                $input['parent_id'] = 0;
            }
            $input['created_by'] = $request->session()->get('user.id');
            // Product categories: visible on POS by default when field not submitted
            if ($category_type === 'product') {
                $input['show_in_pos'] = $request->exists('show_in_pos')
                    ? ((int) $request->input('show_in_pos') === 1 ? 1 : 0)
                    : 1;
            }

            if ($category_type === 'product') {
                $input = array_merge($input, $this->measureUnitInput($request, (int) $input['business_id']));
            }

            $category = Category::create($input);
            if ($category_type === 'product' && $request->exists('needed_for_msa')) {
                Category::toggleMsaCategory((int) $input['business_id'], (int) $category->id, (int) $request->input('needed_for_msa') === 1);
            }
            $output = ['success' => true,
                'data' => $category,
                'msg' => __('category.added_success'),
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
     * Display the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $category_type = request()->get('type');
        if ($category_type == 'product' && ! auth()->user()->can('category.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $category = Category::where('business_id', $business_id)->find($id);

            $module_category_data = $this->moduleUtil->getTaxonomyData($category_type);

            // A category that already has children can only sit at top level or under a top-level category
            $has_children = Category::where('business_id', $business_id)->where('parent_id', $id)->exists();
            $parent_categories = $has_children
                ? Category::where('business_id', $business_id)
                    ->where('parent_id', 0)
                    ->where('category_type', $category_type)
                    ->where('id', '!=', $id)
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->toArray()
                : Category::parentOptions($business_id, $category_type, $id);
            $is_parent = false;

            if ($category->parent_id == 0) {
                $is_parent = true;
                $selected_parent = null;
            } else {
                $selected_parent = $category->parent_id;
            }

            $needed_for_msa = in_array((int) $category->id, Category::msaCategoryIds((int) $business_id), true);

            return view('taxonomy.edit')
                ->with(compact('category', 'parent_categories', 'is_parent', 'selected_parent', 'module_category_data', 'needed_for_msa'));
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
        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'description']);
                $business_id = $request->session()->get('user.business_id');

                $category = Category::where('business_id', $business_id)->findOrFail($id);

                if ($category->category_type == 'product' && ! auth()->user()->can('category.update')) {
                    abort(403, 'Unauthorized action.');
                }

                $category->name = $input['name'];
                $category->description = $input['description'];
                $category->short_code = $request->input('short_code');

                if (! empty($request->input('add_as_sub_cat')) && $request->input('add_as_sub_cat') == 1 && ! empty($request->input('parent_id'))) {
                    $parent_id = (int) $request->input('parent_id');
                    $has_children = Category::where('business_id', $business_id)->where('parent_id', $category->id)->exists();
                    $parent = Category::where('business_id', $business_id)->find($parent_id);
                    if ($parent_id === (int) $category->id
                        || ! $this->isAllowedParent((int) $business_id, $parent_id)
                        || ($has_children && $parent && (int) $parent->parent_id !== 0)) {
                        return ['success' => false, 'msg' => __('category.max_three_levels')];
                    }
                    $category->parent_id = $parent_id;
                } else {
                    $category->parent_id = 0;
                }
                if ($category->category_type === 'product' && $request->exists('show_in_pos')) {
                    $category->show_in_pos = (int) $request->input('show_in_pos') === 1 ? 1 : 0;
                }
                if ($category->category_type === 'product' && $request->exists('measure_unit_enabled')) {
                    $category->fill($this->measureUnitInput($request, (int) $business_id));
                }
                $category->save();
                if ($category->category_type === 'product' && $request->exists('needed_for_msa')) {
                    Category::toggleMsaCategory((int) $business_id, (int) $category->id, (int) $request->input('needed_for_msa') === 1);
                }

                $output = ['success' => true,
                    'msg' => __('category.updated_success'),
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
        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $category = Category::where('business_id', $business_id)->findOrFail($id);

                if ($category->category_type == 'product' && ! auth()->user()->can('category.delete')) {
                    abort(403, 'Unauthorized action.');
                }

                $category->delete();
                if (in_array((int) $category->id, Category::msaCategoryIds((int) $business_id), true)) {
                    Category::toggleMsaCategory((int) $business_id, (int) $category->id, false);
                }

                $output = ['success' => true,
                    'msg' => __('category.deleted_success'),
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
     * Parent must be a top-level category or a sub category (max 3 levels).
     */
    protected function isAllowedParent(int $business_id, int $parent_id): bool
    {
        $parent = Category::where('business_id', $business_id)->find($parent_id);
        if (! $parent) {
            return false;
        }
        if ((int) $parent->parent_id === 0) {
            return true;
        }
        $grand = Category::where('business_id', $business_id)->find($parent->parent_id);

        return $grand && (int) $grand->parent_id === 0;
    }

    public function getCategoriesApi()
    {
        try {
            $api_token = request()->header('API-TOKEN');

            $api_settings = $this->moduleUtil->getApiSettings($api_token);

            $categories = Category::catAndSubCategories($api_settings->business_id);
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            return $this->respondWentWrong($e);
        }

        return $this->respond($categories);
    }

    /**
     * get taxonomy index page
     * through ajax
     *
     * @return \Illuminate\Http\Response
     */
    public function getTaxonomyIndexPage(Request $request)
    {
        if (request()->ajax()) {
            $category_type = $request->get('category_type');
            $module_category_data = $this->moduleUtil->getTaxonomyData($category_type);

            return view('taxonomy.ajax_index')
                ->with(compact('module_category_data', 'category_type'));
        }
    }
}
