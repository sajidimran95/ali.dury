<?php

namespace Modules\AiAssistance\Services;

use App\Contact;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Live POS report numbers from the business database (not invented).
 * Used by JapsAI chat and the OpenAI snapshot.
 */
class PosReportDataService
{
    /**
     * @return array{today:array,week:array,month:array,last_30_days:array}
     */
    public function transactionTotals(int $business_id, Carbon $todayStart, Carbon $todayEnd, Carbon $weekStart, Carbon $monthStart, Carbon $days30Start): array
    {
        $periods = [
            'today' => [$todayStart, $todayEnd],
            'this_week' => [$weekStart, $todayEnd],
            'this_month' => [$monthStart, $todayEnd],
            'last_30_days' => [$days30Start, $todayEnd],
        ];

        $out = [];
        foreach ($periods as $key => [$start, $end]) {
            $out[$key] = [
                'sales' => $this->sumType($business_id, 'sell', 'final', $start, $end),
                'sales_returns' => $this->sumType($business_id, 'sell_return', null, $start, $end),
                'purchases' => $this->sumType($business_id, 'purchase', 'received', $start, $end),
                'expenses' => $this->sumType($business_id, 'expense', null, $start, $end),
            ];
        }

        return $out;
    }

    /**
     * @return array{total:float,count:int}
     */
    protected function sumType(int $business_id, string $type, ?string $status, Carbon $start, Carbon $end): array
    {
        $q = Transaction::where('business_id', $business_id)
            ->where('type', $type)
            ->whereBetween('transaction_date', [$start->toDateTimeString(), $end->toDateTimeString()]);

        if ($status !== null) {
            $q->where('status', $status);
        }

        $row = $q->selectRaw('COUNT(*) as cnt, COALESCE(SUM(final_total), 0) as total')->first();

        return [
            'total' => (float) ($row->total ?? 0),
            'count' => (int) ($row->cnt ?? 0),
        ];
    }

    /**
     * Payments received in period (POS cash-in view).
     *
     * @return array{total:float,count:int,by_method:array<int,array{method:string,total:float,count:int}>}
     */
    public function paymentsReceived(int $business_id, Carbon $start, Carbon $end): array
    {
        $rows = DB::table('transaction_payments as tp')
            ->join('transactions as t', 't.id', '=', 'tp.transaction_id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->whereNull('tp.parent_id')
            ->whereBetween('tp.paid_on', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->groupBy('tp.method')
            ->selectRaw("COALESCE(tp.method, 'other') as method, COUNT(*) as cnt, COALESCE(SUM(IF(tp.is_return = 1, -1 * tp.amount, tp.amount)), 0) as total")
            ->orderByDesc('total')
            ->get();

        $byMethod = [];
        $grand = 0.0;
        $count = 0;
        foreach ($rows as $r) {
            $total = (float) $r->total;
            $cnt = (int) $r->cnt;
            $grand += $total;
            $count += $cnt;
            $byMethod[] = [
                'method' => (string) $r->method,
                'total' => $total,
                'count' => $cnt,
            ];
        }

        return [
            'total' => $grand,
            'count' => $count,
            'by_method' => $byMethod,
        ];
    }

    /**
     * Top products by sell qty / revenue in period.
     *
     * @return array<int, array{name:string,sku:string,qty:float,revenue:float}>
     */
    public function topProducts(int $business_id, Carbon $start, Carbon $end, int $limit = 8): array
    {
        $rows = DB::table('transaction_sell_lines as tsl')
            ->join('transactions as t', 't.id', '=', 'tsl.transaction_id')
            ->join('products as p', 'p.id', '=', 'tsl.product_id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->whereNull('tsl.parent_sell_line_id')
            ->whereBetween('t.transaction_date', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->groupBy('p.id', 'p.name', 'p.sku')
            ->selectRaw('p.name, p.sku, COALESCE(SUM(tsl.quantity - COALESCE(tsl.quantity_returned, 0)), 0) as qty, COALESCE(SUM((tsl.quantity - COALESCE(tsl.quantity_returned, 0)) * tsl.unit_price_inc_tax), 0) as revenue')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($r) => [
            'name' => (string) $r->name,
            'sku' => (string) ($r->sku ?? ''),
            'qty' => (float) $r->qty,
            'revenue' => (float) $r->revenue,
        ])->all();
    }

    /**
     * Receivables (customer) and payables (supplier) summary.
     */
    public function balances(int $business_id): array
    {
        // Customer receivables: open/partial sell dues (approx using payment_status + unpaid remainder)
        $dueSells = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->whereIn('payment_status', ['due', 'partial'])
            ->select('id', 'final_total')
            ->limit(500)
            ->get();

        $receivable = 0.0;
        foreach ($dueSells as $row) {
            $paid = (float) DB::table('transaction_payments')
                ->where('transaction_id', $row->id)
                ->selectRaw('COALESCE(SUM(IF(is_return = 1, -1 * amount, amount)), 0) as paid')
                ->value('paid');
            $receivable += max(0, (float) $row->final_total - $paid);
        }

        $duePurchases = Transaction::where('business_id', $business_id)
            ->where('type', 'purchase')
            ->where('status', 'received')
            ->whereIn('payment_status', ['due', 'partial'])
            ->select('id', 'final_total')
            ->limit(500)
            ->get();

        $payable = 0.0;
        foreach ($duePurchases as $row) {
            $paid = (float) DB::table('transaction_payments')
                ->where('transaction_id', $row->id)
                ->selectRaw('COALESCE(SUM(IF(is_return = 1, -1 * amount, amount)), 0) as paid')
                ->value('paid');
            $payable += max(0, (float) $row->final_total - $paid);
        }

        $suppliers = Contact::where('business_id', $business_id)
            ->whereIn('type', ['supplier', 'both'])
            ->count();

        $locations = DB::table('business_locations')
            ->where('business_id', $business_id)
            ->where('is_active', 1)
            ->count();

        return [
            'customer_receivable' => $receivable,
            'supplier_payable' => $payable,
            'suppliers' => $suppliers,
            'locations' => $locations,
        ];
    }

    /**
     * Rough on-hand inventory value (qty × default purchase price when available).
     */
    public function stockValue(int $business_id): array
    {
        $row = DB::table('variation_location_details as vld')
            ->join('variations as v', 'v.id', '=', 'vld.variation_id')
            ->join('products as p', 'p.id', '=', 'v.product_id')
            ->where('p.business_id', $business_id)
            ->where('p.enable_stock', 1)
            ->where('p.is_inactive', 0)
            ->selectRaw('COALESCE(SUM(GREATEST(vld.qty_available, 0) * COALESCE(v.default_purchase_price, 0)), 0) as stock_value, COALESCE(SUM(GREATEST(vld.qty_available, 0)), 0) as total_qty')
            ->first();

        return [
            'stock_value' => (float) ($row->stock_value ?? 0),
            'total_qty' => (float) ($row->total_qty ?? 0),
        ];
    }
}
