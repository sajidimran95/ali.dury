<?php

namespace App\Http\Controllers;

use App\Brands;
use App\BusinessLocation;
use App\Category;
use App\Product;
use App\Unit;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\VariationLocationDetails;
use DB;
use Illuminate\Http\Request;

class InventoryDashboardController extends Controller
{
    protected BusinessUtil $businessUtil;

    protected ProductUtil $productUtil;

    protected ModuleUtil $moduleUtil;

    public function __construct(BusinessUtil $businessUtil, ProductUtil $productUtil, ModuleUtil $moduleUtil)
    {
        $this->businessUtil = $businessUtil;
        $this->productUtil = $productUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Inventory dashboard with KPI cards and stock overview.
     */
    public function index(Request $request)
    {
        if (! auth()->user()->can('product.view') && ! auth()->user()->can('dashboard.data') && ! auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $permitted_locations = auth()->user()->permitted_locations();

        $location_id = $request->get('location_id');
        if ($permitted_locations != 'all' && ! empty($location_id) && ! in_array($location_id, $permitted_locations)) {
            abort(403, 'Unauthorized action.');
        }

        $stats = $this->getInventoryStats($business_id, $permitted_locations, $location_id);
        $low_stock_products = $this->getLowStockProducts($business_id, $permitted_locations, $location_id, 8);
        $top_stock_products = $this->getTopStockProducts($business_id, $permitted_locations, $location_id, 8);
        $category_breakdown = $this->getCategoryBreakdown($business_id, $permitted_locations, $location_id);

        $business_locations = BusinessLocation::forDropdown($business_id, false);

        return view('inventory.dashboard', compact(
            'stats',
            'low_stock_products',
            'top_stock_products',
            'category_breakdown',
            'business_locations',
            'location_id'
        ));
    }

    /**
     * Inventory KPI stats (shared by inventory dashboard + product list cards).
     */
    public function getStats(int $businessId, $permittedLocations, $locationId = null): array
    {
        return $this->getInventoryStats($businessId, $permittedLocations, $locationId);
    }

    protected function getInventoryStats(int $businessId, $permittedLocations, $locationId = null): array
    {
        $productQuery = Product::where('business_id', $businessId)->where('is_inactive', 0);

        $totalProducts = (clone $productQuery)->count();
        $stockTracked = (clone $productQuery)->where('enable_stock', 1)->count();

        $categories = Category::where('business_id', $businessId)
            ->where('category_type', 'product')
            ->where('parent_id', 0)
            ->count();

        $brands = Brands::where('business_id', $businessId)->count();
        $units = Unit::where('business_id', $businessId)->count();

        $vld = VariationLocationDetails::query()
            ->join('variations as v', 'variation_location_details.variation_id', '=', 'v.id')
            ->join('products as p', 'variation_location_details.product_id', '=', 'p.id')
            ->join('business_locations as bl', 'variation_location_details.location_id', '=', 'bl.id')
            ->where('p.business_id', $businessId)
            ->where('p.is_inactive', 0)
            ->where('p.enable_stock', 1)
            ->whereNull('v.deleted_at');

        if ($permittedLocations != 'all') {
            $vld->whereIn('variation_location_details.location_id', $permittedLocations);
        }
        if (! empty($locationId)) {
            $vld->where('variation_location_details.location_id', $locationId);
        }

        $inventoryValue = (clone $vld)->sum(DB::raw('variation_location_details.qty_available * COALESCE(v.dpp_inc_tax, v.default_purchase_price, 0)'));
        $sellValue = (clone $vld)->sum(DB::raw('variation_location_details.qty_available * COALESCE(v.sell_price_inc_tax, v.default_sell_price, 0)'));
        $totalQty = (clone $vld)->sum('variation_location_details.qty_available');

        $lowStockQuery = VariationLocationDetails::query()
            ->join('variations as v', 'variation_location_details.variation_id', '=', 'v.id')
            ->join('products as p', 'variation_location_details.product_id', '=', 'p.id')
            ->where('p.business_id', $businessId)
            ->where('p.enable_stock', 1)
            ->where('p.is_inactive', 0)
            ->whereNull('v.deleted_at')
            ->whereNotNull('p.alert_quantity')
            ->whereRaw('variation_location_details.qty_available <= p.alert_quantity');

        if ($permittedLocations != 'all') {
            $lowStockQuery->whereIn('variation_location_details.location_id', $permittedLocations);
        }
        if (! empty($locationId)) {
            $lowStockQuery->where('variation_location_details.location_id', $locationId);
        }
        $lowStockCount = $lowStockQuery->count();

        $outOfStock = (clone $vld)->where('variation_location_details.qty_available', '<=', 0)->count();

        return [
            'total_products' => $totalProducts,
            'stock_tracked' => $stockTracked,
            'inventory_value' => (float) $inventoryValue,
            'sell_value' => (float) $sellValue,
            'total_qty' => (float) $totalQty,
            'low_stock' => $lowStockCount,
            'out_of_stock' => $outOfStock,
            'categories' => $categories,
            'brands' => $brands,
            'units' => $units,
        ];
    }

    protected function getLowStockProducts(int $businessId, $permittedLocations, $locationId = null, int $limit = 8)
    {
        $query = VariationLocationDetails::query()
            ->join('variations as v', 'variation_location_details.variation_id', '=', 'v.id')
            ->join('products as p', 'variation_location_details.product_id', '=', 'p.id')
            ->leftJoin('business_locations as l', 'variation_location_details.location_id', '=', 'l.id')
            ->leftJoin('units as u', 'p.unit_id', '=', 'u.id')
            ->where('p.business_id', $businessId)
            ->where('p.enable_stock', 1)
            ->where('p.is_inactive', 0)
            ->whereNull('v.deleted_at')
            ->whereNotNull('p.alert_quantity')
            ->whereRaw('variation_location_details.qty_available <= p.alert_quantity')
            ->select(
                'p.name as product',
                'v.sub_sku as sku',
                'l.name as location_name',
                'variation_location_details.qty_available as stock',
                'p.alert_quantity',
                'u.short_name as unit',
                'variation_location_details.location_id'
            )
            ->orderBy('stock')
            ->limit($limit);

        if ($permittedLocations != 'all') {
            $query->whereIn('variation_location_details.location_id', $permittedLocations);
        }
        if (! empty($locationId)) {
            $query->where('variation_location_details.location_id', $locationId);
        }

        return $query->get();
    }

    protected function getTopStockProducts(int $businessId, $permittedLocations, $locationId = null, int $limit = 8)
    {
        $query = VariationLocationDetails::query()
            ->join('variations as v', 'variation_location_details.variation_id', '=', 'v.id')
            ->join('products as p', 'variation_location_details.product_id', '=', 'p.id')
            ->leftJoin('units as u', 'p.unit_id', '=', 'u.id')
            ->where('p.business_id', $businessId)
            ->where('p.is_inactive', 0)
            ->where('p.enable_stock', 1)
            ->where('variation_location_details.qty_available', '>', 0)
            ->whereNull('v.deleted_at')
            ->select(
                'p.id as product_id',
                'p.name as product',
                'v.sub_sku as sku',
                'u.short_name as unit',
                DB::raw('SUM(variation_location_details.qty_available) as stock'),
                DB::raw('SUM(variation_location_details.qty_available * COALESCE(v.dpp_inc_tax, v.default_purchase_price, 0)) as stock_value')
            )
            ->groupBy('p.id', 'p.name', 'v.sub_sku', 'u.short_name')
            ->orderByDesc('stock')
            ->limit($limit);

        if ($permittedLocations != 'all') {
            $query->whereIn('variation_location_details.location_id', $permittedLocations);
        }
        if (! empty($locationId)) {
            $query->where('variation_location_details.location_id', $locationId);
        }

        return $query->get();
    }

    protected function getCategoryBreakdown(int $businessId, $permittedLocations, $locationId = null, int $limit = 8)
    {
        $query = Product::query()
            ->leftJoin('categories as c', 'products.category_id', '=', 'c.id')
            ->leftJoin('variation_location_details as vld', 'products.id', '=', 'vld.product_id')
            ->leftJoin('variations as v', 'vld.variation_id', '=', 'v.id')
            ->where('products.business_id', $businessId)
            ->where('products.is_inactive', 0)
            ->select(
                'c.id as category_id',
                DB::raw('COALESCE(c.name, "'.__('lang_v1.uncategorized').'") as category_name'),
                DB::raw('COUNT(DISTINCT products.id) as product_count'),
                DB::raw('COALESCE(SUM(vld.qty_available), 0) as stock'),
                DB::raw('COALESCE(SUM(vld.qty_available * COALESCE(v.dpp_inc_tax, v.default_purchase_price, 0)), 0) as stock_value')
            )
            ->groupBy('c.id', 'c.name')
            ->orderByDesc('product_count')
            ->limit($limit);

        if ($permittedLocations != 'all') {
            $query->where(function ($q) use ($permittedLocations) {
                $q->whereNull('vld.location_id')
                    ->orWhereIn('vld.location_id', $permittedLocations);
            });
        }
        if (! empty($locationId)) {
            $query->where(function ($q) use ($locationId) {
                $q->whereNull('vld.location_id')
                    ->orWhere('vld.location_id', $locationId);
            });
        }

        return $query->get();
    }
}
