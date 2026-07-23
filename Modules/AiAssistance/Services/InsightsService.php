<?php

namespace Modules\AiAssistance\Services;

use App\Contact;
use App\Transaction;
use Illuminate\Support\Facades\DB;
use Modules\Crm\Entities\CrmContact;

class InsightsService
{
    public function forBusiness(int $business_id): array
    {
        $start = now()->subDays(29)->startOfDay();
        $end = now()->endOfDay();

        $sales = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->whereBetween('transaction_date', [$start, $end])
            ->selectRaw('COUNT(*) as order_count, COALESCE(SUM(final_total), 0) as total_sales')
            ->first();

        $order_count = (int) ($sales->order_count ?? 0);
        $total_sales = (float) ($sales->total_sales ?? 0);
        $aov = $order_count > 0 ? ($total_sales / $order_count) : 0;

        $customers = Contact::where('business_id', $business_id)
            ->whereIn('type', ['customer', 'both'])
            ->count();

        $leads_count = 0;
        if (class_exists(CrmContact::class) && app(\App\Utils\ModuleUtil::class)->isModuleInstalled('Crm')) {
            $leads_count = CrmContact::where('business_id', $business_id)->leadsOnly()->count();
        }

        $stock_rows = DB::table('products')
            ->leftJoin('variation_location_details as vld', 'products.id', '=', 'vld.product_id')
            ->where('products.business_id', $business_id)
            ->where('products.enable_stock', 1)
            ->where('products.is_inactive', 0)
            ->groupBy('products.id', 'products.name', 'products.alert_quantity')
            ->select(
                'products.id',
                'products.name',
                'products.alert_quantity',
                DB::raw('COALESCE(SUM(vld.qty_available), 0) as qty')
            )
            ->get();

        $out_of_stock = $stock_rows->filter(fn ($r) => (float) $r->qty <= 0)->values();
        $low_stock = $stock_rows->filter(function ($r) {
            $qty = (float) $r->qty;
            $alert = (float) ($r->alert_quantity ?? 0);

            return $qty > 0 && $alert > 0 && $qty <= $alert;
        })->values();

        $attention = $out_of_stock->merge($low_stock)->unique('id')->values();
        $lowest = $stock_rows->sortBy('qty')->take(5)->values();

        $due_query = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->whereIn('payment_status', ['due', 'partial']);

        $open_invoices = (clone $due_query)->count();

        $due_rows = (clone $due_query)
            ->select('id', 'invoice_no', 'final_total', 'transaction_date')
            ->orderBy('transaction_date')
            ->limit(200)
            ->get();

        $outstanding = 0.0;
        $overdue = 0;
        $oldest = null;
        foreach ($due_rows as $row) {
            $paid = (float) DB::table('transaction_payments')
                ->where('transaction_id', $row->id)
                ->sum('amount');
            $remaining = (float) $row->final_total - $paid;
            if ($remaining < 0) {
                $remaining = 0;
            }
            $outstanding += $remaining;
            $txn_date = \Carbon\Carbon::parse($row->transaction_date);
            if ($txn_date->lt(now()->startOfDay())) {
                $overdue++;
                if (! $oldest) {
                    $oldest = [
                        'invoice_no' => $row->invoice_no,
                        'amount' => $remaining,
                    ];
                }
            }
        }

        $product_count = DB::table('products')
            ->where('business_id', $business_id)
            ->where('is_inactive', 0)
            ->count();

        $actions = [];
        if ($out_of_stock->count() > 0) {
            $actions[] = [
                'priority' => 'high',
                'title' => 'Restock '.$out_of_stock->count().' out-of-stock products',
                'description' => __('aiassistance::lang.restock_desc'),
                'url' => action([\App\Http\Controllers\ReportController::class, 'getStockReport']),
            ];
        }
        if ($low_stock->count() > 0) {
            $names = $low_stock->take(3)->pluck('name')->implode(', ');
            $actions[] = [
                'priority' => 'medium',
                'title' => 'Reorder '.$low_stock->count().' low-stock items',
                'description' => __('aiassistance::lang.reorder_desc').($names ? ': '.$names : ''),
                'url' => action([\App\Http\Controllers\ReportController::class, 'getStockReport']),
            ];
        }
        if ($overdue > 0 || $outstanding > 0) {
            $desc = __('aiassistance::lang.follow_overdue_desc');
            if ($oldest) {
                $desc .= ' '.$oldest['invoice_no'].' ('.number_format($oldest['amount'], 2).').';
            }
            $actions[] = [
                'priority' => 'high',
                'title' => 'Follow up on '.$overdue.' overdue invoices',
                'description' => $desc.' Total: '.number_format($outstanding, 2),
                'url' => action([\App\Http\Controllers\SellController::class, 'index'], ['payment_status' => 'due']),
            ];
        }
        if ($leads_count > 0) {
            $leads_url = action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'customer']);
            try {
                if (app(\App\Utils\ModuleUtil::class)->isModuleInstalled('Crm') && \Illuminate\Support\Facades\Route::has('crm.leads.index')) {
                    $leads_url = route('crm.leads.index');
                }
            } catch (\Throwable $e) {
                // keep fallback
            }
            $actions[] = [
                'priority' => 'medium',
                'title' => 'Review '.$leads_count.' active leads',
                'description' => __('aiassistance::lang.review_leads_desc'),
                'url' => $leads_url,
            ];
        }

        return [
            'period' => __('aiassistance::lang.last_30_days'),
            'sales' => [
                'total' => $total_sales,
                'order_count' => $order_count,
                'aov' => $aov,
                'leads' => $leads_count,
                'customers' => $customers,
            ],
            'inventory' => [
                'product_count' => $product_count,
                'attention_count' => $attention->count(),
                'out_of_stock_count' => $out_of_stock->count(),
                'low_stock_count' => $low_stock->count(),
                'lowest' => $lowest->map(fn ($r) => [
                    'name' => $r->name,
                    'qty' => (float) $r->qty,
                    'alert' => (float) ($r->alert_quantity ?? 0),
                ])->all(),
            ],
            'invoices' => [
                'open' => $open_invoices,
                'overdue' => $overdue,
                'outstanding' => $outstanding,
                'oldest' => $oldest,
            ],
            'actions' => $actions,
        ];
    }

    public function snapshotText(int $business_id): string
    {
        $data = $this->forBusiness($business_id);

        return json_encode([
            'period' => $data['period'],
            'sales' => $data['sales'],
            'inventory' => [
                'product_count' => $data['inventory']['product_count'],
                'out_of_stock' => $data['inventory']['out_of_stock_count'],
                'low_stock' => $data['inventory']['low_stock_count'],
                'lowest_stock_items' => $data['inventory']['lowest'],
            ],
            'invoices' => $data['invoices'],
            'suggested_actions' => collect($data['actions'])->map(fn ($a) => [
                'priority' => $a['priority'],
                'title' => $a['title'],
                'description' => $a['description'],
            ])->all(),
        ], JSON_PRETTY_PRINT);
    }
}
