<?php

namespace App\Http\Controllers;

use App\Brands;
use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Exports\ProductsExport;
use App\Media;
use App\Product;
use App\ProductBarcode;
use App\ProductVariation;
use App\PurchaseLine;
use App\SellingPriceGroup;
use App\TaxRate;
use App\Unit;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Variation;
use App\VariationGroupPrice;
use App\VariationLocationDetails;
use App\VariationTemplate;
use App\Warranty;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use App\Events\ProductsCreatedOrModified;
use App\TransactionSellLine;

class ProductController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $productUtil;

    protected $moduleUtil;

    private $barcode_types;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->moduleUtil = $moduleUtil;

        //barcode types
        $this->barcode_types = $this->productUtil->barcode_types();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('product.view') && ! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $selling_price_group_count = SellingPriceGroup::countSellingPriceGroups($business_id);
        $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');

        if (request()->ajax()) {
            //Filter by location
            $location_id = request()->get('location_id', null);
            $permitted_locations = auth()->user()->permitted_locations();

            $query = Product::with(['media'])
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->join('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('categories as c1', 'products.category_id', '=', 'c1.id')
                ->leftJoin('categories as c2', 'products.sub_category_id', '=', 'c2.id')
                ->leftJoin('categories as c3', 'products.child_category_id', '=', 'c3.id')
                ->leftJoin('tax_rates', 'products.tax', '=', 'tax_rates.id')
                ->join('variations as v', 'v.product_id', '=', 'products.id')
                ->leftJoin('variation_location_details as vld', function ($join) use ($permitted_locations) {
                    $join->on('vld.variation_id', '=', 'v.id');
                    if ($permitted_locations != 'all') {
                        $join->whereIn('vld.location_id', $permitted_locations);
                    }
                })
                ->whereNull('v.deleted_at')
                ->where('products.business_id', $business_id)
                ->where('products.type', '!=', 'modifier');

            if (! empty($location_id) && $location_id != 'none') {
                if ($permitted_locations == 'all' || in_array($location_id, $permitted_locations)) {
                    $query->whereHas('product_locations', function ($query) use ($location_id) {
                        $query->where('product_locations.location_id', '=', $location_id);
                    });
                }
            } elseif ($location_id == 'none') {
                $query->doesntHave('product_locations');
            } else {
                if ($permitted_locations != 'all') {
                    $query->whereHas('product_locations', function ($query) use ($permitted_locations) {
                        $query->whereIn('product_locations.location_id', $permitted_locations);
                    });
                } else {
                    $query->with('product_locations');
                }
            }

            $products = $query->select(
                'products.id',
                'products.name as product',
                'products.type',
                'c1.name as category',
                'c2.name as sub_category',
                'c3.name as child_category',
                'units.actual_name as unit',
                'brands.name as brand',
                'tax_rates.name as tax',
                'products.sku',
                'products.image',
                'products.enable_stock',
                'products.supplier_id',
                'products.is_inactive',
                'products.not_for_selling',
                'products.product_custom_field1', 'products.product_custom_field2', 'products.product_custom_field3', 'products.product_custom_field4', 'products.product_custom_field5', 'products.product_custom_field6',
                'products.product_custom_field7', 'products.product_custom_field8', 'products.product_custom_field9',
                'products.product_custom_field10', 'products.product_custom_field11', 'products.product_custom_field12',
                'products.product_custom_field13', 'products.product_custom_field14', 'products.product_custom_field15',
                'products.product_custom_field16', 'products.product_custom_field17', 'products.product_custom_field18', 
                'products.product_custom_field19', 'products.product_custom_field20',
                'products.alert_quantity',
                DB::raw('SUM(vld.qty_available) as current_stock'),
                DB::raw('MAX(v.sell_price_inc_tax) as max_price'),
                DB::raw('MIN(v.sell_price_inc_tax) as min_price'),
                DB::raw('MAX(v.dpp_inc_tax) as max_purchase_price'),
                DB::raw('MIN(v.dpp_inc_tax) as min_purchase_price')
                );

            //if woocomerce enabled add field to query
            if ($is_woocommerce) {
                $products->addSelect('woocommerce_disable_sync');
            }

            $products->groupBy('products.id');

            $type = request()->get('type', null);
            if (! empty($type)) {
                $products->where('products.type', $type);
            }

            $category_id = request()->get('category_id', null);
            if (! empty($category_id)) {
                $products->where('products.category_id', $category_id);
            }

            $sub_category_id = request()->get('sub_category_id', null);
            if (! empty($sub_category_id)) {
                $products->where('products.sub_category_id', $sub_category_id);
            }

            $child_category_id = request()->get('child_category_id', null);
            if (! empty($child_category_id)) {
                $products->where('products.child_category_id', $child_category_id);
            }

            $brand_id = request()->get('brand_id', null);
            if (! empty($brand_id)) {
                $products->where('products.brand_id', $brand_id);
            }

            $unit_id = request()->get('unit_id', null);
            if (! empty($unit_id)) {
                $products->where('products.unit_id', $unit_id);
            }

            $tax_id = request()->get('tax_id', null);
            if (! empty($tax_id)) {
                $products->where('products.tax', $tax_id);
            }

            $active_state = request()->get('active_state', null);
            if ($active_state == 'active') {
                $products->Active();
            }
            if ($active_state == 'inactive') {
                $products->Inactive();
            }
            $not_for_selling = request()->get('not_for_selling', null);
            if ($not_for_selling == 'true') {
                $products->ProductNotForSales();
            }

            $woocommerce_enabled = request()->get('woocommerce_enabled', 0);
            if ($woocommerce_enabled == 1) {
                $products->where('products.woocommerce_disable_sync', 0);
            }

            if (! empty(request()->get('repair_model_id'))) {
                $products->where('products.repair_model_id', request()->get('repair_model_id'));
            }

            return Datatables::of($products)
                ->addColumn(
                    'product_locations',
                    function ($row) {
                        return $row->product_locations->implode('name', ', ');
                    }
                )
                ->editColumn('category', '{{$category}} @if(!empty($sub_category))<br/> -- {{$sub_category}}@endif @if(!empty($child_category))<br/> &nbsp;&nbsp;-- {{$child_category}}@endif')
                ->addColumn(
                    'action',
                    function ($row) use ($selling_price_group_count) {
                        $html =
                        '<div class="btn-group"><button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-info tw-w-max dropdown-toggle" data-toggle="dropdown" aria-expanded="false">'.__('messages.actions').'<span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu dropdown-menu-left" role="menu"><li><a href="'.action([\App\Http\Controllers\LabelsController::class, 'show']).'?product_id='.$row->id.'" data-toggle="tooltip" title="'.__('lang_v1.label_help').'"><i class="fa fa-barcode"></i> '.__('barcode.print_labels').'</a></li>';

                        if (auth()->user()->can('product.view')) {
                            $html .=
                            '<li><a href="'.action([\App\Http\Controllers\ProductController::class, 'view'], [$row->id]).'" class="view-product"><i class="fa fa-eye"></i> '.__('messages.view').'</a></li>';
                        }

                        if (auth()->user()->can('product.update')) {
                            $html .=
                            '<li><a href="'.action([\App\Http\Controllers\ProductController::class, 'edit'], [$row->id]).'"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a></li>';
                        }

                        if (auth()->user()->can('product.delete')) {
                            $html .=
                            '<li><a href="'.action([\App\Http\Controllers\ProductController::class, 'destroy'], [$row->id]).'" class="delete-product"><i class="fa fa-trash"></i> '.__('messages.delete').'</a></li>';
                        }

                        if ($row->is_inactive == 1) {
                            $html .=
                            '<li><a href="'.action([\App\Http\Controllers\ProductController::class, 'activate'], [$row->id]).'" class="activate-product"><i class="fas fa-check-circle"></i> '.__('lang_v1.reactivate').'</a></li>';
                        }

                        $html .= '<li class="divider"></li>';

                        if (auth()->user()->can('purchase.create')) {
                            $purchase_params = ['product_id' => $row->id];
                            if (! empty($row->supplier_id)) {
                                $purchase_params['supplier_id'] = $row->supplier_id;
                            }
                            $html .=
                            '<li><a href="'.action([\App\Http\Controllers\PurchaseController::class, 'create'], $purchase_params).'"><i class="fas fa-cart-plus"></i> '.__('purchase.add_purchase').'</a></li>';
                        }

                        if ($row->enable_stock == 1 && auth()->user()->can('product.opening_stock')) {
                            $html .=
                            '<li><a href="#" data-href="'.action([\App\Http\Controllers\OpeningStockController::class, 'add'], ['product_id' => $row->id]).'" class="add-opening-stock"><i class="fa fa-database"></i> '.__('lang_v1.add_edit_opening_stock').'</a></li>';
                        }

                        if (auth()->user()->can('product.view')) {
                            $html .=
                            '<li><a href="'.action([\App\Http\Controllers\ProductController::class, 'productStockHistory'], [$row->id]).'"><i class="fas fa-history"></i> '.__('lang_v1.product_stock_history').'</a></li>';

                            $html .=
                            '<li><a href="'.action([\App\Http\Controllers\ProductController::class, 'inventoryTimeline'], [$row->id]).'"><i class="fas fa-stream"></i> '.__('lang_v1.inventory_timeline').'</a></li>';
                        }

                        if (auth()->user()->can('product.create')) {
                            if ($selling_price_group_count > 0) {
                                $html .=
                                '<li><a href="'.action([\App\Http\Controllers\ProductController::class, 'addSellingPrices'], [$row->id]).'"><i class="fas fa-money-bill-alt"></i> '.__('lang_v1.add_selling_price_group_prices').'</a></li>';
                            }

                            $html .=
                                '<li><a href="'.action([\App\Http\Controllers\ProductController::class, 'create'], ['d' => $row->id]).'"><i class="fa fa-copy"></i> '.__('lang_v1.duplicate_product').'</a></li>';
                        }

                        if (! empty($row->media->first())) {
                            $html .=
                                '<li><a href="'.$row->media->first()->display_url.'" download="'.$row->media->first()->display_name.'"><i class="fas fa-download"></i> '.__('lang_v1.product_brochure').'</a></li>';
                        }

                        $html .= '</ul></div>';

                        return $html;
                    }
                )
                ->editColumn('product', function ($row) use ($is_woocommerce) {
                    $product = $row->is_inactive == 1 ? e($row->product).' <span class="label bg-gray">'.__('lang_v1.inactive').'</span>' : e($row->product);

                    $product = $row->not_for_selling == 1 ? $product.' <span class="label bg-gray">'.__('lang_v1.not_for_selling').
                        '</span>' : $product;

                    if ($is_woocommerce && ! $row->woocommerce_disable_sync) {
                        $product = $product.'<br><i class="fab fa-wordpress"></i>';
                    }

                    return $product;
                })
                ->editColumn('image', function ($row) {
                    $placeholder = e(asset('/img/default.png'));
                    $src = e($row->image_url ?: asset('/img/default.png'));

                    return '<div style="display: flex;"><img src="'.$src.'" alt="" class="product-thumbnail-small" onerror="this.onerror=null;this.src=\''.$placeholder.'\';"></div>';
                })
                ->editColumn('type', '@lang("lang_v1." . $type)')
                ->addColumn('mass_delete', function ($row) {
                    return  '<input type="checkbox" class="row-select" value="'.$row->id.'">';
                })
                ->editColumn('current_stock', function ($row) {
                    if ($row->enable_stock) {
                        $stock = $this->productUtil->num_f($row->current_stock, false, null, true);

                        return '<span data-is_quantity="true" class="current_stock" data-orig-value="'.$stock.'" data-unit="'.$row->unit.'" >'.$stock.'</span> '.$row->unit;
                    } else {
                        return '--';
                    }
                })
                ->addColumn(
                    'purchase_price',
                    '<div style="white-space: nowrap;">@format_currency($min_purchase_price) @if($max_purchase_price != $min_purchase_price && $type == "variable") -  @format_currency($max_purchase_price)@endif </div>'
                )
                ->addColumn(
                    'selling_price',
                    '<div style="white-space: nowrap;">@format_currency($min_price) @if($max_price != $min_price && $type == "variable") -  @format_currency($max_price)@endif </div>'
                )
                ->filterColumn('products.sku', function ($query, $keyword) {
                    $query->whereHas('variations', function ($q) use ($keyword) {
                        $q->where('sub_sku', 'like', "%{$keyword}%");
                    })
                    ->orWhere('products.sku', 'like', "%{$keyword}%");
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can('product.view')) {
                            return  action([\App\Http\Controllers\ProductController::class, 'view'], [$row->id]);
                        } else {
                            return '';
                        }
                    }, ])
                ->rawColumns(['action', 'image', 'mass_delete', 'product', 'selling_price', 'purchase_price', 'category', 'current_stock'])
                ->make(true);
        }

        $rack_enabled = (request()->session()->get('business.enable_racks') || request()->session()->get('business.enable_row') || request()->session()->get('business.enable_position'));

        $categories = Category::forDropdown($business_id, 'product');

        $brands = Brands::forDropdown($business_id);

        $units = Unit::forDropdown($business_id);

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, false);
        $taxes = $tax_dropdown['tax_rates'];

        $business_locations = BusinessLocation::forDropdown($business_id);
        $business_locations->prepend(__('lang_v1.none'), 'none');

        if ($this->moduleUtil->isModuleInstalled('Manufacturing') && (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'manufacturing_module'))) {
            $show_manufacturing_data = true;
        } else {
            $show_manufacturing_data = false;
        }

        //list product screen filter from module
        $pos_module_data = $this->moduleUtil->getModuleData('get_filters_for_list_product_screen');

        $is_admin = $this->productUtil->is_admin(auth()->user());

        // Dashboard-style KPI cards (same source as inventory dashboard)
        $permitted_locations = auth()->user()->permitted_locations();
        $product_stats = app(InventoryDashboardController::class)->getStats(
            (int) $business_id,
            $permitted_locations,
            null
        );
        $product_type_counts = Product::where('business_id', $business_id)
            ->where('type', '!=', 'modifier')
            ->where('is_inactive', 0)
            ->selectRaw('type, COUNT(*) as c')
            ->groupBy('type')
            ->pluck('c', 'type')
            ->toArray();
        $product_stats['type_single'] = (int) ($product_type_counts['single'] ?? 0);
        $product_stats['type_variable'] = (int) ($product_type_counts['variable'] ?? 0);
        $product_stats['type_combo'] = (int) ($product_type_counts['combo'] ?? 0);
        $product_stats['inactive'] = (int) Product::where('business_id', $business_id)
            ->where('type', '!=', 'modifier')
            ->where('is_inactive', 1)
            ->count();
        $product_stats['not_for_selling'] = (int) Product::where('business_id', $business_id)
            ->where('type', '!=', 'modifier')
            ->where('is_inactive', 0)
            ->where('not_for_selling', 1)
            ->count();

        return view('product.index')
            ->with(compact(
                'rack_enabled',
                'categories',
                'brands',
                'units',
                'taxes',
                'business_locations',
                'show_manufacturing_data',
                'pos_module_data',
                'is_woocommerce',
                'is_admin',
                'product_stats'
            ));
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

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not, then check for products quota
        if (! $this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (! $this->moduleUtil->isQuotaAvailable('products', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('products', $business_id, action([\App\Http\Controllers\ProductController::class, 'index']));
        }

        $categories = Category::forDropdown($business_id, 'product');

        $brands = Brands::forDropdown($business_id);
        $suppliers = \App\Contact::suppliersDropdown($business_id, false);
        $units = Unit::forDropdown($business_id, true);

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
        $taxes = $tax_dropdown['tax_rates'];
        $tax_attributes = $tax_dropdown['attributes'];

        $barcode_types = $this->barcode_types;
        $barcode_default = $this->productUtil->barcode_default();

        $default_profit_percent = request()->session()->get('business.default_profit_percent');

        //Get all business locations
        $business_locations = BusinessLocation::forDropdown($business_id);

        //Duplicate product
        $duplicate_product = null;
        $rack_details = null;

        $sub_categories = [];
        $child_categories = [];
        if (! empty(request()->input('d'))) {
            $duplicate_product = Product::where('business_id', $business_id)->find(request()->input('d'));
            $duplicate_product->name .= ' (copy)';

            if (! empty($duplicate_product->category_id)) {
                $sub_categories = Category::where('business_id', $business_id)
                        ->where('parent_id', $duplicate_product->category_id)
                        ->pluck('name', 'id')
                        ->toArray();
            }
            if (! empty($duplicate_product->sub_category_id)) {
                $child_categories = Category::where('business_id', $business_id)
                        ->where('parent_id', $duplicate_product->sub_category_id)
                        ->pluck('name', 'id')
                        ->toArray();
            }

            //Rack details
            if (! empty($duplicate_product->id)) {
                $rack_details = $this->productUtil->getRackDetails($business_id, $duplicate_product->id);
            }
        }

        $selling_price_group_count = SellingPriceGroup::countSellingPriceGroups($business_id);

        $module_form_parts = $this->moduleUtil->getModuleData('product_form_part');
        $product_types = $this->product_types();

        $common_settings = session()->get('business.common_settings');
        $warranties = Warranty::forDropdown($business_id);

        //product screen view from module
        $pos_module_data = $this->moduleUtil->getModuleData('get_product_screen_top_view');

        $enable_expiry = request()->session()->get('business.enable_product_expiry');
        $enable_lot = request()->session()->get('business.enable_lot_number');

        $product_barcodes = collect();
        $packaging_helper = [];
        if (! empty($duplicate_product)) {
            $product_barcodes = ProductBarcode::where('product_id', $duplicate_product->id)
                ->orderBy('id')
                ->get()
                ->map(function ($row) {
                    $row->id = null; // create fresh rows on save

                    return $row;
                });
            $packaging_helper = $this->getPackagingHelperForProduct($business_id, $duplicate_product);
        }

        return view('product.create')
            ->with(compact('categories', 'brands', 'suppliers', 'units', 'taxes', 'barcode_types', 'default_profit_percent', 'tax_attributes', 'barcode_default', 'business_locations', 'duplicate_product', 'sub_categories', 'child_categories', 'rack_details', 'selling_price_group_count', 'module_form_parts', 'product_types', 'common_settings', 'warranties', 'pos_module_data', 'enable_expiry', 'enable_lot', 'product_barcodes', 'packaging_helper'));
    }

    private function product_types()
    {
        //Product types also includes modifier.
        return ['single' => __('lang_v1.single'),
            'variable' => __('lang_v1.variable'),
            'combo' => __('lang_v1.combo'),
        ];
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
            $business_id = $request->session()->get('user.business_id');
            $has_inline_opening_stock = false;
            $form_fields = ['name', 'brand_id', 'unit_id', 'category_id', 'tax', 'type', 'barcode_type', 'sku', 'alert_quantity', 'tax_type', 'weight', 'product_description', 'sub_unit_ids', 'preparation_time_in_minutes', 'product_custom_field1', 'product_custom_field2', 'product_custom_field3', 'product_custom_field4', 'product_custom_field5', 'product_custom_field6', 'product_custom_field7', 'product_custom_field8', 'product_custom_field9', 'product_custom_field10', 'product_custom_field11', 'product_custom_field12', 'product_custom_field13', 'product_custom_field14', 'product_custom_field15', 'product_custom_field16', 'product_custom_field17', 'product_custom_field18', 'product_custom_field19', 'product_custom_field20',];

            $module_form_fields = $this->moduleUtil->getModuleFormField('product_form_fields');
            if (! empty($module_form_fields)) {
                $form_fields = array_merge($form_fields, $module_form_fields);
            }

            $product_details = $request->only($form_fields);
            $product_details['business_id'] = $business_id;
            $product_details['created_by'] = $request->session()->get('user.id');

            $product_details['enable_stock'] = (! empty($request->input('enable_stock')) && $request->input('enable_stock') == 1) ? 1 : 0;
            $product_details['not_for_selling'] = (! empty($request->input('not_for_selling')) && $request->input('not_for_selling') == 1) ? 1 : 0;
            $product_details['supplier_id'] = $request->input('supplier_id') ?: null;

            if (! empty($request->input('sub_category_id'))) {
                $product_details['sub_category_id'] = $request->input('sub_category_id');
                if (! empty($request->input('child_category_id'))) {
                    $product_details['child_category_id'] = $request->input('child_category_id');
                }
            }

            if (! empty($request->input('secondary_unit_id'))) {
                $product_details['secondary_unit_id'] = $request->input('secondary_unit_id');
            }

            if (empty($product_details['sku'])) {
                $product_details['sku'] = ' ';
            }

            if (! empty($product_details['alert_quantity'])) {
                $product_details['alert_quantity'] = $this->productUtil->num_uf($product_details['alert_quantity']);
            }

            $expiry_enabled = $request->session()->get('business.enable_product_expiry');
            if (! empty($request->input('expiry_period_type')) && ! empty($request->input('expiry_period')) && ! empty($expiry_enabled) && ($product_details['enable_stock'] == 1)) {
                $product_details['expiry_period_type'] = $request->input('expiry_period_type');
                $product_details['expiry_period'] = $this->productUtil->num_uf($request->input('expiry_period'));
            }

            if (! empty($request->input('enable_sr_no')) && $request->input('enable_sr_no') == 1) {
                $product_details['enable_sr_no'] = 1;
            }

            //upload document
            $product_details['image'] = $this->productUtil->uploadFile($request, 'image', config('constants.product_img_path'), 'image');
            $common_settings = session()->get('business.common_settings');

            $product_details['warranty_id'] = ! empty($request->input('warranty_id')) ? $request->input('warranty_id') : null;

            $product_details = $this->applyCategoryMeasureUnit($request, $product_details, $business_id);
            $product_details = $this->applyPackagingUnitFromRequest($request, $product_details, $business_id);

            DB::beginTransaction();

            $product = Product::create($product_details);

            event(new ProductsCreatedOrModified($product_details, 'added'));

            if (empty(trim($request->input('sku')))) {
                $sku = $this->productUtil->generateProductSku($product->id);
                $product->sku = $sku;
                $product->save();
            }

            //Add product locations
            $product_locations = $request->input('product_locations');
            if (! empty($product_locations)) {
                $product->product_locations()->sync($product_locations);
            }

            if ($product->type == 'single') {
                $prices = $this->normalizeSinglePrices($request->only(['single_dpp', 'single_dpp_inc_tax', 'single_dsp', 'single_dsp_inc_tax', 'profit_percent']), $product->tax);
                $this->productUtil->createSingleProductVariation($product->id, $product->sku, $prices['single_dpp'], $prices['single_dpp_inc_tax'], $prices['profit_percent'], $prices['single_dsp'], $prices['single_dsp_inc_tax']);
            } elseif ($product->type == 'variable') {
                if (! empty($request->input('product_variation'))) {
                    $input_variations = $request->input('product_variation');
                    
                    $this->productUtil->createVariableProductVariations($product->id, $input_variations, $request->input('sku_type'));
                }
            } elseif ($product->type == 'combo') {

                //Create combo_variations array by combining variation_id and quantity.
                $combo_variations = [];
                if (! empty($request->input('composition_variation_id'))) {
                    $composition_variation_id = $request->input('composition_variation_id');
                    $quantity = $request->input('quantity');
                    $unit = $request->input('unit');

                    foreach ($composition_variation_id as $key => $value) {
                        $combo_variations[] = [
                            'variation_id' => $value,
                            'quantity' => $this->productUtil->num_uf($quantity[$key]),
                            'unit_id' => $unit[$key],
                        ];
                    }
                }

                $this->productUtil->createSingleProductVariation($product->id, $product->sku, $request->input('item_level_purchase_price_total'), $request->input('purchase_price_inc_tax'), $request->input('profit_percent'), $request->input('selling_price'), $request->input('selling_price_inc_tax'), $combo_variations);
            }

            //Add product racks details.
            $product_racks = $request->get('product_racks', null);
            if (! empty($product_racks)) {
                $this->productUtil->addRackDetails($business_id, $product->id, $product_racks);
            }

            //Set Module fields
            if (! empty($request->input('has_module_data'))) {
                $this->moduleUtil->getModuleData('after_product_saved', ['product' => $product, 'request' => $request]);
            }

            Media::uploadMedia($product->business_id, $product, $request, 'product_brochure', true);

            $this->syncProductBarcodesFromRequest($request, $product, $business_id);

            $has_inline_opening_stock = false;
            if (
                $product->enable_stock == 1
                && $product->type == 'single'
                && auth()->user()->can('product.opening_stock')
                && ! empty($request->input('opening_stock'))
            ) {
                $os_input = $request->input('opening_stock');
                $has_qty = false;
                foreach ($os_input as $os_row) {
                    if ($this->productUtil->num_uf($os_row['quantity'] ?? 0) > 0) {
                        $has_qty = true;
                        break;
                    }
                }
                if ($has_qty) {
                    $product = Product::where('business_id', $business_id)
                        ->with(['variations', 'product_tax'])
                        ->findOrFail($product->id);

                    $user_id = $request->session()->get('user.id');
                    $transaction_date = $request->session()->get('financial_year.start');
                    $transaction_date = \Carbon::createFromFormat('Y-m-d', $transaction_date)->toDateTimeString();

                    $this->productUtil->addSingleProductOpeningStock(
                        $business_id,
                        $product,
                        $os_input,
                        $transaction_date,
                        $user_id
                    );
                    $has_inline_opening_stock = true;
                }
            }

            DB::commit();
            $output = ['success' => 1,
                'msg' => __('product.product_added_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => $this->productFormErrorMessage($e),
            ];

            return redirect('products')->with('status', $output);
        }

        if ($request->input('submit_type') == 'submit_n_add_opening_stock' && empty($has_inline_opening_stock)) {
            return redirect()->action([\App\Http\Controllers\OpeningStockController::class, 'add'],
                ['product_id' => $product->id]
            );
        } elseif ($request->input('submit_type') == 'submit_n_add_selling_prices') {
            return redirect()->action([\App\Http\Controllers\ProductController::class, 'addSellingPrices'],
                [$product->id]
            );
        } elseif ($request->input('submit_type') == 'save_n_add_another') {
            return redirect()->action([\App\Http\Controllers\ProductController::class, 'create']
            )->with('status', $output);
        }

        return redirect('products')->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (! auth()->user()->can('product.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $details = $this->productUtil->getRackDetails($business_id, $id, true);

        return view('product.show')->with(compact('details'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $categories = Category::forDropdown($business_id, 'product');
        $brands = Brands::forDropdown($business_id);
        $suppliers = \App\Contact::suppliersDropdown($business_id, false);

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
        $taxes = $tax_dropdown['tax_rates'];
        $tax_attributes = $tax_dropdown['attributes'];

        $barcode_types = $this->barcode_types;

        $product = Product::where('business_id', $business_id)
                            ->with(['product_locations'])
                            ->where('id', $id)
                            ->firstOrFail();

        //Sub-category
        $sub_categories = [];
        $sub_categories = Category::where('business_id', $business_id)
                        ->where('parent_id', $product->category_id)
                        ->pluck('name', 'id')
                        ->toArray();
        $sub_categories = ['' => 'None'] + $sub_categories;

        $child_categories = [];
        if (! empty($product->sub_category_id)) {
            $child_categories = Category::where('business_id', $business_id)
                        ->where('parent_id', $product->sub_category_id)
                        ->pluck('name', 'id')
                        ->toArray();
        }
        $child_categories = ['' => 'None'] + $child_categories;

        $default_profit_percent = request()->session()->get('business.default_profit_percent');

        //Get units.
        $units = Unit::forDropdown($business_id, true);
        $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit_id, true);

        //Get all business locations
        $business_locations = BusinessLocation::forDropdown($business_id);
        //Rack details
        $rack_details = $this->productUtil->getRackDetails($business_id, $id);

        $selling_price_group_count = SellingPriceGroup::countSellingPriceGroups($business_id);

        $module_form_parts = $this->moduleUtil->getModuleData('product_form_part');
        $product_types = $this->product_types();
        $common_settings = session()->get('business.common_settings');
        $warranties = Warranty::forDropdown($business_id);

        //product screen view from module
        $pos_module_data = $this->moduleUtil->getModuleData('get_product_screen_top_view');

        $alert_quantity = ! is_null($product->alert_quantity) ? $this->productUtil->num_f($product->alert_quantity, false, null, true) : null;

        $packaging_helper = $this->getPackagingHelperForProduct($business_id, $product);
        $product_barcodes = ProductBarcode::where('product_id', $product->id)->orderBy('id')->get();

        return view('product.edit')
                ->with(compact('categories', 'brands', 'suppliers', 'units', 'sub_units', 'taxes', 'tax_attributes', 'barcode_types', 'product', 'sub_categories', 'child_categories', 'default_profit_percent', 'business_locations', 'rack_details', 'selling_price_group_count', 'module_form_parts', 'product_types', 'common_settings', 'warranties', 'pos_module_data', 'alert_quantity', 'packaging_helper', 'product_barcodes'));
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
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');
            $product_details = $request->only(['name', 'brand_id', 'unit_id', 'category_id', 'tax', 'barcode_type', 'sku', 'alert_quantity', 'tax_type', 'weight', 'product_description', 'sub_unit_ids', 'preparation_time_in_minutes', 'product_custom_field1', 'product_custom_field2', 'product_custom_field3', 'product_custom_field4', 'product_custom_field5', 'product_custom_field6', 'product_custom_field7', 'product_custom_field8', 'product_custom_field9', 'product_custom_field10', 'product_custom_field11', 'product_custom_field12', 'product_custom_field13', 'product_custom_field14', 'product_custom_field15', 'product_custom_field16', 'product_custom_field17', 'product_custom_field18', 'product_custom_field19', 'product_custom_field20',]);

            $product_details = $this->applyCategoryMeasureUnit($request, $product_details, $business_id);
            $product_details = $this->applyPackagingUnitFromRequest($request, $product_details, $business_id);

            DB::beginTransaction();

            $product = Product::where('business_id', $business_id)
                                ->where('id', $id)
                                ->with(['product_variations'])
                                ->first();

            $common_settings = session()->get('business.common_settings') ?? [];

            // Keep existing values when optional fields are hidden by settings
            if (empty($common_settings['enable_weight'])) {
                $product_details['weight'] = $product->weight;
            }
            if (empty($common_settings['enable_preparation_time'])) {
                $product_details['preparation_time_in_minutes'] = $product->preparation_time_in_minutes;
            }

            $module_form_fields = $this->moduleUtil->getModuleFormField('product_form_fields');
            if (! empty($module_form_fields)) {
                foreach ($module_form_fields as $column) {
                    $product->$column = $request->input($column);
                }
            }

            $product->name = $product_details['name'];
            $product->brand_id = $product_details['brand_id'];
            $product->unit_id = $product_details['unit_id'];
            $product->category_id = $product_details['category_id'];
            $product->tax = $product_details['tax'];
            $product->barcode_type = $product_details['barcode_type'];
            $product->sku = $product_details['sku'];
            $product->alert_quantity = ! empty($product_details['alert_quantity']) ? $this->productUtil->num_uf($product_details['alert_quantity']) : $product_details['alert_quantity'];
            $product->tax_type = $product_details['tax_type'];
            $product->weight = $product_details['weight'];
            $product->product_custom_field1 = $product_details['product_custom_field1'] ?? '';
            $product->product_custom_field2 = $product_details['product_custom_field2'] ?? '';
            $product->product_custom_field3 = $product_details['product_custom_field3'] ?? '';
            $product->product_custom_field4 = $product_details['product_custom_field4'] ?? '';
            $product->product_custom_field5 = $product_details['product_custom_field5'] ?? '';
            $product->product_custom_field6 = $product_details['product_custom_field6'] ?? '';
            $product->product_custom_field7 = $product_details['product_custom_field7'] ?? '';
            $product->product_custom_field8 = $product_details['product_custom_field8'] ?? '';
            $product->product_custom_field9 = $product_details['product_custom_field9'] ?? '';
            $product->product_custom_field10 = $product_details['product_custom_field10'] ?? '';
            $product->product_custom_field11 = $product_details['product_custom_field11'] ?? '';
            $product->product_custom_field12 = $product_details['product_custom_field12'] ?? '';
            $product->product_custom_field13 = $product_details['product_custom_field13'] ?? '';
            $product->product_custom_field14 = $product_details['product_custom_field14'] ?? '';
            $product->product_custom_field15 = $product_details['product_custom_field15'] ?? '';
            $product->product_custom_field16 = $product_details['product_custom_field16'] ?? '';
            $product->product_custom_field17 = $product_details['product_custom_field17'] ?? '';
            $product->product_custom_field18 = $product_details['product_custom_field18'] ?? '';
            $product->product_custom_field19 = $product_details['product_custom_field19'] ?? '';
            $product->product_custom_field20 = $product_details['product_custom_field20'] ?? '';

            $product->product_description = $product_details['product_description'];
            $product->sub_unit_ids = ! empty($product_details['sub_unit_ids']) ? $product_details['sub_unit_ids'] : null;
            $product->preparation_time_in_minutes = $product_details['preparation_time_in_minutes'];
            $product->warranty_id = ! empty($request->input('warranty_id')) ? $request->input('warranty_id') : null;
            $product->secondary_unit_id = ! empty($request->input('secondary_unit_id')) ? $request->input('secondary_unit_id') : null;
            if ($request->has('supplier_id')) {
                $product->supplier_id = $request->input('supplier_id') ?: null;
            }

            if (! empty($request->input('enable_stock')) && $request->input('enable_stock') == 1) {
                $product->enable_stock = 1;
            } else {
                $product->enable_stock = 0;
            }

            if (! empty($common_settings['enable_not_for_selling'])) {
                $product->not_for_selling = (! empty($request->input('not_for_selling')) && $request->input('not_for_selling') == 1) ? 1 : 0;
            }

            if (! empty($request->input('sub_category_id'))) {
                $product->sub_category_id = $request->input('sub_category_id');
                $product->child_category_id = $request->input('child_category_id') ?: null;
            } else {
                $product->sub_category_id = null;
                $product->child_category_id = null;
            }

            $expiry_enabled = $request->session()->get('business.enable_product_expiry');
            if (! empty($expiry_enabled)) {
                if (! empty($request->input('expiry_period_type')) && ! empty($request->input('expiry_period')) && ($product->enable_stock == 1)) {
                    $product->expiry_period_type = $request->input('expiry_period_type');
                    $product->expiry_period = $this->productUtil->num_uf($request->input('expiry_period'));
                } else {
                    $product->expiry_period_type = null;
                    $product->expiry_period = null;
                }
            }

            if (! empty($common_settings['enable_imei_sr_no'])) {
                if (! empty($request->input('enable_sr_no')) && $request->input('enable_sr_no') == 1) {
                    $product->enable_sr_no = 1;
                } else {
                    $product->enable_sr_no = 0;
                }
            }

            //upload document
            $file_name = $this->productUtil->uploadFile($request, 'image', config('constants.product_img_path'), 'image');
            if (! empty($file_name)) {

                //If previous image found then remove
                if (! empty($product->image_path) && file_exists($product->image_path)) {
                    unlink($product->image_path);
                }

                $product->image = $file_name;
                //If product image is updated update woocommerce media id
                if (! empty($product->woocommerce_media_id)) {
                    $product->woocommerce_media_id = null;
                }
            }

            $product->save();
            $product->touch();

            event(new ProductsCreatedOrModified($product, 'updated'));

            //Add product locations
            $product_locations = ! empty($request->input('product_locations')) ?
                                $request->input('product_locations') : [];

            $permitted_locations = auth()->user()->permitted_locations();
            //If not assigned location exists don't remove it
            if ($permitted_locations != 'all') {
                $existing_product_locations = $product->product_locations()->pluck('id');

                foreach ($existing_product_locations as $pl) {
                    if (! in_array($pl, $permitted_locations)) {
                        $product_locations[] = $pl;
                    }
                }
            }

            $product->product_locations()->sync($product_locations);

            if ($product->type == 'single') {
                $single_data = $request->only(['single_variation_id', 'single_dpp', 'single_dpp_inc_tax', 'single_dsp_inc_tax', 'profit_percent', 'single_dsp']);
                $variation = Variation::find($single_data['single_variation_id']);

                $variation->sub_sku = $product->sku;
                $prices = $this->normalizeSinglePrices($single_data, $product->tax);
                $variation->default_purchase_price = $prices['single_dpp'];
                $variation->dpp_inc_tax = $prices['single_dpp_inc_tax'];
                $variation->profit_percent = $prices['profit_percent'];
                $variation->default_sell_price = $prices['single_dsp'];
                $variation->sell_price_inc_tax = $prices['single_dsp_inc_tax'];
                $variation->save();

                Media::uploadMedia($product->business_id, $variation, $request, 'variation_images');
            } elseif ($product->type == 'variable') {
                //Update existing variations
                $input_variations_edit = $request->get('product_variation_edit');
                if (! empty($input_variations_edit)) {
                    $this->productUtil->updateVariableProductVariations($product->id, $input_variations_edit,$request->input('sku_type'));
                }

                //Add new variations created.
                $input_variations = $request->input('product_variation');
                if (! empty($input_variations)) {
                    $this->productUtil->createVariableProductVariations($product->id, $input_variations, $request->input('sku_type'));
                }
            } elseif ($product->type == 'combo') {

                //Create combo_variations array by combining variation_id and quantity.
                $combo_variations = [];
                if (! empty($request->input('composition_variation_id'))) {
                    $composition_variation_id = $request->input('composition_variation_id');
                    $quantity = $request->input('quantity');
                    $unit = $request->input('unit');

                    foreach ($composition_variation_id as $key => $value) {
                        $combo_variations[] = [
                            'variation_id' => $value,
                            'quantity' => $quantity[$key],
                            'unit_id' => $unit[$key],
                        ];
                    }
                }

                $variation = Variation::find($request->input('combo_variation_id'));
                $variation->sub_sku = $product->sku;
                $variation->default_purchase_price = $this->productUtil->num_uf($request->input('item_level_purchase_price_total'));
                $variation->dpp_inc_tax = $this->productUtil->num_uf($request->input('purchase_price_inc_tax'));
                $variation->profit_percent = $this->productUtil->num_uf($request->input('profit_percent'));
                $variation->default_sell_price = $this->productUtil->num_uf($request->input('selling_price'));
                $variation->sell_price_inc_tax = $this->productUtil->num_uf($request->input('selling_price_inc_tax'));
                $variation->combo_variations = $combo_variations;
                $variation->save();
            }

            //Add product racks details.
            $product_racks = $request->get('product_racks', null);
            if (! empty($product_racks)) {
                $this->productUtil->addRackDetails($business_id, $product->id, $product_racks);
            }

            $product_racks_update = $request->get('product_racks_update', null);
            if (! empty($product_racks_update)) {
                $this->productUtil->updateRackDetails($business_id, $product->id, $product_racks_update);
            }

            //Set Module fields
            if (! empty($request->input('has_module_data'))) {
                $this->moduleUtil->getModuleData('after_product_saved', ['product' => $product, 'request' => $request]);
            }

            Media::uploadMedia($product->business_id, $product, $request, 'product_brochure', true);

            $this->syncProductBarcodesFromRequest($request, $product, $business_id);

            DB::commit();
            $output = ['success' => 1,
                'msg' => __('product.product_updated_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => $this->productFormErrorMessage($e),
            ];
        }

        if ($request->input('submit_type') == 'update_n_edit_opening_stock') {
            return redirect()->action([\App\Http\Controllers\OpeningStockController::class, 'add'],
                ['product_id' => $product->id]
            );
        } elseif ($request->input('submit_type') == 'submit_n_add_selling_prices') {
            return redirect()->action([\App\Http\Controllers\ProductController::class, 'addSellingPrices'],
                [$product->id]
            );
        } elseif ($request->input('submit_type') == 'save_n_add_another') {
            return redirect()->action([\App\Http\Controllers\ProductController::class, 'create']
            )->with('status', $output);
        }

        return redirect('products')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('product.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $product = Product::where('id', $id)
                                ->where('business_id', $business_id)
                                ->with('variations')
                                ->first();

                if (empty($product)) {
                    return ['success' => false, 'msg' => __('lang_v1.recycle_item_not_found')];
                }

                //Check if product is added as an ingredient of any recipe
                if ($this->moduleUtil->isModuleInstalled('Manufacturing')) {
                    $variation_ids = $product->variations->pluck('id');

                    $exists_as_ingredient = \Modules\Manufacturing\Entities\MfgRecipeIngredient::whereIn('variation_id', $variation_ids)
                        ->exists();
                    if ($exists_as_ingredient) {
                        return ['success' => false, 'msg' => __('manufacturing::lang.added_as_ingredient')];
                    }
                }

                // Soft delete: product moves to the Recycle Bin (Settings > System).
                // Invoices, purchases and reports keep showing it; stock rows are kept for restore.
                DB::beginTransaction();
                (new \App\Utils\RecycleBinUtil())->softDeleteProduct($product, auth()->id());
                DB::commit();

                $output = ['success' => true,
                    'msg' => __('lang_v1.product_moved_to_recycle_bin'),
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
    }

    /**
     * Get subcategories list for a category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getSubCategories(Request $request)
    {
        if (! empty($request->input('cat_id'))) {
            $category_id = $request->input('cat_id');
            $business_id = $request->session()->get('user.business_id');
            $sub_categories = Category::where('business_id', $business_id)
                        ->where('parent_id', $category_id)
                        ->select(['name', 'id'])
                        ->get();
            $html = '<option value="">None</option>';
            if (! empty($sub_categories)) {
                foreach ($sub_categories as $sub_category) {
                    $html .= '<option value="'.$sub_category->id.'">'.$sub_category->name.'</option>';
                }
            }
            echo $html;
            exit;
        }
    }

    /**
     * Get product form parts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getProductVariationFormPart(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $business = Business::findorfail($business_id);
        $profit_percent = $business->default_profit_percent;

        $action = $request->input('action');
        if ($request->input('action') == 'add') {
            if ($request->input('type') == 'single') {
                return view('product.partials.single_product_form_part')
                        ->with(['profit_percent' => $profit_percent]);
            } elseif ($request->input('type') == 'variable') {
                $variation_templates = VariationTemplate::where('business_id', $business_id)->pluck('name', 'id')->toArray();
                $variation_templates = ['' => __('messages.please_select')] + $variation_templates;

                return view('product.partials.variable_product_form_part')
                        ->with(compact('variation_templates', 'profit_percent', 'action'));
            } elseif ($request->input('type') == 'combo') {
                return view('product.partials.combo_product_form_part')
                ->with(compact('profit_percent', 'action'));
            }
        } elseif ($request->input('action') == 'edit' || $request->input('action') == 'duplicate') {
            $product_id = $request->input('product_id');
            $action = $request->input('action');
            if ($request->input('type') == 'single') {
                $product_deatails = ProductVariation::where('product_id', $product_id)
                    ->with(['variations', 'variations.media'])
                    ->first();

                return view('product.partials.edit_single_product_form_part')
                            ->with(compact('product_deatails', 'action'));
            } elseif ($request->input('type') == 'variable') {
                $product_variations = ProductVariation::where('product_id', $product_id)
                        ->with(['variations', 'variations.media'])
                        ->get();

                return view('product.partials.variable_product_form_part')
                        ->with(compact('product_variations', 'profit_percent', 'action'));
            } elseif ($request->input('type') == 'combo') {
                $product_deatails = ProductVariation::where('product_id', $product_id)
                    ->with(['variations', 'variations.media'])
                    ->first();
                $combo_variations = $this->productUtil->__getComboProductDetails($product_deatails['variations'][0]->combo_variations, $business_id);

                $variation_id = $product_deatails['variations'][0]->id;
                $profit_percent = $product_deatails['variations'][0]->profit_percent;

                return view('product.partials.combo_product_form_part')
                ->with(compact('combo_variations', 'profit_percent', 'action', 'variation_id'));
            }
        }
    }

    /**
     * Get product form parts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getVariationValueRow(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $business = Business::findorfail($business_id);
        $profit_percent = $business->default_profit_percent;

        $variation_index = $request->input('variation_row_index');
        $value_index = $request->input('value_index') + 1;

        $row_type = $request->input('row_type', 'add');

        return view('product.partials.variation_value_row')
                ->with(compact('profit_percent', 'variation_index', 'value_index', 'row_type'));
    }

    /**
     * Get product form parts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getProductVariationRow(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $business = Business::findorfail($business_id);
        $profit_percent = $business->default_profit_percent;

        $variation_templates = VariationTemplate::where('business_id', $business_id)
                                                ->pluck('name', 'id')->toArray();
        $variation_templates = ['' => __('messages.please_select')] + $variation_templates;

        $row_index = $request->input('row_index', 0);
        $action = $request->input('action');

        return view('product.partials.product_variation_row')
                    ->with(compact('variation_templates', 'row_index', 'action', 'profit_percent'));
    }

    /**
     * Get product form parts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getVariationTemplate(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $business = Business::findorfail($business_id);
        $profit_percent = $business->default_profit_percent;

        $template = VariationTemplate::where('id', $request->input('template_id'))
                                                ->with(['values'])
                                                ->first();
        $row_index = $request->input('row_index');

        $values = [];
        foreach ($template->values as $v) {
            $values[] = [
                'id' => $v->id,
                'text' => $v->name,
            ];
        }

        return [
            'html' => view('product.partials.product_variation_template')
                    ->with(compact('template', 'row_index', 'profit_percent'))->render(),
            'values' => $values,
        ];
    }

    /**
     * Return the view for combo product row
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getComboProductEntryRow(Request $request)
    {
        if (request()->ajax()) {
            $product_id = $request->input('product_id');
            $variation_id = $request->input('variation_id');
            $business_id = $request->session()->get('user.business_id');

            if (! empty($product_id)) {
                $product = Product::where('id', $product_id)
                        ->with(['unit'])
                        ->first();

                $query = Variation::where('product_id', $product_id)
                        ->with(['product_variation']);

                if ($variation_id !== '0') {
                    $query->where('id', $variation_id);
                }
                $variations = $query->get();

                $sub_units = $this->productUtil->getSubUnits($business_id, $product['unit']->id);

                return view('product.partials.combo_product_entry_row')
                ->with(compact('product', 'variations', 'sub_units'));
            }
        }
    }

    /**
     * Retrieves products list.
     *
     * @param  string  $q
     * @param  bool  $check_qty
     * @return JSON
     */
    public function getProducts()
    {
        if (request()->ajax()) {
            $search_term = trim((string) request()->input('term', ''));
            $search_term = preg_replace('/[\x00-\x1F\x7F]/', '', $search_term);
            $search_term = trim((string) $search_term);
            $location_id = request()->input('location_id', null);
            $check_qty = request()->input('check_qty', false);
            $price_group_id = request()->input('price_group', null);
            $business_id = request()->session()->get('user.business_id');
            $not_for_selling = request()->get('not_for_selling', null);
            $price_group_id = request()->input('price_group', '');
            $product_types = request()->get('product_types', []);

            $search_fields = request()->get('search_fields', ['name', 'sku']);
            if (in_array('sku', $search_fields)) {
                $search_fields[] = 'sub_sku';
            }

            $auto_add_single = filter_var(request()->get('auto_add_single', false), FILTER_VALIDATE_BOOLEAN);
            // scan = exact SKU/barcode only; search = partial name/SKU list
            $search_mode = strtolower((string) request()->get('search_mode', ''));
            $is_scan_mode = ($search_mode === 'scan');
            // Hardware scanners always send a full code; treat numeric barcodes as scan
            // so leading-zero UPC/EAN variants match even if the UI toggle was on Search.
            if (! $is_scan_mode && $auto_add_single && $search_term !== '' && preg_match('/^\d{8,14}$/', $search_term)) {
                $is_scan_mode = true;
            }

            if ($is_scan_mode) {
                // Barcode scanners must match full SKU only (not SKU-2 when scanning SKU)
                $search_fields = ['sku', 'sub_sku'];
            }

            $result = collect();
            $scan_terms = $is_scan_mode
                ? $this->barcodeScanCandidates($search_term)
                : ($search_term !== '' ? $this->barcodeScanCandidates($search_term) : []);

            $alt_variation_ids = $this->variationIdsForBarcodeTerms($business_id, $scan_terms);
            $matched_barcode = $this->findProductBarcodeMatch($business_id, $scan_terms);

            // Exact SKU/name first — Scan mode only.
            // Search mode must use partial LIKE so "geek" returns all Geek* products,
            // not only an exact SKU match like "GEEK".
            if ($is_scan_mode) {
                foreach ($scan_terms as $term) {
                    $result = $this->productUtil->filterProduct(
                        $business_id,
                        $term,
                        $location_id,
                        $not_for_selling,
                        $price_group_id,
                        $product_types,
                        $search_fields,
                        $check_qty,
                        'exact'
                    );

                    if ($result->count() > 1) {
                        $exactOnly = $result->filter(function ($item) use ($term, $alt_variation_ids) {
                            $sub = html_entity_decode((string) ($item->sub_sku ?? ''), ENT_QUOTES, 'UTF-8');
                            $sku = html_entity_decode((string) ($item->sku ?? ''), ENT_QUOTES, 'UTF-8');

                            return strcasecmp(trim($sub), $term) === 0
                                || ($sku !== '' && strcasecmp(trim($sku), $term) === 0)
                                || in_array((int) $item->variation_id, $alt_variation_ids, true);
                        })->values();

                        if ($exactOnly->isNotEmpty()) {
                            $result = $exactOnly;
                        }
                    }

                    if ($result->isNotEmpty()) {
                        break;
                    }
                }
            }

            // Search mode (and scan fallback if exact found nothing): partial LIKE
            if ($result->isEmpty() && $search_term !== '') {
                foreach ($scan_terms as $term) {
                    $likeResult = $this->productUtil->filterProduct(
                        $business_id,
                        $term,
                        $location_id,
                        $not_for_selling,
                        $price_group_id,
                        $product_types,
                        $search_fields,
                        $check_qty,
                        'like'
                    );

                    if ($is_scan_mode) {
                        $result = $likeResult->filter(function ($item) use ($term, $alt_variation_ids) {
                            $sub = html_entity_decode((string) ($item->sub_sku ?? ''), ENT_QUOTES, 'UTF-8');
                            $sku = html_entity_decode((string) ($item->sku ?? ''), ENT_QUOTES, 'UTF-8');

                            return strcasecmp(trim($sub), $term) === 0
                                || ($sku !== '' && strcasecmp(trim($sku), $term) === 0)
                                || in_array((int) $item->variation_id, $alt_variation_ids, true);
                        })->values();
                    } else {
                        $result = $likeResult;
                    }

                    if ($result->isNotEmpty()) {
                        break;
                    }
                }
            }

            // Alternate / QR barcode matched a row even if SKU text did not
            if ($result->isEmpty() && ! empty($matched_barcode) && ! empty($matched_barcode->variation_id)) {
                $posOk = Product::where('business_id', $business_id)
                    ->where('id', $matched_barcode->product_id)
                    ->forPos()
                    ->exists();
                if ($posOk) {
                    $sku = Product::where('id', $matched_barcode->product_id)->value('sku');
                    if (! empty($sku)) {
                        $result = $this->productUtil->filterProduct(
                            $business_id,
                            $sku,
                            $location_id,
                            $not_for_selling,
                            $price_group_id,
                            $product_types,
                            ['sku', 'sub_sku'],
                            $check_qty,
                            'exact'
                        );
                        if ($result->count() > 1) {
                            $result = $result->filter(function ($item) use ($matched_barcode) {
                                return (int) $item->variation_id === (int) $matched_barcode->variation_id;
                            })->values();
                        }
                    }
                }
            }

            $noStore = ['Cache-Control' => 'no-store, no-cache, must-revalidate'];

            // If only one result and location_id is provided (POS context), auto-fetch the product row
            if (count($result) == 1 && ! empty($location_id) && $auto_add_single) {
                
                $variation_id = $result[0]->variation_id;

                if (! empty($matched_barcode) && (int) $matched_barcode->variation_id === (int) $variation_id
                    && $matched_barcode->type === 'pack' && ! empty($matched_barcode->unit_id)) {
                    request()->merge(['sub_unit_id' => $matched_barcode->unit_id]);
                }
                        
                $row_data = $this->productUtil->getPosProductRow($variation_id, $location_id);
                
                // Add variation_id to row_data for duplicate checking
                $row_data['variation_id'] = $variation_id;
                if (! empty($matched_barcode) && $matched_barcode->type === 'pack' && ! empty($matched_barcode->unit_id)) {
                    $row_data['matched_sub_unit_id'] = $matched_barcode->unit_id;
                }
                
                return response()->json([
                    'auto_add' => true,
                    'row_data' => $row_data
                ], 200, $noStore);
            }

            // Attach pack unit hint for client-side single-product add
            if ($result->isNotEmpty() && ! empty($matched_barcode) && $matched_barcode->type === 'pack') {
                $result = $result->map(function ($item) use ($matched_barcode) {
                    if ((int) $item->variation_id === (int) $matched_barcode->variation_id) {
                        $item->matched_sub_unit_id = $matched_barcode->unit_id;
                    }

                    return $item;
                });
            }

            // POS auto_add requests always get a stable envelope
            if ($auto_add_single) {
                $msg = null;
                if ($result->isEmpty() && ! empty($matched_barcode)) {
                    $msg = __('lang_v1.product_not_for_pos');
                }

                return response()->json([
                    'auto_add' => false,
                    'products' => $result->values()->all(),
                    'msg' => $msg,
                ], 200, $noStore);
            }

            // Other modules (stock transfer, etc.) expect a bare product array
            return response()->json($result->values()->all(), 200, $noStore);
        }
    }
    /**
     * Get multiple variation details in a single request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVariationDetailsBulk(Request $request)
    {
        if (!request()->ajax()) {
            abort(404);
        }

        $business_id = $request->session()->get('user.business_id');
        $location_id = $request->input('location_id');
        $ids_param = $request->input('ids', '');

        if (empty($location_id) || empty($ids_param)) {
            return response()->json([]);
        }

        // Support both comma separated string or array
        $variation_ids = is_array($ids_param)
            ? $ids_param
            : array_filter(array_map('trim', explode(',', (string) $ids_param)), function ($v) {
                return $v !== '';
            });

        $details = [];
        foreach ($variation_ids as $vid) {
            try {
                $product = $this->productUtil->getDetailsFromVariation($vid, $business_id, $location_id, null);
                $details[$vid] = $product;
            } catch (\Exception $e) {
                \Log::warning('Bulk variation detail fetch failed for id: '.$vid.' msg: '.$e->getMessage());
                $details[$vid] = null;
            }
        }

        return response()->json($details);
    }

    /**
     * Retrieves products list without variation list
     *
     * @param  string  $q
     * @param  bool  $check_qty
     * @return JSON
     */
    public function getProductsWithoutVariations()
    {
        if (request()->ajax()) {
            $term = request()->input('term', '');
            //$location_id = request()->input('location_id', '');

            //$check_qty = request()->input('check_qty', false);

            $business_id = request()->session()->get('user.business_id');

            $products = Product::join('variations', 'products.id', '=', 'variations.product_id')
                ->where('products.business_id', $business_id)
                ->where('products.type', '!=', 'modifier');

            //Include search
            if (! empty($term)) {
                $products->where(function ($query) use ($term) {
                    $query->where('products.name', 'like', '%'.$term.'%');
                    $query->orWhere('sku', 'like', '%'.$term.'%');
                    $query->orWhere('sub_sku', 'like', '%'.$term.'%');
                });
            }

            //Include check for quantity
            // if($check_qty){
            //     $products->where('VLD.qty_available', '>', 0);
            // }

            $products = $products->groupBy('products.id')
                ->select(
                    'products.id as product_id',
                    'products.name',
                    'products.type',
                    'products.enable_stock',
                    'products.sku',
                    'products.id as id',
                    DB::raw('CONCAT(products.name, " - ", products.sku) as text')
                )
                    ->orderBy('products.name')
                    ->get();

            return json_encode($products);
        }
    }

    /**
     * Checks if product sku already exists.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkProductSku(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $sku = trim((string) $request->input('sku'));
        $product_id = $request->input('product_id');

        if ($sku === '') {
            return response()->json(true);
        }

        $taken = $this->barcodeIsTaken($business_id, $sku, $product_id ?: null, $request->input('variation_id'));

        return response()->json(! $taken);
    }

     /**
     * Checks if product name already exists.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkProductName(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $name = $request->input('name');
        $product_id = $request->input('product_id');

        //check in products table
        $query = Product::where('business_id', $business_id)
                        ->where('name', $name);
        if (! empty($product_id)) {
            $query->where('id', '!=', $product_id);
        }
        $count = $query->count();

        //check in variation table if $count = 0
        
        if ($count == 0) {
            echo 'true';
            exit;
        } else {
            echo 'false';
            exit;
        }
    }

    /**
     * Validates multiple variation skus
     */
    public function validateVaritionSkus(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $all_skus = $request->input('skus');

        $skus = [];
        foreach ($all_skus as $key => $value) {
            $skus[] = $value['sku'];
        }

        //check product table is sku present
        $product = Product::where('business_id', $business_id)
                        ->whereIn('sku', $skus)
                        ->first();

        if (! empty($product)) {
            return ['success' => 0, 'sku' => $product->sku];
        }

        foreach ($all_skus as $key => $value) {
            $query = Variation::where('sub_sku', $value['sku'])
                            ->join('products', 'variations.product_id', '=', 'products.id')
                            ->where('business_id', $business_id);

            if (! empty($value['variation_id'])) {
                $query->where('variations.id', '!=', $value['variation_id']);
            }
            $variation = $query->first();

            if (! empty($variation)) {
                return ['success' => 0, 'sku' => $variation->sub_sku];
            }
        }

        return ['success' => 1];
    }

    /**
     * Loads quick add product modal.
     *
     * @return \Illuminate\Http\Response
     */
    public function quickAdd()
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        $product_name = ! empty(request()->input('product_name')) ? request()->input('product_name') : '';

        // Carried over from JapsAI invoice extraction when an unmatched line item is
        // sent to "Add new product" — pre-fills the barcode/SKU printed on the invoice
        // and its unit price so the user isn't retyping what the invoice already shows.
        $product_sku = ! empty(request()->input('sku')) ? request()->input('sku') : null;
        $product_purchase_price = is_numeric(request()->input('purchase_price')) ? request()->input('purchase_price') : null;

        $product_for = ! empty(request()->input('product_for')) ? request()->input('product_for') : null;

        $business_id = request()->session()->get('user.business_id');
        $categories = Category::forDropdown($business_id, 'product');
        $brands = Brands::forDropdown($business_id);
        $units = Unit::forDropdown($business_id, true);
        // Quick-add is a fast path (used from JapsAI's missing-product flows, among others) —
        // don't force picking a unit every time. Default to the business's first-defined
        // unit; the field stays editable in case it's the wrong one for this product.
        $default_unit_id = $units->except([''])->keys()->first();

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
        $taxes = $tax_dropdown['tax_rates'];
        $tax_attributes = $tax_dropdown['attributes'];

        $barcode_types = $this->barcode_types;

        $default_profit_percent = Business::where('id', $business_id)->value('default_profit_percent');

        $locations = BusinessLocation::forDropdown($business_id);

        $enable_expiry = request()->session()->get('business.enable_product_expiry');
        $enable_lot = request()->session()->get('business.enable_lot_number');

        $module_form_parts = $this->moduleUtil->getModuleData('product_form_part');

        //Get all business locations
        $business_locations = BusinessLocation::forDropdown($business_id);

        $common_settings = session()->get('business.common_settings');
        $warranties = Warranty::forDropdown($business_id);

        return view('product.partials.quick_add_product')
                ->with(compact('categories', 'brands', 'units', 'default_unit_id', 'taxes', 'barcode_types', 'default_profit_percent', 'tax_attributes', 'product_name', 'product_sku', 'product_purchase_price', 'locations', 'product_for', 'enable_expiry', 'enable_lot', 'module_form_parts', 'business_locations', 'common_settings', 'warranties'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function saveQuickProduct(Request $request)
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        // check for products quota
        if(! $this->moduleUtil->isQuotaAvailable('products', $business_id)) {
            return $output = ['success' => 0,
                'msg' => __('superadmin::lang.max_products'),
            ];
        }

        try {
            $form_fields = ['name', 'brand_id', 'unit_id', 'category_id', 'tax', 'barcode_type', 'tax_type', 'sku',
                'alert_quantity', 'type', 'sub_unit_ids', 'sub_category_id', 'weight', 'product_description', 'product_custom_field1', 'product_custom_field2', 'product_custom_field3', 'product_custom_field4', 'product_custom_field5', 'product_custom_field6', 'product_custom_field7', 'product_custom_field8', 'product_custom_field9', 'product_custom_field10', 'product_custom_field11', 'product_custom_field12', 'product_custom_field13', 'product_custom_field14', 'product_custom_field15', 'product_custom_field16', 'product_custom_field17', 'product_custom_field18', 'product_custom_field19', 'product_custom_field20'];

            $module_form_fields = $this->moduleUtil->getModuleData('product_form_fields');
            if (! empty($module_form_fields)) {
                foreach ($module_form_fields as $key => $value) {
                    if (! empty($value) && is_array($value)) {
                        $form_fields = array_merge($form_fields, $value);
                    }
                }
            }
            $product_details = $request->only($form_fields);

            $product_details['type'] = empty($product_details['type']) ? 'single' : $product_details['type'];
            $product_details['business_id'] = $business_id;
            $product_details['created_by'] = $request->session()->get('user.id');
            if (! empty($request->input('enable_stock')) && $request->input('enable_stock') == 1) {
                $product_details['enable_stock'] = 1;
                //TODO: Save total qty
                //$product_details['total_qty_available'] = 0;
            }
            if (! empty($request->input('not_for_selling')) && $request->input('not_for_selling') == 1) {
                $product_details['not_for_selling'] = 1;
            }
            if (empty($product_details['sku'])) {
                $product_details['sku'] = ' ';
            }

            if (! empty($product_details['alert_quantity'])) {
                $product_details['alert_quantity'] = $this->productUtil->num_uf($product_details['alert_quantity']);
            }

            $expiry_enabled = $request->session()->get('business.enable_product_expiry');
            if (! empty($request->input('expiry_period_type')) && ! empty($request->input('expiry_period')) && ! empty($expiry_enabled)) {
                $product_details['expiry_period_type'] = $request->input('expiry_period_type');
                $product_details['expiry_period'] = $this->productUtil->num_uf($request->input('expiry_period'));
            }

            if (! empty($request->input('enable_sr_no')) && $request->input('enable_sr_no') == 1) {
                $product_details['enable_sr_no'] = 1;
            }

            $product_details['warranty_id'] = ! empty($request->input('warranty_id')) ? $request->input('warranty_id') : null;

            DB::beginTransaction();

            $product = Product::create($product_details);
            event(new ProductsCreatedOrModified($product_details, 'added'));

            // Product image for single type (POS quick add)
            $image_name = $this->productUtil->uploadFile($request, 'image', config('constants.product_img_path'), 'image');
            if (! empty($image_name)) {
                $product->image = $image_name;
                $product->save();
            }

            if (empty(trim($request->input('sku')))) {
                $sku = $this->productUtil->generateProductSku($product->id);
                $product->sku = $sku;
                $product->save();
            }

            $this->productUtil->createSingleProductVariation(
                $product->id,
                $product->sku,
                $request->input('single_dpp'),
                $request->input('single_dpp_inc_tax'),
                $request->input('profit_percent'),
                $request->input('single_dsp'),
                $request->input('single_dsp_inc_tax')
            );

            if ($product->enable_stock == 1 && ! empty($request->input('opening_stock'))) {
                $user_id = $request->session()->get('user.id');

                $transaction_date = $request->session()->get('financial_year.start');
                $transaction_date = \Carbon::createFromFormat('Y-m-d', $transaction_date)->toDateTimeString();

                $this->productUtil->addSingleProductOpeningStock($business_id, $product, $request->input('opening_stock'), $transaction_date, $user_id);
            }

            //Add product locations
            $product_locations = $request->input('product_locations');
            if (! empty($product_locations)) {
                $product->product_locations()->sync($product_locations);
            }

            DB::commit();

            $output = ['success' => 1,
                'msg' => __('product.product_added_success'),
                'product' => $product,
                'variation' => $product->variations->first(),
                'locations' => $product_locations,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function view($id)
    {
        if (! auth()->user()->can('product.view')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');

            $product = Product::where('business_id', $business_id)
                        ->with(['brand', 'supplier', 'unit', 'category', 'sub_category', 'product_tax', 'variations', 'variations.product_variation', 'variations.group_prices', 'variations.media', 'product_locations', 'warranty', 'media'])
                        ->findOrFail($id);

            $purchased_from = PurchaseLine::join('transactions as t', 't.id', '=', 'purchase_lines.transaction_id')
                ->join('contacts as c', 'c.id', '=', 't.contact_id')
                ->where('t.business_id', $business_id)
                ->where('t.type', 'purchase')
                ->where('purchase_lines.product_id', $product->id)
                ->groupBy('c.id', 'c.name', 'c.supplier_business_name')
                ->select(
                    'c.id',
                    DB::raw("IF(c.name IS NULL OR c.name = '', c.supplier_business_name, c.name) as name"),
                    DB::raw('COUNT(DISTINCT t.id) as purchase_count'),
                    DB::raw('MAX(t.transaction_date) as last_purchase')
                )
                ->orderByDesc('last_purchase')
                ->limit(10)
                ->get();

            $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->pluck('name', 'id');

            $allowed_group_prices = [];
            foreach ($price_groups as $key => $value) {
                if (auth()->user()->can('selling_price_group.'.$key)) {
                    $allowed_group_prices[$key] = $value;
                }
            }

            $group_price_details = [];

            foreach ($product->variations as $variation) {
                foreach ($variation->group_prices as $group_price) {
                    $group_price_details[$variation->id][$group_price->price_group_id] = ['price' => $group_price->price_inc_tax, 'price_type' => $group_price->price_type, 'calculated_price' => $group_price->calculated_price];
                }
            }

            $rack_details = $this->productUtil->getRackDetails($business_id, $id, true);

            $combo_variations = [];
            if ($product->type == 'combo') {
                $combo_variations = $this->productUtil->__getComboProductDetails($product['variations'][0]->combo_variations, $business_id);
            }

            $packaging = $this->getPackagingHelperForProduct($business_id, $product);
            $pack_unit = ! empty($packaging['enabled']) && (float) $packaging['factor'] > 0
                ? ['name' => $packaging['name'], 'factor' => (float) $packaging['factor']]
                : null;

            return view('product.view-modal')->with(compact(
                'product',
                'rack_details',
                'allowed_group_prices',
                'group_price_details',
                'combo_variations',
                'purchased_from',
                'pack_unit'
            ));
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
        }
    }

    /**
     * Mass deletes products.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function massDestroy(Request $request)
    {
        if (! auth()->user()->can('product.delete')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $purchase_exist = false;

            if (! empty($request->input('selected_rows'))) {
                $business_id = $request->session()->get('user.business_id');

                $selected_rows = explode(',', $request->input('selected_rows'));

                $products = Product::where('business_id', $business_id)
                                    ->whereIn('id', $selected_rows)
                                    ->with(['purchase_lines', 'variations'])
                                    ->get();
                $deletable_products = [];

                $is_mfg_installed = $this->moduleUtil->isModuleInstalled('Manufacturing');

                DB::beginTransaction();

                $recycleBin = new \App\Utils\RecycleBinUtil();
                foreach ($products as $product) {
                    $can_be_deleted = true;
                    //Check if product is added as an ingredient of any recipe
                    if ($is_mfg_installed) {
                        $variation_ids = $product->variations->pluck('id');

                        $exists_as_ingredient = \Modules\Manufacturing\Entities\MfgRecipeIngredient::whereIn('variation_id', $variation_ids)
                            ->exists();
                        $can_be_deleted = ! $exists_as_ingredient;
                    }

                    if ($can_be_deleted) {
                        // Soft delete -> Recycle Bin
                        $recycleBin->softDeleteProduct($product, auth()->id());
                    } else {
                        $purchase_exist = true;
                    }
                }

                DB::commit();
            }

            if (! $purchase_exist) {
                $output = ['success' => 1,
                    'msg' => __('lang_v1.deleted_success'),
                ];
            } else {
                $output = ['success' => 0,
                    'msg' => __('lang_v1.products_could_not_be_deleted'),
                ];
            }
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
     * Shows form to add selling price group prices for a product.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addSellingPrices($id)
    {
        if (! auth()->user()->can('product.create') && ! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $product = Product::where('business_id', $business_id)
                    ->with(['variations', 'variations.group_prices', 'variations.product_variation'])
                            ->findOrFail($id);

        $price_groups = SellingPriceGroup::where('business_id', $business_id)
                                            ->active()
                                            ->get();
        $variation_prices = [];
        foreach ($product->variations as $variation) {
            foreach ($variation->group_prices as $group_price) {
                $variation_prices[$variation->id][$group_price->price_group_id] = ['price' => $group_price->price_inc_tax, 'price_type' => $group_price->price_type];
            }
        }

        return view('product.add-selling-prices')->with(compact('product', 'price_groups', 'variation_prices'));
    }

    /**
     * Saves selling price group prices for a product.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function saveSellingPrices(Request $request)
    {
        if (! auth()->user()->can('product.create') && ! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');
            $product = Product::where('business_id', $business_id)
                            ->with(['variations'])
                            ->findOrFail($request->input('product_id'));
            DB::beginTransaction();
            foreach ($product->variations as $variation) {
                $variation_group_prices = [];
                foreach ($request->input('group_prices') as $key => $value) {
                    if (isset($value[$variation->id])) {
                        $variation_group_price =
                        VariationGroupPrice::where('variation_id', $variation->id)
                                            ->where('price_group_id', $key)
                                            ->first();
                        if (empty($variation_group_price)) {
                            $variation_group_price = new VariationGroupPrice([
                                'variation_id' => $variation->id,
                                'price_group_id' => $key,
                            ]);
                        }

                        $price = $this->productUtil->num_uf($value[$variation->id]['price']);
                        $price_type = $value[$variation->id]['price_type'];
                        $allowed_price_types = ['fixed', 'percentage_adjustment'];

                        // Existing legacy percentage records can be saved unchanged.
                        if ($variation_group_price->price_type === 'percentage') {
                            $allowed_price_types[] = 'percentage';
                        }

                        if (! in_array($price_type, $allowed_price_types, true)) {
                            throw new \InvalidArgumentException(__('lang_v1.price_group_invalid_price_type_exception', [
                                'type' => $price_type,
                                'row' => 1,
                            ]));
                        }

                        if (($price_type === 'fixed' && $price < 0) ||
                            ($price_type === 'percentage_adjustment' && $price < -100)) {
                            throw new \InvalidArgumentException(__('lang_v1.invalid_group_price_adjustment'));
                        }

                        $variation_group_price->price_inc_tax = $price;
                        $variation_group_price->price_type = $price_type;
                        $variation_group_prices[] = $variation_group_price;
                    }
                }

                if (! empty($variation_group_prices)) {
                    $variation->group_prices()->saveMany($variation_group_prices);
                }
            }
            //Update product updated_at timestamp
            $product->touch();

            DB::commit();
            $output = ['success' => 1,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        if ($request->input('submit_type') == 'submit_n_add_opening_stock') {
            return redirect()->action([\App\Http\Controllers\OpeningStockController::class, 'add'],
                ['product_id' => $product->id]
            );
        } elseif ($request->input('submit_type') == 'save_n_add_another') {
            return redirect()->action([\App\Http\Controllers\ProductController::class, 'create']
            )->with('status', $output);
        }

        return redirect('products')->with('status', $output);
    }

    public function viewGroupPrice($id)
    {
        if (! auth()->user()->can('product.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $product = Product::where('business_id', $business_id)
                            ->where('id', $id)
                            ->with(['variations', 'variations.product_variation', 'variations.group_prices'])
                            ->first();

        $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->pluck('name', 'id');

        $allowed_group_prices = [];
        foreach ($price_groups as $key => $value) {
            if (auth()->user()->can('selling_price_group.'.$key)) {
                $allowed_group_prices[$key] = $value;
            }
        }

        $group_price_details = [];

        foreach ($product->variations as $variation) {
            foreach ($variation->group_prices as $group_price) {
                $group_price_details[$variation->id][$group_price->price_group_id] = $group_price->price_inc_tax;
            }
        }

        return view('product.view-product-group-prices')->with(compact('product', 'allowed_group_prices', 'group_price_details'));
    }

    /**
     * Mass deactivates products.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function massDeactivate(Request $request)
    {
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            if (! empty($request->input('selected_products'))) {
                $business_id = $request->session()->get('user.business_id');

                $selected_products = explode(',', $request->input('selected_products'));

                DB::beginTransaction();

                $products = Product::where('business_id', $business_id)
                                    ->whereIn('id', $selected_products)
                                    ->update(['is_inactive' => 1]);

                DB::commit();
            }

            $output = ['success' => 1,
                'msg' => __('lang_v1.products_deactivated_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Activates the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function activate($id)
    {
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');
                $product = Product::where('id', $id)
                                ->where('business_id', $business_id)
                                ->update(['is_inactive' => 0]);

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
     * Deletes a media file from storage and database.
     *
     * @param  int  $media_id
     * @return json
     */
    public function deleteMedia($media_id)
    {
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                Media::deleteMedia($business_id, $media_id);

                $output = ['success' => true,
                    'msg' => __('lang_v1.file_deleted_successfully'),
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

    public function getProductsApi($id = null)
    {
        try {
            $api_token = request()->header('API-TOKEN');
            $filter_string = request()->header('FILTERS');
            $order_by = request()->header('ORDER-BY');

            parse_str($filter_string, $filters);

            $api_settings = $this->moduleUtil->getApiSettings($api_token);

            $limit = ! empty(request()->input('limit')) ? request()->input('limit') : 10;

            $location_id = $api_settings->location_id;

            $query = Product::where('business_id', $api_settings->business_id)
                            ->active()
                            ->with(['brand', 'unit', 'category', 'sub_category',
                                'product_variations', 'product_variations.variations', 'product_variations.variations.media',
                                'product_variations.variations.variation_location_details' => function ($q) use ($location_id) {
                                    $q->where('location_id', $location_id);
                                }, ]);

            if (! empty($filters['categories'])) {
                $query->whereIn('category_id', $filters['categories']);
            }

            if (! empty($filters['brands'])) {
                $query->whereIn('brand_id', $filters['brands']);
            }

            if (! empty($filters['category'])) {
                $query->where('category_id', $filters['category']);
            }

            if (! empty($filters['sub_category'])) {
                $query->where('sub_category_id', $filters['sub_category']);
            }

            if ($order_by == 'name') {
                $query->orderBy('name', 'asc');
            } elseif ($order_by == 'date') {
                $query->orderBy('created_at', 'desc');
            }

            if (empty($id)) {
                $products = $query->paginate($limit);
            } else {
                $products = $query->find($id);
            }
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            return $this->respondWentWrong($e);
        }

        return $this->respond($products);
    }

    public function getVariationsApi()
    {
        try {
            $api_token = request()->header('API-TOKEN');
            $variations_string = request()->header('VARIATIONS');

            if (is_numeric($variations_string)) {
                $variation_ids = intval($variations_string);
            } else {
                parse_str($variations_string, $variation_ids);
            }

            $api_settings = $this->moduleUtil->getApiSettings($api_token);
            $location_id = $api_settings->location_id;
            $business_id = $api_settings->business_id;

            $query = Variation::with([
                'product_variation',
                'product' => function ($q) use ($business_id) {
                    $q->where('business_id', $business_id);
                },
                'product.unit',
                'variation_location_details' => function ($q) use ($location_id) {
                    $q->where('location_id', $location_id);
                },
            ]);

            $variations = is_array($variation_ids) ? $query->whereIn('id', $variation_ids)->get() : $query->where('id', $variation_ids)->first();
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            return $this->respondWentWrong($e);
        }

        return $this->respond($variations);
    }

    /**
     * Shows form to edit multiple products at once.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkEdit(Request $request)
    {
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        $selected_products_string = $request->input('selected_products');
        if (! empty($selected_products_string)) {
            $selected_products = explode(',', $selected_products_string);
            $business_id = $request->session()->get('user.business_id');

            $products = Product::where('business_id', $business_id)
                                ->whereIn('id', $selected_products)
                                ->with(['variations', 'variations.product_variation', 'variations.group_prices', 'product_locations'])
                                ->get();

            $all_categories = Category::catAndSubCategories($business_id);

            $categories = [];
            $sub_categories = [];
            foreach ($all_categories as $category) {
                $categories[$category['id']] = $category['name'];

                if (! empty($category['sub_categories'])) {
                    foreach ($category['sub_categories'] as $sub_category) {
                        $sub_categories[$category['id']][$sub_category['id']] = $sub_category['name'];
                    }
                }
            }

            $brands = Brands::forDropdown($business_id);

            $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
            $taxes = $tax_dropdown['tax_rates'];
            $tax_attributes = $tax_dropdown['attributes'];

            $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->pluck('name', 'id');
            $business_locations = BusinessLocation::forDropdown($business_id);

            return view('product.bulk-edit')->with(compact(
                'products',
                'categories',
                'brands',
                'taxes',
                'tax_attributes',
                'sub_categories',
                'price_groups',
                'business_locations'
            ));
        }
    }

    /**
     * Updates multiple products at once.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkUpdate(Request $request)
    {
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $products = $request->input('products');
            $business_id = $request->session()->get('user.business_id');

            DB::beginTransaction();
            foreach ($products as $id => $product_data) {
                $update_data = [
                    'category_id' => $product_data['category_id'],
                    'sub_category_id' => $product_data['sub_category_id'],
                    'brand_id' => $product_data['brand_id'],
                    'tax' => $product_data['tax'],
                ];

                //Update product
                $product = Product::where('business_id', $business_id)
                                ->findOrFail($id);

                $product->update($update_data);

                //Add product locations
                $product_locations = ! empty($product_data['product_locations']) ?
                                    $product_data['product_locations'] : [];
                $product->product_locations()->sync($product_locations);

                $variations_data = [];

                //Format variations data
                foreach ($product_data['variations'] as $key => $value) {
                    $variation = Variation::where('product_id', $product->id)->findOrFail($key);
                    $variation->default_purchase_price = $this->productUtil->num_uf($value['default_purchase_price']);
                    $variation->dpp_inc_tax = $this->productUtil->num_uf($value['dpp_inc_tax']);
                    $variation->profit_percent = $this->productUtil->num_uf($value['profit_percent']);
                    $variation->default_sell_price = $this->productUtil->num_uf($value['default_sell_price']);
                    $variation->sell_price_inc_tax = $this->productUtil->num_uf($value['sell_price_inc_tax']);
                    $variations_data[] = $variation;

                    //Update price groups
                    if (! empty($value['group_prices'])) {
                        foreach ($value['group_prices'] as $k => $v) {
                            VariationGroupPrice::updateOrCreate(
                                ['price_group_id' => $k, 'variation_id' => $variation->id],
                                ['price_inc_tax' => $this->productUtil->num_uf($v)]
                            );
                        }
                    }
                }
                $product->variations()->saveMany($variations_data);
            }
            DB::commit();

            $output = ['success' => 1,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect('products')->with('status', $output);
    }

    /**
     * Adds product row to edit in bulk edit product form
     *
     * @param  int  $product_id
     * @return \Illuminate\Http\Response
     */
    public function getProductToEdit($product_id)
    {
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');

        $product = Product::where('business_id', $business_id)
                            ->with(['variations', 'variations.product_variation', 'variations.group_prices'])
                            ->findOrFail($product_id);
        $all_categories = Category::catAndSubCategories($business_id);

        $categories = [];
        $sub_categories = [];
        foreach ($all_categories as $category) {
            $categories[$category['id']] = $category['name'];

            if (! empty($category['sub_categories'])) {
                foreach ($category['sub_categories'] as $sub_category) {
                    $sub_categories[$category['id']][$sub_category['id']] = $sub_category['name'];
                }
            }
        }

        $brands = Brands::forDropdown($business_id);

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
        $taxes = $tax_dropdown['tax_rates'];
        $tax_attributes = $tax_dropdown['attributes'];

        $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->pluck('name', 'id');
        $business_locations = BusinessLocation::forDropdown($business_id);

        return view('product.partials.bulk_edit_product_row')->with(compact(
            'product',
            'categories',
            'brands',
            'taxes',
            'tax_attributes',
            'sub_categories',
            'price_groups',
            'business_locations'
        ));
    }

    /**
     * Gets the sub units for the given unit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $unit_id
     * @return \Illuminate\Http\Response
     */
    public function getSubUnits(Request $request)
    {
        if (! empty($request->input('unit_id'))) {
            $unit_id = $request->input('unit_id');
            $business_id = $request->session()->get('user.business_id');
            $sub_units = $this->productUtil->getSubUnits($business_id, $unit_id, true);

            //$html = '<option value="">' . __('lang_v1.all') . '</option>';
            $html = '';
            if (! empty($sub_units)) {
                foreach ($sub_units as $id => $sub_unit) {
                    $html .= '<option value="'.$id.'">'.$sub_unit['name'].'</option>';
                }
            }

            return $html;
        }
    }

    /**
     * Keeps purchase exc/inc tax and margin consistent when one side of the pair is missing.
     */
    protected function normalizeSinglePrices(array $data, $tax_id): array
    {
        $tax_rate = ! empty($tax_id) ? (float) (TaxRate::where('id', $tax_id)->value('amount') ?? 0) : 0;

        $dpp = (float) $this->productUtil->num_uf($data['single_dpp'] ?? 0);
        $dpp_inc = (float) $this->productUtil->num_uf($data['single_dpp_inc_tax'] ?? 0);
        $dsp = (float) $this->productUtil->num_uf($data['single_dsp'] ?? 0);
        $dsp_inc = (float) $this->productUtil->num_uf($data['single_dsp_inc_tax'] ?? 0);

        if ($dpp <= 0 && $dpp_inc > 0) {
            $dpp = $dpp_inc / (1 + $tax_rate / 100);
        } elseif ($dpp_inc <= 0 && $dpp > 0) {
            $dpp_inc = $dpp * (1 + $tax_rate / 100);
        }
        if ($dsp <= 0 && $dsp_inc > 0) {
            $dsp = $dsp_inc / (1 + $tax_rate / 100);
        } elseif ($dsp_inc <= 0 && $dsp > 0) {
            $dsp_inc = $dsp * (1 + $tax_rate / 100);
        }

        return [
            'single_dpp' => $dpp,
            'single_dpp_inc_tax' => $dpp_inc,
            'single_dsp' => $dsp,
            'single_dsp_inc_tax' => $dsp_inc,
            'profit_percent' => $dpp > 0 ? $this->productUtil->get_percent($dpp, $dsp) : (float) $this->productUtil->num_uf($data['profit_percent'] ?? 0),
        ];
    }

    /**
     * Categories with a measurement unit (oz, mg, gram…) fix the product's base (stock) unit.
     */
    protected function applyCategoryMeasureUnit(Request $request, array $product_details, $business_id): array
    {
        $unit_id = \App\Category::measureUnitIdFor((int) $business_id, [
            $request->input('category_id'),
            $request->input('sub_category_id'),
            $request->input('child_category_id'),
        ]);
        if ($unit_id) {
            $product_details['unit_id'] = $unit_id;
        }

        return $product_details;
    }

    /**
     * Find/create packaging unit from product form helper and merge into sub_unit_ids.
     */
    protected function applyPackagingUnitFromRequest(Request $request, array $product_details, $business_id): array
    {
        if (empty($request->input('enable_packaging_unit'))) {
            // If user turns packaging off, drop the previous packaging unit from related list
            $prev_pack_id = $request->input('packaging_prev_unit_id') ?: $request->input('packaging_unit_id');
            if (! empty($prev_pack_id) && ! empty($product_details['sub_unit_ids']) && is_array($product_details['sub_unit_ids'])) {
                $product_details['sub_unit_ids'] = array_values(array_filter(
                    $product_details['sub_unit_ids'],
                    function ($id) use ($prev_pack_id) {
                        return (string) $id !== (string) $prev_pack_id;
                    }
                ));
                if (empty($product_details['sub_unit_ids'])) {
                    $product_details['sub_unit_ids'] = null;
                }
            }

            return $product_details;
        }

        $base_unit_id = $product_details['unit_id'] ?? $request->input('unit_id');
        $factor = $this->productUtil->num_uf($request->input('packaging_conversion_factor'));

        $pack_name = $request->input('packaging_unit_name');
        if ($pack_name === '__custom__') {
            $pack_name = trim((string) $request->input('packaging_unit_custom_name'));
        }
        $pack_name = trim((string) $pack_name);

        if (empty($base_unit_id) || empty($pack_name) || empty($factor) || $factor <= 0) {
            return $product_details;
        }

        $packaging = null;
        $existing_id = $request->input('packaging_unit_id');
        if (! empty($existing_id)) {
            $packaging = Unit::where('business_id', $business_id)
                ->where('id', $existing_id)
                ->where('base_unit_id', $base_unit_id)
                ->first();
        }

        if (empty($packaging)) {
            $packaging = Unit::where('business_id', $business_id)
                ->where('base_unit_id', $base_unit_id)
                ->where('actual_name', $pack_name)
                ->where('base_unit_multiplier', $factor)
                ->first();
        }

        if (empty($packaging)) {
            $short = strlen($pack_name) <= 10 ? $pack_name : substr($pack_name, 0, 10);
            $packaging = Unit::create([
                'business_id' => $business_id,
                'actual_name' => $pack_name,
                'short_name' => $short,
                'allow_decimal' => 0,
                'base_unit_id' => $base_unit_id,
                'base_unit_multiplier' => $factor,
                'created_by' => $request->session()->get('user.id'),
            ]);
        } elseif ((float) $packaging->base_unit_multiplier != (float) $factor) {
            // Existing packaging selected but factor changed — create a distinct unit row
            $short = strlen($pack_name) <= 10 ? $pack_name : substr($pack_name, 0, 10);
            $packaging = Unit::create([
                'business_id' => $business_id,
                'actual_name' => $pack_name,
                'short_name' => $short,
                'allow_decimal' => 0,
                'base_unit_id' => $base_unit_id,
                'base_unit_multiplier' => $factor,
                'created_by' => $request->session()->get('user.id'),
            ]);
        }

        $sub_ids = $product_details['sub_unit_ids'] ?? [];
        if (! is_array($sub_ids)) {
            $sub_ids = [];
        }
        // Replace the previous packaging unit (factor or name changed) instead of keeping both
        foreach (array_unique([(string) $existing_id, (string) $request->input('packaging_prev_unit_id')]) as $old_pack_id) {
            if ($old_pack_id !== '' && $old_pack_id !== (string) $packaging->id) {
                $sub_ids = array_filter($sub_ids, function ($id) use ($old_pack_id) {
                    return (string) $id !== $old_pack_id;
                });
            }
        }
        $sub_ids[] = $packaging->id;
        // Keep base unit out of related filter list (POS always includes base)
        $sub_ids = array_values(array_unique(array_filter($sub_ids, function ($id) use ($base_unit_id) {
            return ! empty($id) && (string) $id !== (string) $base_unit_id;
        })));
        $product_details['sub_unit_ids'] = ! empty($sub_ids) ? $sub_ids : null;

        $this->ensureEnableSubUnits($business_id);

        return $product_details;
    }

    /**
     * Prefill packaging helper on product edit.
     */
    protected function getPackagingHelperForProduct($business_id, $product): array
    {
        $helper = [
            'enabled' => false,
            'name' => 'Box',
            'factor' => '',
            'unit_id' => '',
        ];

        if (empty($product) || empty($product->sub_unit_ids) || ! is_array($product->sub_unit_ids)) {
            return $helper;
        }

        $packaging = Unit::where('business_id', $business_id)
            ->whereIn('id', $product->sub_unit_ids)
            ->whereNotNull('base_unit_id')
            ->where('base_unit_id', $product->unit_id)
            ->orderBy('id')
            ->first();

        if (empty($packaging)) {
            return $helper;
        }

        return [
            'enabled' => true,
            'name' => $packaging->actual_name,
            'factor' => $packaging->base_unit_multiplier,
            'unit_id' => $packaging->id,
        ];
    }

    /**
     * Save additional barcodes / SKUs from the product form.
     */
    protected function syncProductBarcodesFromRequest(Request $request, Product $product, $business_id): void
    {
        $rows = $request->input('product_barcodes', []);
        if (! is_array($rows)) {
            $rows = [];
        }

        $default_variation_id = Variation::where('product_id', $product->id)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->value('id');

        $pack_unit_id = $request->input('packaging_unit_id');
        if (empty($pack_unit_id) && ! empty($product->sub_unit_ids) && is_array($product->sub_unit_ids)) {
            $pack = Unit::where('business_id', $business_id)
                ->whereIn('id', $product->sub_unit_ids)
                ->whereNotNull('base_unit_id')
                ->where('base_unit_id', $product->unit_id)
                ->orderBy('id')
                ->first();
            $pack_unit_id = $pack->id ?? null;
        }

        $keep_ids = [];
        $seen = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $code = trim((string) ($row['barcode'] ?? ''));
            if ($code === '') {
                continue;
            }

            $code_key = mb_strtolower($code);
            if (isset($seen[$code_key])) {
                continue;
            }
            $seen[$code_key] = true;

            // Don't duplicate the primary SKU as an alternate row
            if (strcasecmp($code, trim((string) $product->sku)) === 0) {
                continue;
            }

            $type = (($row['type'] ?? 'alternate') === 'pack') ? 'pack' : 'alternate';
            $label = trim((string) ($row['label'] ?? ''));
            $unit_id = null;

            if ($type === 'pack') {
                $unit_id = ! empty($row['unit_id']) ? $row['unit_id'] : $pack_unit_id;
                if (empty($unit_id)) {
                    $type = 'alternate';
                    $unit_id = null;
                }
            }

            $variation_id = ! empty($row['variation_id']) ? $row['variation_id'] : $default_variation_id;
            if (empty($variation_id)) {
                continue;
            }

            if ($this->barcodeIsTaken($business_id, $code, $product->id)) {
                throw new \InvalidArgumentException(__('lang_v1.barcode_already_used', ['code' => $code]));
            }

            $payload = [
                'business_id' => $business_id,
                'product_id' => $product->id,
                'variation_id' => $variation_id,
                'barcode' => $code,
                'type' => $type,
                'unit_id' => $unit_id,
                'label' => $label !== '' ? $label : null,
            ];

            $existing_id = $row['id'] ?? null;
            if (! empty($existing_id)) {
                $existing = ProductBarcode::where('business_id', $business_id)
                    ->where('product_id', $product->id)
                    ->where('id', $existing_id)
                    ->first();
                if (! empty($existing)) {
                    $existing->fill($payload);
                    $existing->save();
                    $keep_ids[] = $existing->id;
                    continue;
                }
            }

            $created = ProductBarcode::create($payload);
            $keep_ids[] = $created->id;
        }

        $delete_query = ProductBarcode::where('product_id', $product->id);
        if (! empty($keep_ids)) {
            $delete_query->whereNotIn('id', $keep_ids);
        }
        $delete_query->delete();
    }

    /**
     * @param  array<int, string>  $terms
     * @return array<int, int>
     */
    private function variationIdsForBarcodeTerms($business_id, array $terms): array
    {
        $terms = array_values(array_filter(array_map('strval', $terms), function ($t) {
            return $t !== '';
        }));
        if (empty($terms) || ! $this->productBarcodesReady()) {
            return [];
        }

        return ProductBarcode::where('business_id', $business_id)
            ->whereIn('barcode', $terms)
            ->whereNotNull('variation_id')
            ->pluck('variation_id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $terms
     */
    private function findProductBarcodeMatch($business_id, array $terms): ?ProductBarcode
    {
        $terms = array_values(array_filter(array_map('strval', $terms), function ($t) {
            return $t !== '';
        }));
        if (empty($terms) || ! $this->productBarcodesReady()) {
            return null;
        }

        foreach ($terms as $term) {
            $match = ProductBarcode::where('business_id', $business_id)
                ->where(function ($q) use ($term) {
                    $q->where('barcode', $term)
                        ->orWhereRaw('LOWER(barcode) = ?', [mb_strtolower($term)]);
                })
                ->first();
            if (! empty($match)) {
                return $match;
            }
        }

        return null;
    }

    /**
     * Turn on business enable_sub_units when packaging helper is used.
     */
    protected function ensureEnableSubUnits($business_id): void
    {
        $business = Business::find($business_id);
        if (empty($business)) {
            return;
        }

        if (empty($business->enable_sub_units)) {
            $business->enable_sub_units = 1;
            $business->save();
        }

        $session_business = request()->session()->get('business');
        if (! empty($session_business)) {
            if (is_array($session_business)) {
                $session_business['enable_sub_units'] = 1;
            } else {
                $session_business->enable_sub_units = 1;
            }
            request()->session()->put('business', $session_business);
        }
    }

    public function updateProductLocation(Request $request)
    {
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $selected_products = $request->input('products');
            $update_type = $request->input('update_type');
            $location_ids = $request->input('product_location');

            $business_id = $request->session()->get('user.business_id');

            $product_ids = explode(',', $selected_products);

            $products = Product::where('business_id', $business_id)
                                ->whereIn('id', $product_ids)
                                ->with(['product_locations'])
                                ->get();
            DB::beginTransaction();
            foreach ($products as $product) {
                $product_locations = $product->product_locations->pluck('id')->toArray();

                if ($update_type == 'add') {
                    $product_locations = array_unique(array_merge($location_ids, $product_locations));
                    $product->product_locations()->sync($product_locations);
                } elseif ($update_type == 'remove') {
                    foreach ($product_locations as $key => $value) {
                        if (in_array($value, $location_ids)) {
                            unset($product_locations[$key]);
                        }
                    }
                    $product->product_locations()->sync($product_locations);
                }
            }
            DB::commit();
            $output = ['success' => 1,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function productStockHistory($id)
    {
        if (! auth()->user()->can('product.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {

            //for ajax call $id is variation id else it is product id
            $stock_details = $this->productUtil->getVariationStockDetails($business_id, $id, request()->input('location_id'));
            $stock_history = $this->productUtil->getVariationStockHistory($business_id, $id, request()->input('location_id'));

            //if mismach found update stock in variation location details
            if (isset($stock_history[0]) && (float) $stock_details['current_stock'] != (float) $stock_history[0]['stock']) {
                VariationLocationDetails::where('variation_id',
                                            $id)
                                    ->where('location_id', request()->input('location_id'))
                                    ->update(['qty_available' => $stock_history[0]['stock']]);
                $stock_details['current_stock'] = $stock_history[0]['stock'];
            }

            return view('product.stock_history_details')
                ->with(compact('stock_details', 'stock_history'));
        }

        $product = Product::where('business_id', $business_id)
                            ->with(['variations', 'variations.product_variation'])
                            ->findOrFail($id);

        //Get all business locations
        $business_locations = BusinessLocation::forDropdown($business_id);

        return view('product.stock_history')
                ->with(compact('product', 'business_locations'));
    }

    /**
     * Shows the inventory movements of a product as a visual timeline.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function inventoryTimeline($id)
    {
        if (! auth()->user()->can('product.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            //for ajax call $id is variation id else it is product id
            $stock_details = $this->productUtil->getVariationStockDetails($business_id, $id, request()->input('location_id'));
            $stock_history = $this->productUtil->getVariationStockHistory($business_id, $id, request()->input('location_id'));

            return view('product.inventory_timeline_details')
                ->with(compact('stock_details', 'stock_history'));
        }

        $product = Product::where('business_id', $business_id)
                            ->with(['variations', 'variations.product_variation'])
                            ->findOrFail($id);

        //Get all business locations
        $business_locations = BusinessLocation::forDropdown($business_id);

        return view('product.inventory_timeline')
                ->with(compact('product', 'business_locations'));
    }

    /**
     * Toggle WooComerce sync
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function toggleWooCommerceSync(Request $request)
    {
        try {
            $selected_products = $request->input('woocommerce_products_sync');
            $woocommerce_disable_sync = $request->input('woocommerce_disable_sync');

            $business_id = $request->session()->get('user.business_id');
            $product_ids = explode(',', $selected_products);

            DB::beginTransaction();
            if ($this->moduleUtil->isModuleInstalled('Woocommerce')) {
                Product::where('business_id', $business_id)
                        ->whereIn('id', $product_ids)
                        ->update(['woocommerce_disable_sync' => $woocommerce_disable_sync]);
            }
            DB::commit();
            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Function to download all products in xlsx format
     */
    public function downloadExcel()
    {
        $is_admin = $this->productUtil->is_admin(auth()->user());
        if (! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        $filename = 'products-export-'.\Carbon::now()->format('Y-m-d').'.xlsx';

        return Excel::download(new ProductsExport, $filename);
    }

    /**
     * Barcode scanners often send UPC/EAN variants (leading zeros, check digit, AIM prefix).
     * Search-by-name still works; exact SKU scan would otherwise miss after stock/label updates.
     *
     * @return array<int, string>
     */
    private function barcodeScanCandidates(string $term): array
    {
        $term = preg_replace('/[\x00-\x1F\x7F]/', '', $term);
        $term = trim((string) $term);
        if ($term === '') {
            return [];
        }
        if (preg_match('/^\][A-Za-z0-9]{2}/', $term)) {
            $term = substr($term, 3);
        }

        $extracted = $this->extractCodeFromQrPayload($term);
        $term = $extracted !== '' ? $extracted : $term;

        $candidates = [$term];
        if (preg_match('/^\d+$/', $term)) {
            $stripped = ltrim($term, '0');
            if ($stripped !== '' && $stripped !== $term) {
                $candidates[] = $stripped;
            }
            $len = strlen($term);
            if ($len === 12) {
                $candidates[] = '0'.$term;
            }
            if ($len === 13 && $term[0] === '0') {
                $candidates[] = substr($term, 1);
            }
            if ($len >= 12) {
                $candidates[] = substr($term, 0, -1);
            }
            if ($stripped !== '') {
                $candidates[] = str_pad($stripped, 12, '0', STR_PAD_LEFT);
                $candidates[] = str_pad($stripped, 13, '0', STR_PAD_LEFT);
            }
        }

        return array_values(array_unique(array_filter($candidates, function ($v) {
            return $v !== '';
        })));
    }

    private function productBarcodesReady(): bool
    {
        static $ready = null;
        if ($ready === null) {
            $ready = Schema::hasTable('product_barcodes');
        }

        return $ready;
    }

    private function barcodeIsTaken($business_id, string $code, $except_product_id = null, $except_variation_id = null): bool
    {
        $code = trim($code);
        if ($code === '') {
            return false;
        }
        $lower = mb_strtolower($code);

        $skuQuery = Product::where('business_id', $business_id)
            ->where(function ($q) use ($code, $lower) {
                $q->where('sku', $code)->orWhereRaw('LOWER(sku) = ?', [$lower]);
            });
        if (! empty($except_product_id)) {
            $skuQuery->where('id', '!=', $except_product_id);
        }
        if ($skuQuery->exists()) {
            return true;
        }

        $varQuery = Variation::join('products', 'variations.product_id', '=', 'products.id')
            ->where('products.business_id', $business_id)
            ->where(function ($q) use ($code, $lower) {
                $q->where('variations.sub_sku', $code)
                    ->orWhereRaw('LOWER(variations.sub_sku) = ?', [$lower]);
            });
        if (! empty($except_product_id)) {
            $varQuery->where('products.id', '!=', $except_product_id);
        }
        if (! empty($except_variation_id)) {
            $varQuery->where('variations.id', '!=', $except_variation_id);
        }
        if ($varQuery->exists()) {
            return true;
        }

        if (! $this->productBarcodesReady()) {
            return false;
        }

        $barQuery = ProductBarcode::where('business_id', $business_id)
            ->where(function ($q) use ($code, $lower) {
                $q->where('barcode', $code)->orWhereRaw('LOWER(barcode) = ?', [$lower]);
            });
        if (! empty($except_product_id)) {
            $barQuery->where('product_id', '!=', $except_product_id);
        }

        return $barQuery->exists();
    }

    private function productFormErrorMessage(\Throwable $e): string
    {
        $message = $e->getMessage();
        if ($e instanceof \InvalidArgumentException) {
            return $message;
        }
        if (str_contains($message, 'barcode') || str_contains($message, 'Duplicate') || str_contains($message, 'product_barcodes')) {
            return __('lang_v1.barcode_already_used', ['code' => '']);
        }

        return __('messages.something_went_wrong');
    }

    private function extractCodeFromQrPayload(string $term): string
    {
        $term = trim($term);
        if ($term === '') {
            return '';
        }

        $json = json_decode($term, true);
        if (is_array($json)) {
            foreach (['sku', 'barcode', 'code', 'sub_sku'] as $key) {
                if (! empty($json[$key]) && is_scalar($json[$key])) {
                    return trim((string) $json[$key]);
                }
            }
        }

        if (preg_match('#https?://[^\s]+#i', $term, $m)) {
            $url = $m[0];
            $parts = parse_url($url);
            if (! empty($parts['query'])) {
                parse_str($parts['query'], $qs);
                foreach (['sku', 'barcode', 'code', 'sub_sku'] as $key) {
                    if (! empty($qs[$key])) {
                        return trim((string) $qs[$key]);
                    }
                }
            }
            if (! empty($parts['path'])) {
                $seg = trim((string) basename($parts['path']), '/');
                if ($seg !== '' && ! preg_match('/^(shop|product|p|c|category)$/i', $seg)) {
                    return rawurldecode($seg);
                }
            }
        }

        if (preg_match('/(?:^|\D)01(\d{8,14})(?:\D|$)/', $term, $m)) {
            return $m[1];
        }

        if (str_contains($term, '|') || str_contains($term, ',')) {
            $first = trim((string) preg_split('/[|,]/', $term, 2)[0]);
            if ($first !== '') {
                return $first;
            }
        }

        return $term;
    }
}
