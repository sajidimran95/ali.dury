<?php

namespace Modules\Ecommerce\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Transaction;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $business_id = session('user.business_id');
        $orders = Transaction::where('business_id', $business_id)
            ->where('source', 'ecommerce')
            ->where('type', 'sell')
            ->where('status', 'final');

        $summary = [
            'orders' => (clone $orders)->count(),
            'revenue' => (clone $orders)->sum('final_total'),
            'avg_order' => (clone $orders)->avg('final_total'),
        ];

        $top_products = DB::table('transaction_sell_lines as tsl')
            ->join('transactions as t', 't.id', '=', 'tsl.transaction_id')
            ->join('products as p', 'p.id', '=', 'tsl.product_id')
            ->where('t.business_id', $business_id)
            ->where('t.source', 'ecommerce')
            ->selectRaw('p.name, SUM(tsl.quantity) as qty, SUM(tsl.quantity * tsl.unit_price_inc_tax) as revenue')
            ->groupBy('p.name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        return view('ecommerce::admin.reports.index', compact('summary', 'top_products'));
    }
}
