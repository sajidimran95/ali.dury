<?php

namespace Modules\Ecommerce\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $business_id = session('user.business_id');
        $base = Transaction::where('business_id', $business_id)
            ->where('source', 'ecommerce')
            ->where('type', 'sell')
            ->where('status', 'final');

        $summary = [
            'orders' => (int) (clone $base)->count(),
            'revenue' => (float) (clone $base)->sum('final_total'),
            'avg_order' => (float) (clone $base)->avg('final_total'),
            'today_orders' => (int) (clone $base)->whereDate('transaction_date', Carbon::today())->count(),
            'today_sales' => (float) (clone $base)->whereDate('transaction_date', Carbon::today())->sum('final_total'),
            'this_month_orders' => (int) (clone $base)
                ->whereBetween('transaction_date', [
                    Carbon::now()->startOfMonth()->toDateTimeString(),
                    Carbon::now()->endOfMonth()->toDateTimeString(),
                ])
                ->count(),
            'this_month_sales' => (float) (clone $base)
                ->whereBetween('transaction_date', [
                    Carbon::now()->startOfMonth()->toDateTimeString(),
                    Carbon::now()->endOfMonth()->toDateTimeString(),
                ])
                ->sum('final_total'),
        ];

        $top_products = DB::table('transaction_sell_lines as tsl')
            ->join('transactions as t', 't.id', '=', 'tsl.transaction_id')
            ->join('products as p', 'p.id', '=', 'tsl.product_id')
            ->where('t.business_id', $business_id)
            ->where('t.source', 'ecommerce')
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->selectRaw('p.name, SUM(tsl.quantity) as qty, SUM(tsl.quantity * tsl.unit_price_inc_tax) as revenue')
            ->groupBy('p.name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        return view('ecommerce::admin.reports.index', compact('summary', 'top_products'));
    }
}
