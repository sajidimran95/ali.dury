<?php

namespace Modules\AiAssistance\Services;

use App\BusinessLocation;
use App\Contact;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\AiAssistance\Entities\AiAssistanceSetting;
use Modules\AiAssistance\Entities\AiMatchReview;

/**
 * Brief-driven intelligence (forecast, AR risk, 3-way match, etc.).
 * Read-only against core POS tables except explicit draft PO creation.
 */
class IntelligenceService
{
    public const MATCH_TOLERANCE_PCT = 2.0;

    public const SLOW_DAYS = 90;

    public const DEAD_DAYS = 180;

    public const FORECAST_DAYS = 90;

    public function snapshot(int $business_id): array
    {
        $tz = (string) (session('business.time_zone') ?? config('app.timezone', 'UTC'));
        $now = Carbon::now($tz);

        $tol = 2.0;
        try {
            $tol = (float) (AiAssistanceSetting::forBusiness($business_id)->match_tolerance_pct ?: 2);
        } catch (\Throwable $e) {
            $tol = 2.0;
        }

        $forecast = $this->demandForecast($business_id, $now);
        $slow = $this->slowMovers($business_id, $now);
        $collections = $this->collectionsRisk($business_id, $now);
        $credit = $this->creditRisk($business_id, $now);
        $cash = $this->cashFlow($business_id, $now);
        $churn = $this->churn($business_id, $now);
        $match = $this->threeWayMatch($business_id, $tol);
        $vendors = $this->vendorScores($business_id, $now);
        $regulated = $this->regulated($business_id, $now);
        $anomalies = $this->anomalies($business_id, $now);
        $cross = $this->crossSellPairs($business_id, $now);
        $supplier_prices = $this->cheaperSupplierPrices($business_id, $now);

        $actions = $this->suggestedActions($forecast, $slow, $collections, $churn, $match, $regulated, $anomalies, $supplier_prices);

        return [
            'as_of' => $now->toDateTimeString(),
            'timezone' => $tz,
            'basis' => 'Based on last '.self::FORECAST_DAYS.' days of POS sales, inventory, PO, receiving, and AR data. Drafts only — nothing is auto-sent.',
            'forecast' => $forecast,
            'slow_movers' => $slow,
            'collections' => $collections,
            'credit' => $credit,
            'cash_flow' => $cash,
            'churn' => $churn,
            'match' => $match,
            'vendors' => $vendors,
            'supplier_prices' => $supplier_prices,
            'regulated' => $regulated,
            'anomalies' => $anomalies,
            'cross_sell' => $cross,
            'actions' => $actions,
            'digest' => $this->digestText($forecast, $slow, $collections, $churn, $match, $anomalies, $now, $supplier_prices),
        ];
    }

    /**
     * One report only — used by the chat modal so a chip click is fast and does not 500 the pane.
     */
    public function sectionData(int $business_id, string $section): array
    {
        $tz = (string) (session('business.time_zone') ?? config('app.timezone', 'UTC'));
        $now = Carbon::now($tz);
        $data = [
            'as_of' => $now->toDateTimeString(),
            'timezone' => $tz,
            'basis' => 'Based on last '.self::FORECAST_DAYS.' days of POS data. Drafts only.',
            'forecast' => ['ready' => [], 'ready_count' => 0, 'insufficient_count' => 0, 'insufficient_sample' => []],
            'slow_movers' => [],
            'collections' => [],
            'credit' => [],
            'cash_flow' => ['total' => 0, 'buckets' => [], 'basis' => ''],
            'churn' => [],
            'match' => ['tolerance_pct' => 2, 'auto_ok_count' => 0, 'exception_count' => 0, 'exceptions' => []],
            'vendors' => [],
            'supplier_prices' => [],
            'regulated' => [
                'regulated_sku_count' => 0,
                'licenses' => ['tracked' => false, 'missing_note' => '', 'expiring' => [], 'missing' => []],
            ],
            'anomalies' => [],
            'cross_sell' => [],
            'actions' => [],
            'digest' => '',
            'error' => null,
        ];

        try {
            switch ($section) {
                case 'forecast':
                    $data['forecast'] = $this->demandForecast($business_id, $now);
                    break;
                case 'collections':
                    $data['collections'] = $this->collectionsRisk($business_id, $now);
                    break;
                case 'match':
                    $tol = 2.0;
                    try {
                        $tol = (float) (AiAssistanceSetting::forBusiness($business_id)->match_tolerance_pct ?: 2);
                    } catch (\Throwable $e) {
                        $tol = 2.0;
                    }
                    $data['match'] = $this->threeWayMatch($business_id, $tol);
                    break;
                case 'slow':
                    $data['slow_movers'] = $this->slowMovers($business_id, $now);
                    break;
                case 'credit':
                    $data['credit'] = $this->creditRisk($business_id, $now);
                    break;
                case 'cash':
                    $data['cash_flow'] = $this->cashFlow($business_id, $now);
                    break;
                case 'cross':
                    $data['cross_sell'] = $this->crossSellPairs($business_id, $now);
                    break;
                case 'churn':
                    $data['churn'] = $this->churn($business_id, $now);
                    break;
                case 'vendors':
                    $data['vendors'] = $this->vendorScores($business_id, $now);
                    break;
                case 'prices':
                    $data['supplier_prices'] = $this->cheaperSupplierPrices($business_id, $now);
                    break;
                case 'compliance':
                    $data['regulated'] = $this->regulated($business_id, $now);
                    break;
                case 'anomalies':
                    $data['anomalies'] = $this->anomalies($business_id, $now);
                    break;
                case 'digest':
                    $forecast = $this->demandForecast($business_id, $now);
                    $slow = $this->slowMovers($business_id, $now);
                    $collections = $this->collectionsRisk($business_id, $now);
                    $churn = $this->churn($business_id, $now);
                    $match = $this->threeWayMatch($business_id, 2.0);
                    $anomalies = $this->anomalies($business_id, $now);
                    $prices = $this->cheaperSupplierPrices($business_id, $now);
                    $data['digest'] = $this->digestText($forecast, $slow, $collections, $churn, $match, $anomalies, $now, $prices);
                    break;
                default:
                    $data['cash_flow'] = $this->cashFlow($business_id, $now);
                    $data['actions'] = [];
                    break;
            }
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            \Log::warning('JapsAI intelligence section failed', [
                'section' => $section,
                'error' => $e->getMessage(),
            ]);
        }

        return $data;
    }

    public function demandForecast(int $business_id, ?Carbon $now = null): array
    {
        $now = $now ?: Carbon::now();
        $from = $now->copy()->subDays(self::FORECAST_DAYS)->startOfDay();

        $sold = DB::table('transaction_sell_lines as tsl')
            ->join('transactions as t', 't.id', '=', 'tsl.transaction_id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->where('t.transaction_date', '>=', $from)
            ->groupBy('tsl.variation_id', 'tsl.product_id')
            ->select(
                'tsl.variation_id',
                'tsl.product_id',
                DB::raw('SUM(tsl.quantity) as qty_sold'),
                DB::raw('COUNT(DISTINCT DATE(t.transaction_date)) as sale_days')
            )
            ->get()
            ->keyBy('variation_id');

        $stock = DB::table('products as p')
            ->join('variations as v', 'v.product_id', '=', 'p.id')
            ->leftJoin('variation_location_details as vld', 'vld.variation_id', '=', 'v.id')
            ->where('p.business_id', $business_id)
            ->where('p.enable_stock', 1)
            ->where('p.is_inactive', 0)
            ->whereNull('v.deleted_at')
            ->groupBy('v.id', 'p.id', 'p.name', 'v.sub_sku', 'v.default_purchase_price', 'p.alert_quantity')
            ->select(
                'v.id as variation_id',
                'p.id as product_id',
                'p.name',
                'v.sub_sku',
                'v.default_purchase_price',
                'p.alert_quantity',
                DB::raw('COALESCE(SUM(vld.qty_available), 0) as qty')
            )
            ->get();

        $lead = $this->leadTimesByVariation($business_id);
        $lastVendor = $this->lastVendorByVariation($business_id);

        $ready = [];
        $insufficient = [];
        foreach ($stock as $row) {
            $qty = (float) $row->qty;
            $alert = (float) ($row->alert_quantity ?? 0);
            $hist = $sold->get($row->variation_id);
            $qtySold = $hist ? (float) $hist->qty_sold : 0;
            $saleDays = $hist ? (int) $hist->sale_days : 0;
            $velocity = $qtySold / self::FORECAST_DAYS;
            $leadDays = (int) ($lead[$row->variation_id] ?? 7);
            $coverDays = $velocity > 0 ? ($qty / $velocity) : 9999;
            $need = $velocity * ($leadDays + 14);
            $suggested = max(0, ceil($need - $qty));
            if ($suggested < 1 && $alert > 0 && $qty <= $alert) {
                $suggested = max(1, (int) ceil($alert - $qty));
            }

            $item = [
                'variation_id' => (int) $row->variation_id,
                'product_id' => (int) $row->product_id,
                'name' => $row->name,
                'sku' => $row->sub_sku,
                'qty' => $qty,
                'velocity_per_day' => round($velocity, 3),
                'lead_days' => $leadDays,
                'suggested_qty' => (int) $suggested,
                'target_date' => $now->copy()->addDays(max(1, $leadDays))->toDateString(),
                'contact_id' => $lastVendor[$row->variation_id] ?? null,
                'purchase_price' => (float) ($row->default_purchase_price ?? 0),
                'confidence' => $saleDays >= 14 ? 'ok' : 'insufficient',
                'basis' => $saleDays.' sale-days in last '.self::FORECAST_DAYS.' days; lead time '.$leadDays.'d from PO history',
            ];

            if ($item['confidence'] !== 'ok') {
                $insufficient[] = $item;

                continue;
            }
            if ($suggested >= 1 || $coverDays < ($leadDays + 7)) {
                $ready[] = $item;
            }
        }

        usort($ready, fn ($a, $b) => $b['suggested_qty'] <=> $a['suggested_qty']);

        return [
            'ready' => array_slice($ready, 0, 80),
            'ready_count' => count($ready),
            'insufficient_count' => count($insufficient),
            'insufficient_sample' => array_slice($insufficient, 0, 15),
        ];
    }

    public function slowMovers(int $business_id, ?Carbon $now = null): array
    {
        $now = $now ?: Carbon::now();

        $lastSale = DB::table('transaction_sell_lines as tsl')
            ->join('transactions as t', 't.id', '=', 'tsl.transaction_id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->groupBy('tsl.variation_id')
            ->select('tsl.variation_id', DB::raw('MAX(t.transaction_date) as last_sold'))
            ->pluck('last_sold', 'variation_id');

        $rows = DB::table('products as p')
            ->join('variations as v', 'v.product_id', '=', 'p.id')
            ->leftJoin('variation_location_details as vld', 'vld.variation_id', '=', 'v.id')
            ->where('p.business_id', $business_id)
            ->where('p.enable_stock', 1)
            ->where('p.is_inactive', 0)
            ->whereNull('v.deleted_at')
            ->groupBy('v.id', 'p.name', 'v.sub_sku', 'v.default_purchase_price')
            ->select(
                'v.id as variation_id',
                'p.name',
                'v.sub_sku',
                'v.default_purchase_price',
                DB::raw('COALESCE(SUM(vld.qty_available), 0) as qty')
            )
            ->havingRaw('COALESCE(SUM(vld.qty_available), 0) > 0')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $last = $lastSale[$row->variation_id] ?? null;
            $days = $last ? $now->diffInDays(Carbon::parse($last)) : 999;
            if ($days < self::SLOW_DAYS) {
                continue;
            }
            $out[] = [
                'name' => $row->name,
                'sku' => $row->sub_sku,
                'qty' => (float) $row->qty,
                'days_since_sale' => $days,
                'stock_value' => round((float) $row->qty * (float) ($row->default_purchase_price ?? 0), 2),
                'band' => $days >= self::DEAD_DAYS ? 'dead' : 'slow',
                'basis' => $last ? 'Last sale '.$last : 'No sales on record',
            ];
        }
        usort($out, fn ($a, $b) => $b['days_since_sale'] <=> $a['days_since_sale']);

        return array_slice($out, 0, 60);
    }

    public function collectionsRisk(int $business_id, ?Carbon $now = null): array
    {
        $now = $now ?: Carbon::now();
        $today = $now->copy()->startOfDay();

        $due = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->whereIn('payment_status', ['due', 'partial'])
            ->with('contact:id,name,supplier_business_name,email,mobile')
            ->orderBy('transaction_date')
            ->limit(400)
            ->get(['id', 'contact_id', 'invoice_no', 'final_total', 'transaction_date', 'pay_term_number', 'pay_term_type']);

        $paidMap = DB::table('transaction_payments')
            ->whereIn('transaction_id', $due->pluck('id')->all() ?: [0])
            ->select('transaction_id', DB::raw('COALESCE(SUM(IF(is_return = 1, -1 * amount, amount)), 0) as paid'))
            ->groupBy('transaction_id')
            ->pluck('paid', 'transaction_id');

        $lateness = $this->customerLateness($business_id);

        $rows = [];
        foreach ($due as $txn) {
            $paid = (float) ($paidMap[$txn->id] ?? 0);
            $remaining = max(0, (float) $txn->final_total - $paid);
            if ($remaining <= 0) {
                continue;
            }
            $age = Carbon::parse($txn->transaction_date)->diffInDays($today);
            $lateAvg = (float) ($lateness[$txn->contact_id]['avg_late'] ?? 0);
            $score = min(100, round(($age * 0.4) + ($lateAvg * 0.8) + min(30, $remaining / 500)));
            $name = $txn->contact->supplier_business_name ?: ($txn->contact->name ?? 'Customer #'.$txn->contact_id);
            $script = $this->collectionScript($name, $txn->invoice_no, $remaining, $age);
            $rows[] = [
                'transaction_id' => $txn->id,
                'contact_id' => $txn->contact_id,
                'customer' => $name,
                'email' => $txn->contact->email ?? '',
                'mobile' => $txn->contact->mobile ?? '',
                'invoice_no' => $txn->invoice_no,
                'remaining' => round($remaining, 2),
                'days_overdue' => $age,
                'hist_late_days' => round($lateAvg, 1),
                'risk_score' => $score,
                'script' => $script,
                'basis' => 'Days overdue '.$age.', historical lateness '.$lateAvg.'d, balance '.number_format($remaining, 2),
            ];
        }
        usort($rows, fn ($a, $b) => $b['risk_score'] <=> $a['risk_score']);

        return array_slice($rows, 0, 80);
    }

    public function creditRisk(int $business_id, ?Carbon $now = null): array
    {
        $now = $now ?: Carbon::now();
        $curFrom = $now->copy()->subDays(90);
        $prevFrom = $now->copy()->subDays(180);
        $prevTo = $now->copy()->subDays(90);

        $build = function ($from, $to) use ($business_id) {
            return DB::table('transactions as t')
                ->where('t.business_id', $business_id)
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->whereBetween('t.transaction_date', [$from, $to])
                ->groupBy('t.contact_id')
                ->select(
                    't.contact_id',
                    DB::raw('AVG(DATEDIFF(NOW(), t.transaction_date)) as dso'),
                    DB::raw('SUM(CASE WHEN t.payment_status = "paid" THEN 1 ELSE 0 END) / COUNT(*) as on_time'),
                    DB::raw('COUNT(*) as orders')
                )
                ->get()
                ->keyBy('contact_id');
        };

        $cur = $build($curFrom, $now);
        $prev = $build($prevFrom, $prevTo);
        $names = Contact::where('business_id', $business_id)->pluck('name', 'id');

        $flags = [];
        foreach ($cur as $cid => $row) {
            $p = $prev->get($cid);
            if (! $p || (int) $row->orders < 2) {
                continue;
            }
            $dsoWorse = (float) $row->dso > ((float) $p->dso * 1.2 + 3);
            $payWorse = (float) $row->on_time < ((float) $p->on_time - 0.15);
            if (! $dsoWorse && ! $payWorse) {
                continue;
            }
            $flags[] = [
                'contact_id' => (int) $cid,
                'customer' => $names[$cid] ?? ('#'.$cid),
                'dso_now' => round((float) $row->dso, 1),
                'dso_prev' => round((float) $p->dso, 1),
                'on_time_now' => round((float) $row->on_time * 100, 0),
                'on_time_prev' => round((float) $p->on_time * 100, 0),
                'basis' => 'Rolling 90 days vs prior 90 days of invoice/payment history',
            ];
        }

        return array_slice($flags, 0, 40);
    }

    public function cashFlow(int $business_id, ?Carbon $now = null): array
    {
        $now = $now ?: Carbon::now();
        $lateness = $this->customerLateness($business_id);
        $due = $this->collectionsRisk($business_id, $now);
        $buckets = ['0-7' => 0.0, '8-14' => 0.0, '15-28' => 0.0, '29+' => 0.0];
        foreach ($due as $row) {
            $typical = (int) round($lateness[$row['contact_id']]['avg_late'] ?? 14);
            $eta = max(0, $typical - (int) $row['days_overdue']);
            $key = $eta <= 7 ? '0-7' : ($eta <= 14 ? '8-14' : ($eta <= 28 ? '15-28' : '29+'));
            $buckets[$key] += $row['remaining'];
        }

        return [
            'horizon' => 'Next 2–4 weeks',
            'buckets' => array_map(fn ($v) => round($v, 2), $buckets),
            'total' => round(array_sum($buckets), 2),
            'basis' => 'Open AR remaining × each customer typical days-to-pay',
        ];
    }

    public function churn(int $business_id, ?Carbon $now = null): array
    {
        $now = $now ?: Carbon::now();
        $curFrom = $now->copy()->subDays(90);
        $prevFrom = $now->copy()->subDays(180);
        $prevTo = $now->copy()->subDays(90);

        $agg = function ($from, $to) use ($business_id) {
            return DB::table('transactions')
                ->where('business_id', $business_id)
                ->where('type', 'sell')
                ->where('status', 'final')
                ->whereBetween('transaction_date', [$from, $to])
                ->groupBy('contact_id')
                ->select('contact_id', DB::raw('COUNT(*) as orders'), DB::raw('SUM(final_total) as volume'))
                ->get()
                ->keyBy('contact_id');
        };
        $cur = $agg($curFrom, $now);
        $prev = $agg($prevFrom, $prevTo);
        $names = Contact::where('business_id', $business_id)->pluck('name', 'id');

        $out = [];
        foreach ($prev as $cid => $p) {
            if ((int) $p->orders < 3) {
                continue;
            }
            $c = $cur->get($cid);
            $cOrders = $c ? (int) $c->orders : 0;
            $cVol = $c ? (float) $c->volume : 0;
            if ($cOrders <= ((int) $p->orders * 0.5) || $cVol <= ((float) $p->volume * 0.5)) {
                $out[] = [
                    'contact_id' => (int) $cid,
                    'customer' => $names[$cid] ?? ('#'.$cid),
                    'orders_now' => $cOrders,
                    'orders_prev' => (int) $p->orders,
                    'volume_now' => round($cVol, 2),
                    'volume_prev' => round((float) $p->volume, 2),
                    'basis' => 'Last 90 days vs prior 90 days vs this account baseline',
                ];
            }
        }

        return array_slice($out, 0, 40);
    }

    public function threeWayMatch(int $business_id, float $tolerance = self::MATCH_TOLERANCE_PCT): array
    {
        $pos = Transaction::where('business_id', $business_id)
            ->where('type', 'purchase_order')
            ->with(['purchase_lines', 'contact:id,name,supplier_business_name'])
            ->orderByDesc('id')
            ->limit(80)
            ->get();

        $ok = [];
        $exceptions = [];
        foreach ($pos as $po) {
            foreach ($po->purchase_lines as $poLine) {
                $received = DB::table('purchase_lines as pl')
                    ->join('transactions as t', 't.id', '=', 'pl.transaction_id')
                    ->where('t.business_id', $business_id)
                    ->where('t.type', 'purchase')
                    ->where('pl.purchase_order_line_id', $poLine->id)
                    ->selectRaw('COALESCE(SUM(pl.quantity),0) as qty, COALESCE(AVG(pl.purchase_price),0) as unit_cost, MAX(t.id) as purchase_id')
                    ->first();

                $recvQty = (float) ($received->qty ?? 0);
                $recvCost = (float) ($received->unit_cost ?? 0);
                $poQty = (float) $poLine->quantity;
                $poCost = (float) $poLine->purchase_price;
                if ($poQty <= 0) {
                    continue;
                }
                $qtyVar = abs($recvQty - $poQty) / $poQty * 100;
                $costVar = $poCost > 0 ? abs($recvCost - $poCost) / $poCost * 100 : ($recvCost > 0 ? 100 : 0);
                $purchaseId = (int) ($received->purchase_id ?? 0) ?: null;
                $status = ($qtyVar <= $tolerance && $costVar <= $tolerance && $recvQty > 0)
                    ? 'auto_approved'
                    : 'exception';
                $row = [
                    'po_id' => $po->id,
                    'po_line_id' => $poLine->id,
                    'po_ref' => $po->ref_no,
                    'purchase_id' => $purchaseId,
                    'supplier' => $po->contact->supplier_business_name ?: ($po->contact->name ?? ''),
                    'product_id' => $poLine->product_id,
                    'po_qty' => $poQty,
                    'recv_qty' => $recvQty,
                    'po_cost' => $poCost,
                    'recv_cost' => $recvCost,
                    'qty_var_pct' => round($qtyVar, 2),
                    'cost_var_pct' => round($costVar, 2),
                    'status' => $status,
                    'basis' => 'PO line vs warehouse receipts vs invoice unit cost; tolerance '.$tolerance.'%',
                ];
                $this->persistMatchRow($business_id, $row);
                $saved = $this->matchStatus($poLine->id);
                if ($saved === 'reviewed') {
                    continue;
                }
                if ($status === 'auto_approved') {
                    $ok[] = $row;
                } else {
                    $exceptions[] = $row;
                }
            }
        }

        return [
            'tolerance_pct' => $tolerance,
            'auto_ok' => array_slice($ok, 0, 40),
            'auto_ok_count' => count($ok),
            'exceptions' => array_slice($exceptions, 0, 80),
            'exception_count' => count($exceptions),
        ];
    }

    public function vendorScores(int $business_id, ?Carbon $now = null): array
    {
        $now = $now ?: Carbon::now();
        $from = $now->copy()->subDays(180);
        $pos = Transaction::where('business_id', $business_id)
            ->where('type', 'purchase_order')
            ->where('transaction_date', '>=', $from)
            ->with('purchase_lines')
            ->get();

        $by = [];
        foreach ($pos as $po) {
            $cid = (int) $po->contact_id;
            if (! isset($by[$cid])) {
                $by[$cid] = ['ordered' => 0, 'received' => 0, 'on_time' => 0, 'late' => 0, 'po_count' => 0, 'prices' => []];
            }
            $by[$cid]['po_count']++;
            foreach ($po->purchase_lines as $line) {
                $by[$cid]['ordered'] += (float) $line->quantity;
                $by[$cid]['received'] += (float) ($line->po_quantity_purchased ?? 0);
                $by[$cid]['prices'][] = (float) $line->purchase_price;
            }
            $due = $po->delivery_date ?: $po->transaction_date;
            $recvAt = DB::table('transactions')
                ->where('business_id', $business_id)
                ->where('type', 'purchase')
                ->where(function ($q) use ($po) {
                    $q->where('purchase_order_ids', 'like', '%'.$po->id.'%')
                        ->orWhere('id', $po->id);
                })
                ->min('transaction_date');
            if ($recvAt && $due) {
                if (Carbon::parse($recvAt)->lte(Carbon::parse($due)->endOfDay())) {
                    $by[$cid]['on_time']++;
                } else {
                    $by[$cid]['late']++;
                }
            }
        }
        $names = Contact::where('business_id', $business_id)->pluck('supplier_business_name', 'id');
        $out = [];
        foreach ($by as $cid => $s) {
            $fill = $s['ordered'] > 0 ? $s['received'] / $s['ordered'] * 100 : 0;
            $den = $s['on_time'] + $s['late'];
            $onTime = $den > 0 ? ($s['on_time'] / $den * 100) : 0;
            $prices = $s['prices'];
            $avg = count($prices) ? array_sum($prices) / count($prices) : 0;
            $var = 0;
            if ($avg > 0 && count($prices) > 1) {
                $acc = 0;
                foreach ($prices as $p) {
                    $acc += abs($p - $avg) / $avg;
                }
                $var = $acc / count($prices) * 100;
            }
            $score = max(0, min(100, $fill * 0.5 + $onTime * 0.35 + max(0, 100 - $var) * 0.15));
            $out[] = [
                'contact_id' => $cid,
                'vendor' => $names[$cid] ?: ('#'.$cid),
                'fill_rate' => round($fill, 1),
                'on_time_pct' => round($onTime, 1),
                'price_variance_pct' => round($var, 1),
                'po_count' => $s['po_count'],
                'score' => round($score, 0),
                'basis' => 'Fill rate, on-time vs delivery date, price variance — last 180 days PO/receive/invoice',
            ];
        }
        usort($out, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($out, 0, 30);
    }

    public function cheaperSupplierPrices(int $business_id, ?Carbon $now = null): array
    {
        $now = $now ?: Carbon::now();
        $from = $now->copy()->subDays(365);

        $rows = DB::table('purchase_lines as pl')
            ->join('transactions as t', 't.id', '=', 'pl.transaction_id')
            ->join('products as p', 'p.id', '=', 'pl.product_id')
            ->leftJoin('variations as v', 'v.id', '=', 'pl.variation_id')
            ->leftJoin('contacts as c', 'c.id', '=', 't.contact_id')
            ->where('t.business_id', $business_id)
            ->whereIn('t.type', ['purchase', 'purchase_order'])
            ->where('t.transaction_date', '>=', $from)
            ->where('pl.purchase_price', '>', 0)
            ->groupBy(
                'pl.variation_id',
                'pl.product_id',
                'p.name',
                'v.sub_sku',
                't.contact_id',
                'c.name',
                'c.supplier_business_name'
            )
            ->select(
                'pl.variation_id',
                'pl.product_id',
                'p.name as product',
                'v.sub_sku as sku',
                't.contact_id',
                DB::raw('COALESCE(NULLIF(c.supplier_business_name, ""), c.name) as supplier'),
                DB::raw('AVG(pl.purchase_price) as avg_cost'),
                DB::raw('MIN(pl.purchase_price) as min_cost'),
                DB::raw('SUM(pl.quantity) as qty'),
                DB::raw('MAX(t.transaction_date) as last_buy')
            )
            ->get();

        $bySku = [];
        foreach ($rows as $r) {
            $vid = (int) $r->variation_id;
            if (! isset($bySku[$vid])) {
                $bySku[$vid] = [
                    'variation_id' => $vid,
                    'product' => $r->product,
                    'sku' => $r->sku,
                    'suppliers' => [],
                ];
            }
            $bySku[$vid]['suppliers'][] = [
                'contact_id' => (int) $r->contact_id,
                'supplier' => $r->supplier ?: ('#'.$r->contact_id),
                'avg_cost' => round((float) $r->avg_cost, 4),
                'min_cost' => round((float) $r->min_cost, 4),
                'qty' => (float) $r->qty,
                'last_buy' => $r->last_buy,
            ];
        }

        $out = [];
        foreach ($bySku as $item) {
            if (count($item['suppliers']) < 2) {
                continue;
            }
            usort($item['suppliers'], fn ($a, $b) => $a['avg_cost'] <=> $b['avg_cost']);
            $best = $item['suppliers'][0];
            $worst = $item['suppliers'][count($item['suppliers']) - 1];
            if ($best['avg_cost'] <= 0 || $worst['avg_cost'] <= $best['avg_cost']) {
                continue;
            }
            $savePct = round(($worst['avg_cost'] - $best['avg_cost']) / $worst['avg_cost'] * 100, 1);
            $out[] = [
                'product' => $item['product'],
                'sku' => $item['sku'],
                'variation_id' => $item['variation_id'],
                'cheapest_supplier' => $best['supplier'],
                'cheapest_avg' => $best['avg_cost'],
                'costliest_supplier' => $worst['supplier'],
                'costliest_avg' => $worst['avg_cost'],
                'save_pct' => $savePct,
                'suppliers' => $item['suppliers'],
                'basis' => 'Last 12 months purchase + PO unit cost (average). Advice only — not auto-ordered.',
            ];
        }
        usort($out, fn ($a, $b) => $b['save_pct'] <=> $a['save_pct']);

        return array_slice($out, 0, 80);
    }

    public function regulated(int $business_id, ?Carbon $now = null): array
    {
        $now = $now ?: Carbon::now();
        $cats = DB::table('categories')
            ->where('business_id', $business_id)
            ->where(function ($q) {
                $q->where('name', 'like', '%tobacco%')
                    ->orWhere('name', 'like', '%vape%')
                    ->orWhere('name', 'like', '%nicotine%')
                    ->orWhere('name', 'like', '%hemp%')
                    ->orWhere('name', 'like', '%cbd%')
                    ->orWhere('name', 'like', '%cigarette%');
            })
            ->pluck('id');

        $skuCount = 0;
        if ($cats->isNotEmpty()) {
            $skuCount = DB::table('products')
                ->where('business_id', $business_id)
                ->whereIn('category_id', $cats)
                ->where('is_inactive', 0)
                ->count();
        }

        $licenses = ['tracked' => false, 'expiring' => [], 'missing_note' => 'License table not present'];
        if (Schema::hasTable('ecom_customer_licenses')) {
            $licenses['tracked'] = true;
            $licenses['missing_note'] = null;
            $soon = DB::table('ecom_customer_licenses as l')
                ->join('contacts as c', 'c.id', '=', 'l.contact_id')
                ->where('c.business_id', $business_id)
                ->whereNotNull('l.expires_at')
                ->whereBetween('l.expires_at', [$now->toDateString(), $now->copy()->addDays(30)->toDateString()])
                ->select('c.id', 'c.name', 'l.license_type', 'l.expires_at')
                ->limit(40)
                ->get();
            $licenses['expiring'] = $soon->map(fn ($r) => [
                'customer' => $r->name,
                'type' => $r->license_type,
                'expires_at' => $r->expires_at,
            ])->all();
            $licensed = DB::table('ecom_customer_licenses')->pluck('contact_id')->unique()->all();
            $buyers = collect();
            if ($cats->isNotEmpty()) {
                $buyers = DB::table('transactions as t')
                    ->join('transaction_sell_lines as tsl', 'tsl.transaction_id', '=', 't.id')
                    ->join('products as p', 'p.id', '=', 'tsl.product_id')
                    ->join('contacts as c', 'c.id', '=', 't.contact_id')
                    ->where('t.business_id', $business_id)
                    ->where('t.type', 'sell')
                    ->where('t.status', 'final')
                    ->whereIn('p.category_id', $cats)
                    ->whereNotIn('t.contact_id', $licensed ?: [0])
                    ->groupBy('c.id', 'c.name')
                    ->select('c.id', 'c.name')
                    ->limit(40)
                    ->get();
            }
            $licenses['missing'] = $buyers->map(fn ($r) => [
                'customer' => $r->name,
                'contact_id' => $r->id,
            ])->all();
        }

        return [
            'regulated_sku_count' => $skuCount,
            'licenses' => $licenses,
            'basis' => 'Category name tags (tobacco/vape/etc.) and ecom_customer_licenses if the table exists',
        ];
    }

    public function anomalies(int $business_id, ?Carbon $now = null): array
    {
        $now = $now ?: Carbon::now();
        $from = $now->copy()->subDays(30);
        $lines = DB::table('transaction_sell_lines as tsl')
            ->join('transactions as t', 't.id', '=', 'tsl.transaction_id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->where('t.transaction_date', '>=', $from)
            ->select(
                't.id',
                't.invoice_no',
                't.final_total',
                't.discount_amount',
                't.discount_type',
                't.contact_id'
            )
            ->limit(500)
            ->get();

        if ($lines->isEmpty()) {
            return [];
        }
        $avg = $lines->avg('final_total') ?: 1;
        $out = [];
        foreach ($lines as $row) {
            $disc = (float) $row->discount_amount;
            $pct = $row->discount_type === 'percentage' ? $disc : ($row->final_total > 0 ? $disc / (float) $row->final_total * 100 : 0);
            $sizeFlag = (float) $row->final_total > ($avg * 4);
            $discFlag = $pct >= 25;
            if (! $sizeFlag && ! $discFlag) {
                continue;
            }
            $out[] = [
                'invoice_no' => $row->invoice_no,
                'amount' => (float) $row->final_total,
                'discount_pct' => round($pct, 1),
                'reason' => implode(', ', array_filter([$sizeFlag ? 'unusual order size' : null, $discFlag ? 'high discount %' : null])),
                'basis' => 'Last 30 days vs typical ticket size / discount % — flagged for review, not blocked',
            ];
        }

        return array_slice($out, 0, 40);
    }

    public function crossSellPairs(int $business_id, ?Carbon $now = null): array
    {
        $now = $now ?: Carbon::now();
        $from = $now->copy()->subDays(120);
        $pairs = DB::select("
            SELECT a.product_id as a_id, b.product_id as b_id, COUNT(*) as together
            FROM transaction_sell_lines a
            JOIN transaction_sell_lines b ON b.transaction_id = a.transaction_id AND b.product_id > a.product_id
            JOIN transactions t ON t.id = a.transaction_id
            WHERE t.business_id = ? AND t.type = 'sell' AND t.status = 'final'
              AND t.transaction_date >= ?
            GROUP BY a.product_id, b.product_id
            HAVING together >= 3
            ORDER BY together DESC
            LIMIT 25
        ", [$business_id, $from]);

        $ids = [];
        foreach ($pairs as $p) {
            $ids[] = $p->a_id;
            $ids[] = $p->b_id;
        }
        $names = empty($ids) ? collect() : DB::table('products')->whereIn('id', array_unique($ids))->pluck('name', 'id');

        return array_map(fn ($p) => [
            'buy' => $names[$p->a_id] ?? ('#'.$p->a_id),
            'also' => $names[$p->b_id] ?? ('#'.$p->b_id),
            'together' => (int) $p->together,
            'basis' => 'Customers who buy X also buy Y — last 120 days of order lines',
        ], $pairs);
    }

    public function customersIdleDays(int $business_id, int $days = 60): array
    {
        $cut = Carbon::now()->subDays($days);
        $rows = DB::table('transactions as t')
            ->join('contacts as c', 'c.id', '=', 't.contact_id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->groupBy('t.contact_id', 'c.name')
            ->havingRaw('MAX(t.transaction_date) < ?', [$cut])
            ->select('c.name', DB::raw('MAX(t.transaction_date) as last_order'))
            ->orderBy('last_order')
            ->limit(50)
            ->get();

        return $rows->map(fn ($r) => [
            'customer' => $r->name,
            'last_order' => $r->last_order,
        ])->all();
    }

    public function createDraftPurchaseOrder(int $business_id, int $user_id, array $variation_ids): Transaction
    {
        $snap = $this->demandForecast($business_id);
        $wanted = collect($snap['ready'])->whereIn('variation_id', $variation_ids)->values();
        if ($wanted->isEmpty()) {
            throw new \RuntimeException('No forecast lines selected (or insufficient sales history).');
        }

        $contactId = $wanted->pluck('contact_id')->filter()->first();
        if (! $contactId) {
            $contactId = Contact::where('business_id', $business_id)
                ->whereIn('type', ['supplier', 'both'])
                ->value('id');
        }
        if (! $contactId) {
            throw new \RuntimeException('No supplier on file to pre-fill the draft PO.');
        }

        $locationId = BusinessLocation::where('business_id', $business_id)->value('id');
        if (! $locationId) {
            throw new \RuntimeException('No business location found.');
        }

        $total = 0;
        $purchases = [];
        foreach ($wanted as $line) {
            $qty = max(1, (int) $line['suggested_qty']);
            $price = (float) $line['purchase_price'];
            $lineTotal = $qty * $price;
            $total += $lineTotal;
            $purchases[] = [
                'product_id' => $line['product_id'],
                'variation_id' => $line['variation_id'],
                'quantity' => $qty,
                'pp_without_discount' => $price,
                'discount_percent' => 0,
                'purchase_price' => $price,
                'purchase_price_inc_tax' => $price,
                'item_tax' => 0,
                'purchase_line_tax_id' => null,
            ];
        }

        return DB::transaction(function () use ($business_id, $user_id, $contactId, $locationId, $total, $purchases) {
            $transaction = Transaction::create([
                'business_id' => $business_id,
                'location_id' => $locationId,
                'type' => 'purchase_order',
                'status' => 'ordered',
                'contact_id' => $contactId,
                'transaction_date' => Carbon::now()->toDateTimeString(),
                'total_before_tax' => $total,
                'final_total' => $total,
                'discount_type' => 'fixed',
                'discount_amount' => 0,
                'tax_amount' => 0,
                'exchange_rate' => 1,
                'created_by' => $user_id,
                'ref_no' => 'AI-PO-'.Carbon::now()->format('Ymd-His'),
                'additional_notes' => 'JapsAI draft PO. Based on last '.self::FORECAST_DAYS.' days of sales and PO lead times. Review before sending — not auto-sent.',
            ]);

            $util = app(\App\Utils\ProductUtil::class);
            $currency = ['thousand_separator' => ',', 'decimal_separator' => '.', 'symbol' => ''];
            $util->createOrUpdatePurchaseLines($transaction, $purchases, $currency, 0);

            return $transaction->fresh();
        });
    }

    protected function suggestedActions(array $forecast, array $slow, array $collections, array $churn, array $match, array $regulated, array $anomalies, array $supplier_prices = []): array
    {
        $actions = [];
        if (($forecast['ready_count'] ?? 0) > 0) {
            $actions[] = [
                'priority' => 'high',
                'title' => 'Create draft POs for '.$forecast['ready_count'].' SKUs (forecast)',
                'description' => 'Forward-looking reorder from '.self::FORECAST_DAYS.'-day velocity + vendor lead time. Insufficient-history SKUs are excluded.',
                'url' => route('ai.intelligence.index', ['section' => 'forecast']),
            ];
        }
        if (! empty($collections)) {
            $actions[] = [
                'priority' => 'high',
                'title' => 'Collections worklist: '.count($collections).' invoices ranked by risk',
                'description' => 'Not age-only. Draft follow-up scripts are ready — not auto-sent.',
                'url' => route('ai.intelligence.index', ['section' => 'collections']),
            ];
        }
        if (($match['exception_count'] ?? 0) > 0) {
            $actions[] = [
                'priority' => 'high',
                'title' => $match['exception_count'].' 3-way match exceptions',
                'description' => ($match['auto_ok_count'] ?? 0).' lines within '.$match['tolerance_pct'].'% tolerance. Review mismatches only.',
                'url' => route('ai.intelligence.index', ['section' => 'match']),
            ];
        }
        if (! empty($slow)) {
            $actions[] = [
                'priority' => 'medium',
                'title' => count($slow).' slow / dead-stock SKUs',
                'description' => 'Suggest clearance/discount — draft only.',
                'url' => route('ai.intelligence.index', ['section' => 'slow']),
            ];
        }
        if (! empty($churn)) {
            $actions[] = [
                'priority' => 'medium',
                'title' => count($churn).' wholesale accounts dropping off',
                'description' => 'Order frequency/volume vs their own baseline.',
                'url' => route('ai.intelligence.index', ['section' => 'churn']),
            ];
        }
        if (! empty($regulated['licenses']['expiring']) || ! empty($regulated['licenses']['missing'])) {
            $n = count($regulated['licenses']['expiring'] ?? []) + count($regulated['licenses']['missing'] ?? []);
            $actions[] = [
                'priority' => 'medium',
                'title' => $n.' license alerts (missing or expiring)',
                'description' => $regulated['basis'],
                'url' => route('ai.intelligence.index', ['section' => 'compliance']),
            ];
        }
        if (! empty($anomalies)) {
            $actions[] = [
                'priority' => 'low',
                'title' => count($anomalies).' unusual transactions to review',
                'description' => 'Flagged only — not blocked.',
                'url' => route('ai.intelligence.index', ['section' => 'anomalies']),
            ];
        }
        if (! empty($supplier_prices)) {
            $top = $supplier_prices[0];
            $actions[] = [
                'priority' => 'medium',
                'title' => count($supplier_prices).' SKUs have a cheaper supplier',
                'description' => $top['product'].': buy from '.$top['cheapest_supplier'].' (~'.$top['save_pct'].'% vs highest). Last 12 months PO/purchase cost.',
                'url' => route('ai.intelligence.index', ['section' => 'prices']),
            ];
        }

        return $actions;
    }

    public function digestText(array $forecast, array $slow, array $collections, array $churn, array $match, array $anomalies, Carbon $now, array $supplier_prices = []): string
    {
        $topCollect = $collections[0]['customer'] ?? 'none';
        $cheap = $supplier_prices[0]['cheapest_supplier'] ?? 'n/a';
        $lines = [
            'JapsAI digest for '.$now->toFormattedDateString().'.',
            'Forecast: '.($forecast['ready_count'] ?? 0).' SKUs have a draft reorder ready (human review).',
            'Collections: '.count($collections).' open invoices ranked by risk; top account '.$topCollect.'.',
            '3-way match: '.($match['exception_count'] ?? 0).' exceptions, '.($match['auto_ok_count'] ?? 0).' within tolerance.',
            'Watch: '.count($slow).' slow movers, '.count($churn).' accounts dropping off, '.count($anomalies).' odd tickets.',
            'Cheaper suppliers: '.count($supplier_prices).' SKUs (best example vendor '.$cheap.').',
            'Nothing was auto-sent. Open /ai/intelligence to review drafts.',
        ];

        return implode(' ', $lines);
    }

    public function ask(int $business_id, string $message, array $history = []): string
    {
        $m = strtolower(trim($message));
        $snap = $this->snapshot($business_id);

        if (preg_match('/haven.?t ordered|not ordered|idle|60 day/', $m)) {
            $days = 60;
            if (preg_match('/(\d+)\s*day/', $m, $mm)) {
                $days = (int) $mm[1];
            }
            $rows = $this->customersIdleDays($business_id, $days);
            if (empty($rows)) {
                return 'No customers with a last order older than '.$days.' days (live POS).';
            }
            $lines = ['Customers with no order in '.$days.'+ days (live POS):'];
            foreach ($rows as $r) {
                $lines[] = '• '.$r['customer'].' — last '.$r['last_order'];
            }

            return implode("\n", $lines);
        }
        if (str_contains($m, 'forecast') || str_contains($m, 'reorder') || str_contains($m, 'draft po')) {
            return 'Forecast ready SKUs: '.$snap['forecast']['ready_count'].'. Insufficient history: '.$snap['forecast']['insufficient_count'].'. '.$snap['basis'];
        }
        if (str_contains($m, 'collection') || str_contains($m, 'overdue') || str_contains($m, 'unpaid')) {
            $top = array_slice($snap['collections'], 0, 5);
            $lines = ['Top collections by risk score (not age alone):'];
            foreach ($top as $r) {
                $lines[] = '• '.$r['customer'].' '.$r['invoice_no'].' score '.$r['risk_score'].' — '.$r['basis'];
            }

            return implode("\n", $lines) ?: 'No open invoices.';
        }
        if (str_contains($m, 'match') || str_contains($m, '3-way') || str_contains($m, 'three way')) {
            $m2 = $snap['match'];

            return '3-way match: '.$m2['exception_count'].' exceptions, '.$m2['auto_ok_count'].' within '.$m2['tolerance_pct'].'% (PO ↔ receipt ↔ invoice cost). Drafts/review only.';
        }
        if (str_contains($m, 'churn') || str_contains($m, 'drop')) {
            return count($snap['churn']).' accounts dropping vs their own baseline. '.$snap['basis'];
        }
        if (str_contains($m, 'cheaper') || str_contains($m, 'cheap supplier') || str_contains($m, 'better price') || (str_contains($m, 'supplier') && str_contains($m, 'price'))) {
            $rows = array_slice($snap['supplier_prices'] ?? [], 0, 8);
            if (empty($rows)) {
                return 'No SKUs with two or more suppliers and different prices in the last 12 months of purchases/POs.';
            }
            $lines = ['Cheapest supplier by SKU (12-month average PO/purchase cost):'];
            foreach ($rows as $r) {
                $lines[] = '• '.$r['product'].' — buy from '.$r['cheapest_supplier'].' @ '.number_format($r['cheapest_avg'], 2)
                    .' vs '.$r['costliest_supplier'].' @ '.number_format($r['costliest_avg'], 2).' (save '.$r['save_pct'].'%)';
            }

            return implode("\n", $lines);
        }
        if (str_contains($m, 'digest') || str_contains($m, 'summary')) {
            return $snap['digest'];
        }

        $follow = '';
        if (! empty($history)) {
            $last = end($history);
            $follow = ' (follow-up in this session after: '.substr((string) ($last['content'] ?? ''), 0, 80).')';
        }

        return "I can answer from live POS only: idle customers, forecast/POs, collections risk, 3-way match, churn, cash flow, slow stock, vendors, compliance, anomalies, digest. Ask one of those.".$follow."\n\n".$snap['digest'];
    }

    protected function collectionScript(string $name, string $invoice, float $amount, int $age): string
    {
        return 'Hi '.$name.', just checking in on invoice '.$invoice.' — '.$age.' days out, balance '
            .number_format($amount, 2).'. Can you confirm when we should expect payment? Thanks — draft only, not sent.';
    }

    protected function customerLateness(int $business_id): array
    {
        $perInvoice = DB::table('transactions as t')
            ->leftJoin('transaction_payments as tp', function ($j) {
                $j->on('tp.transaction_id', '=', 't.id')->where('tp.is_return', 0);
            })
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->where('t.payment_status', 'paid')
            ->groupBy('t.id', 't.contact_id', 't.transaction_date')
            ->select(
                't.contact_id',
                DB::raw('DATEDIFF(COALESCE(MAX(tp.paid_on), t.transaction_date), t.transaction_date) as late_days')
            );

        $rows = DB::query()
            ->fromSub($perInvoice, 'late')
            ->groupBy('contact_id')
            ->select('contact_id', DB::raw('AVG(late_days) as avg_late'))
            ->get();

        $map = [];
        foreach ($rows as $r) {
            $map[$r->contact_id] = ['avg_late' => (float) $r->avg_late];
        }

        return $map;
    }

    protected function leadTimesByVariation(int $business_id): array
    {
        $rows = DB::table('purchase_lines as pl')
            ->join('transactions as po', 'po.id', '=', 'pl.transaction_id')
            ->where('po.business_id', $business_id)
            ->where('po.type', 'purchase_order')
            ->select('pl.variation_id', 'po.transaction_date', 'pl.created_at')
            ->orderByDesc('po.id')
            ->limit(2000)
            ->get();

        $acc = [];
        foreach ($rows as $r) {
            $vid = (int) $r->variation_id;
            if (isset($acc[$vid])) {
                continue;
            }
            $days = Carbon::parse($r->transaction_date)->diffInDays(Carbon::parse($r->created_at));
            $acc[$vid] = max(3, min(45, $days ?: 7));
        }

        return $acc;
    }

    protected function lastVendorByVariation(int $business_id): array
    {
        $rows = DB::table('purchase_lines as pl')
            ->join('transactions as t', 't.id', '=', 'pl.transaction_id')
            ->where('t.business_id', $business_id)
            ->whereIn('t.type', ['purchase', 'purchase_order'])
            ->orderByDesc('t.id')
            ->limit(3000)
            ->get(['pl.variation_id', 't.contact_id']);

        $map = [];
        foreach ($rows as $r) {
            $vid = (int) $r->variation_id;
            if (! isset($map[$vid])) {
                $map[$vid] = (int) $r->contact_id;
            }
        }

        return $map;
    }

    public function markMatchReviewed(int $business_id, int $po_line_id): void
    {
        if (! Schema::hasTable('ai_match_reviews')) {
            return;
        }
        AiMatchReview::where('business_id', $business_id)
            ->where('po_line_id', $po_line_id)
            ->update(['status' => 'reviewed', 'reviewed_at' => now()]);
    }

    public function posHints(int $business_id, int $contact_id): array
    {
        $credit = collect($this->creditRisk($business_id))->firstWhere('contact_id', $contact_id);
        $due = $this->reorderDue($business_id, $contact_id);

        return [
            'credit' => $credit ? [
                'flag' => true,
                'message' => $credit['customer'].' payment is getting worse (DSO '.$credit['dso_now'].' vs '.$credit['dso_prev'].', on-time '.$credit['on_time_now'].'%). Confirm credit before this order. Based on last 90 vs prior 90 days.',
            ] : ['flag' => false],
            'reorder' => $due,
        ];
    }

    public function reorderDue(int $business_id, int $contact_id): array
    {
        $rows = DB::table('transaction_sell_lines as tsl')
            ->join('transactions as t', 't.id', '=', 'tsl.transaction_id')
            ->join('products as p', 'p.id', '=', 'tsl.product_id')
            ->join('variations as v', 'v.product_id', '=', 'p.id')
            ->where('t.business_id', $business_id)
            ->where('t.contact_id', $contact_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->whereNull('v.deleted_at')
            ->groupBy('p.id', 'p.name', 'v.id')
            ->select(
                'p.id as product_id',
                'p.name',
                'v.id as variation_id',
                DB::raw('COUNT(DISTINCT t.id) as orders'),
                DB::raw('MAX(t.transaction_date) as last_order'),
                DB::raw('MIN(t.transaction_date) as first_order')
            )
            ->havingRaw('COUNT(DISTINCT t.id) >= 3')
            ->orderByDesc('last_order')
            ->limit(8)
            ->get();

        $out = [];
        $now = Carbon::now();
        foreach ($rows as $r) {
            $span = max(1, Carbon::parse($r->first_order)->diffInDays(Carbon::parse($r->last_order)));
            $cadence = $span / max(1, (int) $r->orders - 1);
            $since = Carbon::parse($r->last_order)->diffInDays($now);
            if ($since >= $cadence * 0.9) {
                $out[] = [
                    'name' => $r->name,
                    'product_id' => (int) $r->product_id,
                    'variation_id' => (int) $r->variation_id,
                    'days_since' => $since,
                    'usual_days' => (int) round($cadence),
                    'message' => $r->name.' is usually reordered about every '.(int) round($cadence).' days (last '.$since.' days ago).',
                ];
            }
        }

        return array_slice($out, 0, 3);
    }

    public function addonsForVariation(int $business_id, int $variation_id): array
    {
        $productId = (int) DB::table('variations')->where('id', $variation_id)->value('product_id');
        if (! $productId) {
            return [];
        }

        $rows = DB::select("
            SELECT CASE WHEN a.product_id = ? THEN b.product_id ELSE a.product_id END as other_id, COUNT(*) as together
            FROM transaction_sell_lines a
            JOIN transaction_sell_lines b ON b.transaction_id = a.transaction_id AND b.product_id > a.product_id
            JOIN transactions t ON t.id = a.transaction_id
            WHERE t.business_id = ? AND t.type = 'sell' AND t.status = 'final'
              AND (a.product_id = ? OR b.product_id = ?)
            GROUP BY other_id
            ORDER BY together DESC
            LIMIT 3
        ", [$productId, $business_id, $productId, $productId]);

        $out = [];
        foreach ($rows as $r) {
            $v = DB::table('variations')->where('product_id', $r->other_id)->whereNull('deleted_at')->orderBy('id')->first();
            $name = DB::table('products')->where('id', $r->other_id)->value('name');
            if (! $v) {
                continue;
            }
            $out[] = [
                'product_id' => (int) $r->other_id,
                'variation_id' => (int) $v->id,
                'name' => $name,
                'together' => (int) $r->together,
            ];
        }

        return $out;
    }

    protected function persistMatchRow(int $business_id, array $row): void
    {
        if (! Schema::hasTable('ai_match_reviews') || empty($row['po_line_id'])) {
            return;
        }
        $existing = AiMatchReview::where('po_line_id', $row['po_line_id'])->first();
        if ($existing && $existing->status === 'reviewed') {
            return;
        }
        AiMatchReview::updateOrCreate(
            ['po_line_id' => $row['po_line_id']],
            [
                'business_id' => $business_id,
                'po_id' => $row['po_id'],
                'purchase_id' => $row['purchase_id'],
                'status' => $row['status'],
                'qty_var_pct' => $row['qty_var_pct'],
                'cost_var_pct' => $row['cost_var_pct'],
            ]
        );
    }

    protected function matchStatus(int $po_line_id): ?string
    {
        if (! Schema::hasTable('ai_match_reviews')) {
            return null;
        }

        return AiMatchReview::where('po_line_id', $po_line_id)->value('status');
    }
}
