<?php

namespace Modules\Ecommerce\Http\Controllers\Admin;

use App\Contact;
use App\Http\Controllers\Controller;
use App\Product;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Ecommerce\Entities\EcommerceSetting;
use Modules\Ecommerce\Entities\EcomCoupon;
use Modules\Ecommerce\Entities\EcomOrderMeta;
use Modules\Ecommerce\Entities\EcomReview;
use Modules\Ecommerce\Support\WholesaleAccount;

class DashboardController extends Controller
{
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $settings = EcommerceSetting::forBusiness($business_id);

        $today = Carbon::today();
        $start30 = Carbon::now()->subDays(29)->startOfDay();
        $start7 = Carbon::now()->subDays(6)->startOfDay();

        $base = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('source', 'ecommerce');

        $final = (clone $base)->where('status', 'final');

        $order_ids = (clone $base)->pluck('id');
        $meta_counts = EcomOrderMeta::whereIn('transaction_id', $order_ids)
            ->selectRaw('fulfillment_status, COUNT(*) as c')
            ->groupBy('fulfillment_status')
            ->pluck('c', 'fulfillment_status');

        $today_sales = (clone $final)->whereDate('transaction_date', $today)->sum('final_total');
        $sales_7d = (clone $final)->where('transaction_date', '>=', $start7)->sum('final_total');
        $sales_30d = (clone $final)->where('transaction_date', '>=', $start30)->sum('final_total');
        $orders_30d = (clone $final)->where('transaction_date', '>=', $start30)->count();
        $avg_order = $orders_30d > 0 ? ($sales_30d / $orders_30d) : 0;

        $online_customers = Contact::where('business_id', $business_id)
            ->onlyCustomers()
            ->whereNotNull('password')
            ->count();

        $pending_customers = Contact::where('business_id', $business_id)
            ->onlyCustomers()
            ->whereNotNull('password')
            ->where('custom_field6', WholesaleAccount::PENDING)
            ->count();

        $online_products = Product::where('business_id', $business_id)
            ->active()
            ->forEcommerce()
            ->count();

        $pending_reviews = EcomReview::where('business_id', $business_id)
            ->where('status', 'pending')
            ->count();

        $active_coupons = EcomCoupon::where('business_id', $business_id)
            ->where('is_active', 1)
            ->count();

        $stats = [
            'today_orders' => (clone $base)->whereDate('transaction_date', $today)->count(),
            'today_sales' => $today_sales,
            'total_sales' => (clone $final)->sum('final_total'),
            'total_orders' => (clone $final)->count(),
            'sales_7d' => $sales_7d,
            'sales_30d' => $sales_30d,
            'avg_order' => $avg_order,
            'pending' => (int) ($meta_counts['pending'] ?? 0) + (int) ($meta_counts['confirmed'] ?? 0),
            'processing' => (int) ($meta_counts['processing'] ?? 0) + (int) ($meta_counts['packed'] ?? 0),
            'shipped' => (int) ($meta_counts['shipped'] ?? 0),
            'completed' => (int) ($meta_counts['delivered'] ?? 0),
            'cancelled' => (int) ($meta_counts['cancelled'] ?? 0) + (int) ($meta_counts['returned'] ?? 0),
            'customers' => $online_customers,
            'pending_customers' => $pending_customers,
            'products' => $online_products,
            'pending_reviews' => $pending_reviews,
            'coupons' => $active_coupons,
        ];

        $fulfillment = [
            'pending' => (int) ($meta_counts['pending'] ?? 0),
            'confirmed' => (int) ($meta_counts['confirmed'] ?? 0),
            'processing' => (int) ($meta_counts['processing'] ?? 0),
            'packed' => (int) ($meta_counts['packed'] ?? 0),
            'shipped' => (int) ($meta_counts['shipped'] ?? 0),
            'delivered' => (int) ($meta_counts['delivered'] ?? 0),
            'cancelled' => (int) ($meta_counts['cancelled'] ?? 0),
            'returned' => (int) ($meta_counts['returned'] ?? 0),
        ];

        // 30-day revenue + order count series (fill missing days)
        $raw_chart = (clone $final)
            ->where('transaction_date', '>=', $start30)
            ->selectRaw('DATE(transaction_date) as d, SUM(final_total) as total, COUNT(*) as orders')
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');

        $revenue_labels = [];
        $revenue_totals = [];
        $order_counts = [];
        for ($i = 0; $i < 30; $i++) {
            $day = $start30->copy()->addDays($i);
            $key = $day->format('Y-m-d');
            $revenue_labels[] = $day->format('M j');
            $revenue_totals[] = round((float) ($raw_chart[$key]->total ?? 0), 2);
            $order_counts[] = (int) ($raw_chart[$key]->orders ?? 0);
        }

        $payment_breakdown = EcomOrderMeta::whereIn('transaction_id', $order_ids)
            ->selectRaw("COALESCE(NULLIF(payment_method, ''), 'unknown') as method, COUNT(*) as c")
            ->groupBy('method')
            ->pluck('c', 'method')
            ->toArray();

        $top_products = DB::table('transaction_sell_lines as tsl')
            ->join('transactions as t', 't.id', '=', 'tsl.transaction_id')
            ->join('products as p', 'p.id', '=', 'tsl.product_id')
            ->where('t.business_id', $business_id)
            ->where('t.source', 'ecommerce')
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->where('t.transaction_date', '>=', $start30)
            ->selectRaw('p.id, p.name, SUM(tsl.quantity) as qty, SUM(tsl.quantity * tsl.unit_price_inc_tax) as revenue')
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('qty')
            ->limit(8)
            ->get();

        $recent_orders = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('source', 'ecommerce')
            ->with(['contact'])
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $metas = EcomOrderMeta::whereIn('transaction_id', $recent_orders->pluck('id'))
            ->get()
            ->keyBy('transaction_id');

        $recent_reviews = EcomReview::where('business_id', $business_id)
            ->with(['product', 'contact'])
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $chart = [
            'labels' => $revenue_labels,
            'revenue' => $revenue_totals,
            'orders' => $order_counts,
            'fulfillment_labels' => array_keys(array_filter($fulfillment)),
            'fulfillment_values' => array_values(array_filter($fulfillment)),
            'payment_labels' => array_map(function ($m) {
                return strtoupper($m === 'unknown' ? 'N/A' : $m);
            }, array_keys($payment_breakdown)),
            'payment_values' => array_values($payment_breakdown),
        ];

        return view('ecommerce::admin.dashboard', compact(
            'settings',
            'stats',
            'fulfillment',
            'recent_orders',
            'metas',
            'top_products',
            'recent_reviews',
            'chart'
        ));
    }
}
