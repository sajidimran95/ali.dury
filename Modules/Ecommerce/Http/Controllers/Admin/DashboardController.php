<?php

namespace Modules\Ecommerce\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Ecommerce\Entities\EcommerceSetting;

class DashboardController extends Controller
{
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $settings = EcommerceSetting::forBusiness($business_id);

        $today = Carbon::today();
        $base = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('source', 'ecommerce');

        $order_ids = (clone $base)->pluck('id');
        $meta_counts = \Modules\Ecommerce\Entities\EcomOrderMeta::whereIn('transaction_id', $order_ids)
            ->selectRaw('fulfillment_status, COUNT(*) as c')
            ->groupBy('fulfillment_status')
            ->pluck('c', 'fulfillment_status');

        $stats = [
            'today_orders' => (clone $base)->whereDate('transaction_date', $today)->count(),
            'total_sales' => (clone $base)->where('status', 'final')->sum('final_total'),
            'pending' => (int) ($meta_counts['pending'] ?? 0),
            'completed' => (int) ($meta_counts['delivered'] ?? 0),
            'cancelled' => (int) ($meta_counts['cancelled'] ?? 0),
        ];

        $recent_orders = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('source', 'ecommerce')
            ->with(['contact'])
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $revenue_chart = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('source', 'ecommerce')
            ->where('status', 'final')
            ->where('transaction_date', '>=', Carbon::now()->subDays(14))
            ->selectRaw('DATE(transaction_date) as d, SUM(final_total) as total')
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        return view('ecommerce::admin.dashboard', compact('settings', 'stats', 'recent_orders', 'revenue_chart'));
    }
}
