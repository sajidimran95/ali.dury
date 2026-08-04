<?php

namespace Modules\AiAssistance\Services;

use App\Contact;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Crm\Entities\CrmContact;

class InsightsService
{
    /**
     * Live business snapshot with multi-period sales so the chat can answer
     * "today sale", week / month questions from real numbers.
     * Cached briefly so the panel does not lock the UI on every reopen.
     */
    public function forBusiness(int $business_id): array
    {
        return Cache::remember('japsai_insights_'.$business_id, 30, function () use ($business_id) {
            return $this->buildForBusiness($business_id);
        });
    }

    public function forgetCache(int $business_id): void
    {
        Cache::forget('japsai_insights_'.$business_id);
    }

    protected function buildForBusiness(int $business_id): array
    {
        $tz = $this->businessTimezone();
        $now = Carbon::now($tz);

        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();
        $yesterdayStart = $now->copy()->subDay()->startOfDay();
        $yesterdayEnd = $now->copy()->subDay()->endOfDay();
        $weekStart = $now->copy()->startOfWeek(Carbon::SUNDAY)->startOfDay();
        $monthStart = $now->copy()->startOfMonth()->startOfDay();
        $days30Start = $now->copy()->subDays(29)->startOfDay();
        $end = $todayEnd->copy();

        $sales_today = $this->salesSummary($business_id, $todayStart, $todayEnd);
        $sales_yesterday = $this->salesSummary($business_id, $yesterdayStart, $yesterdayEnd);
        $sales_week = $this->salesSummary($business_id, $weekStart, $end);
        $sales_month = $this->salesSummary($business_id, $monthStart, $end);
        $sales_30 = $this->salesSummary($business_id, $days30Start, $end);

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
                ->selectRaw('COALESCE(SUM(IF(is_return = 1, -1 * amount, amount)), 0) as paid')
                ->value('paid');
            $remaining = (float) $row->final_total - $paid;
            if ($remaining < 0) {
                $remaining = 0;
            }
            $outstanding += $remaining;
            $txn_date = Carbon::parse($row->transaction_date);
            if ($txn_date->lt($todayStart) && $remaining > 0) {
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

        // Recent today invoices (for detail questions)
        $today_invoices = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->whereBetween('transaction_date', [$todayStart, $todayEnd])
            ->orderByDesc('transaction_date')
            ->limit(15)
            ->get(['invoice_no', 'final_total', 'payment_status', 'transaction_date']);

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
            'assistant' => 'JapsAI',
            'as_of' => $now->toDateTimeString(),
            'timezone' => $tz,
            'period' => __('aiassistance::lang.last_30_days'),
            'sales' => array_merge($sales_30, [
                'leads' => $leads_count,
                'customers' => $customers,
                // multi-period
                'today' => $sales_today,
                'yesterday' => $sales_yesterday,
                'this_week' => $sales_week,
                'this_month' => $sales_month,
                'last_30_days' => $sales_30,
            ]),
            'today_invoices' => $today_invoices->map(fn ($t) => [
                'invoice_no' => $t->invoice_no,
                'amount' => (float) $t->final_total,
                'payment_status' => $t->payment_status,
                'time' => Carbon::parse($t->transaction_date)->format('H:i'),
            ])->all(),
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

    /**
     * @return array{total:float,order_count:int,aov:float}
     */
    protected function salesSummary(int $business_id, Carbon $start, Carbon $end): array
    {
        $row = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->whereBetween('transaction_date', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->selectRaw('COUNT(*) as order_count, COALESCE(SUM(final_total), 0) as total_sales')
            ->first();

        $order_count = (int) ($row->order_count ?? 0);
        $total_sales = (float) ($row->total_sales ?? 0);
        $aov = $order_count > 0 ? ($total_sales / $order_count) : 0.0;

        return [
            'total' => $total_sales,
            'order_count' => $order_count,
            'aov' => $aov,
        ];
    }

    protected function businessTimezone(): string
    {
        try {
            $tz = session('business.time_zone')
                ?? session('business.timezone')
                ?? config('app.timezone');
            if (! empty($tz)) {
                return (string) $tz;
            }
        } catch (\Throwable $e) {
            // fall through
        }

        return config('app.timezone', 'UTC');
    }

    public function snapshotText(int $business_id): string
    {
        $data = $this->forBusiness($business_id);

        return json_encode([
            'assistant_name' => 'JapsAI',
            'as_of' => $data['as_of'],
            'timezone' => $data['timezone'],
            'sales' => [
                'today' => $data['sales']['today'],
                'yesterday' => $data['sales']['yesterday'],
                'this_week' => $data['sales']['this_week'],
                'this_month' => $data['sales']['this_month'],
                'last_30_days' => $data['sales']['last_30_days'],
                'customers' => $data['sales']['customers'],
                'leads' => $data['sales']['leads'],
            ],
            'today_invoices' => $data['today_invoices'],
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
