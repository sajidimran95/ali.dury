<?php

namespace App\Http\Controllers;

use App\DeletedSale;
use App\Product;
use App\Utils\RecycleBinUtil;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

/**
 * Recycle Bin (Business Settings > System): list, restore and permanently delete
 * soft-deleted products and deleted sales.
 */
class RecycleBinController extends Controller
{
    protected $recycleBin;

    protected $util;

    public function __construct(RecycleBinUtil $recycleBin, Util $util)
    {
        $this->recycleBin = $recycleBin;
        $this->util = $util;
    }

    private function formatDate($date)
    {
        return ! empty($date) ? $this->util->format_date($date, true) : '';
    }

    private function authorizeAccess()
    {
        if (! auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function businessId()
    {
        return request()->session()->get('user.business_id');
    }

    public function counts()
    {
        $this->authorizeAccess();
        $business_id = $this->businessId();

        return [
            'products' => Product::onlyTrashed()->where('business_id', $business_id)->count(),
            'sales' => DeletedSale::where('business_id', $business_id)->count(),
        ];
    }

    /* ---------------- Products ---------------- */

    public function products()
    {
        $this->authorizeAccess();
        $business_id = $this->businessId();

        $query = Product::onlyTrashed()
            ->where('products.business_id', $business_id)
            ->leftJoin('users as u', 'products.deleted_by', '=', 'u.id')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.type',
                'products.deleted_at',
                DB::raw("TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))) as deleted_by_name"),
                DB::raw('(SELECT COUNT(*) FROM transaction_sell_lines tsl WHERE tsl.product_id = products.id) as sales_count')
            );

        return DataTables::of($query)
            ->editColumn('deleted_at', fn ($row) => $this->formatDate($row->deleted_at))
            ->editColumn('sales_count', function ($row) {
                return $row->sales_count > 0
                    ? '<span class="label bg-yellow" title="'.e(__('lang_v1.recycle_product_used_help')).'">'.(int) $row->sales_count.'</span>'
                    : '0';
            })
            ->addColumn('action', function ($row) {
                return '<button type="button" class="btn btn-xs btn-success recycle-restore" data-type="products" data-id="'.$row->id.'"><i class="fa fa-undo"></i> '.__('lang_v1.restore').'</button> '
                    .'<button type="button" class="btn btn-xs btn-danger recycle-force-delete" data-type="products" data-id="'.$row->id.'"><i class="fa fa-trash"></i> '.__('lang_v1.delete_permanently').'</button>';
            })
            ->filterColumn('deleted_by_name', function ($q, $keyword) {
                $q->whereRaw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
            })
            ->rawColumns(['action', 'sales_count'])
            ->make(true);
    }

    public function restoreProduct($id)
    {
        $this->authorizeAccess();

        try {
            return $this->recycleBin->restoreProduct($this->businessId(), $id);
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            return ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }
    }

    public function forceDeleteProduct($id)
    {
        $this->authorizeAccess();

        $product = Product::onlyTrashed()->where('business_id', $this->businessId())->find($id);
        if (empty($product)) {
            return ['success' => false, 'msg' => __('lang_v1.recycle_item_not_found')];
        }

        try {
            DB::beginTransaction();
            $purged = $this->recycleBin->forceDeleteProduct($product);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            return ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }

        return $purged
            ? ['success' => true, 'msg' => __('lang_v1.recycle_deleted_permanently')]
            : ['success' => false, 'msg' => __('lang_v1.recycle_product_in_use')];
    }

    /* ---------------- Sales ---------------- */

    public function sales()
    {
        $this->authorizeAccess();
        $business_id = $this->businessId();

        $query = DeletedSale::where('deleted_sales.business_id', $business_id)
            ->leftJoin('contacts as c', 'deleted_sales.contact_id', '=', 'c.id')
            ->leftJoin('users as u', 'deleted_sales.deleted_by', '=', 'u.id')
            ->select(
                'deleted_sales.id',
                'deleted_sales.invoice_no',
                'deleted_sales.type',
                'deleted_sales.status',
                'deleted_sales.final_total',
                'deleted_sales.transaction_date',
                'deleted_sales.created_at',
                DB::raw("COALESCE(NULLIF(c.supplier_business_name, ''), c.name) as customer"),
                DB::raw("TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))) as deleted_by_name")
            );

        return DataTables::of($query)
            ->editColumn('transaction_date', fn ($row) => $this->formatDate($row->transaction_date))
            ->editColumn('created_at', fn ($row) => $this->formatDate($row->created_at))
            ->editColumn('final_total', fn ($row) => '<span class="display_currency" data-currency_symbol="true">'.$row->final_total.'</span>')
            ->editColumn('type', function ($row) {
                $label = $row->type == 'sales_order' ? __('lang_v1.sales_order') : __('sale.sale');

                return $label.' <small class="text-muted">('.e($row->status).')</small>';
            })
            ->addColumn('action', function ($row) {
                return '<button type="button" class="btn btn-xs btn-success recycle-restore" data-type="sales" data-id="'.$row->id.'"><i class="fa fa-undo"></i> '.__('lang_v1.restore').'</button> '
                    .'<button type="button" class="btn btn-xs btn-danger recycle-force-delete" data-type="sales" data-id="'.$row->id.'"><i class="fa fa-trash"></i> '.__('lang_v1.delete_permanently').'</button>';
            })
            ->filterColumn('customer', function ($q, $keyword) {
                $q->where(function ($w) use ($keyword) {
                    $w->where('c.name', 'like', "%{$keyword}%")->orWhere('c.supplier_business_name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('deleted_by_name', function ($q, $keyword) {
                $q->whereRaw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
            })
            ->rawColumns(['action', 'final_total', 'type'])
            ->make(true);
    }

    public function restoreSale($id)
    {
        $this->authorizeAccess();

        try {
            DB::beginTransaction();
            $output = $this->recycleBin->restoreSale($this->businessId(), $id);
            if ($output['success']) {
                DB::commit();
            } else {
                DB::rollBack();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }

        return $output;
    }

    public function forceDeleteSale($id)
    {
        $this->authorizeAccess();

        $deleted = DeletedSale::where('business_id', $this->businessId())->where('id', $id)->delete();

        return $deleted
            ? ['success' => true, 'msg' => __('lang_v1.recycle_deleted_permanently')]
            : ['success' => false, 'msg' => __('lang_v1.recycle_item_not_found')];
    }

    /* ---------------- Empty ---------------- */

    public function emptyBin(Request $request)
    {
        $this->authorizeAccess();
        $business_id = $this->businessId();
        $type = $request->input('type', 'all');

        $purged_products = 0;
        $kept_products = 0;
        $purged_sales = 0;

        try {
            DB::beginTransaction();

            if (in_array($type, ['all', 'sales'])) {
                $purged_sales = DeletedSale::where('business_id', $business_id)->delete();
            }

            if (in_array($type, ['all', 'products'])) {
                Product::onlyTrashed()->where('business_id', $business_id)
                    ->chunkById(200, function ($products) use (&$purged_products, &$kept_products) {
                        foreach ($products as $product) {
                            if ($this->recycleBin->forceDeleteProduct($product)) {
                                $purged_products++;
                            } else {
                                $kept_products++;
                            }
                        }
                    });
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            return ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }

        $msg = __('lang_v1.recycle_emptied', ['products' => $purged_products, 'sales' => $purged_sales]);
        if ($kept_products > 0) {
            $msg .= ' '.__('lang_v1.recycle_products_kept', ['count' => $kept_products]);
        }

        return ['success' => true, 'msg' => $msg];
    }
}
