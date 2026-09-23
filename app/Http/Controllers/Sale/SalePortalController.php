<?php

namespace App\Http\Controllers\Sale;

use App\BusinessLocation;
use App\Category;
use App\Contact;
use App\CustomerGroup;
use App\EmployeeAttendance;
use App\Http\Controllers\Controller;
use App\Product;
use App\SaleZeroSale;
use App\SaleZeroSaleReason;
use App\Transaction;
use App\TransactionPayment;
use App\Unit;
use App\Utils\BusinessUtil;
use App\Utils\ContactUtil;
use App\Utils\ProductUtil;
use App\Utils\SalesOrderPermission;
use App\Utils\TransactionUtil;
use App\Variation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Mobile sales-rep portal: list own orders, create order for customer.
 * Creates Sales Orders (type=sales_order, status=ordered) — same as admin Sales Orders.
 * Source = sale_app. Admin converts SO → invoice as usual (no admin code changes).
 */
class SalePortalController extends Controller
{
    protected $transactionUtil;

    protected $productUtil;

    protected $contactUtil;

    protected $businessUtil;

    protected $saleAppUnitsCache = [];

    public function __construct(
        TransactionUtil $transactionUtil,
        ProductUtil $productUtil,
        ContactUtil $contactUtil,
        BusinessUtil $businessUtil
    ) {
        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;
        $this->contactUtil = $contactUtil;
        $this->businessUtil = $businessUtil;
    }

    public function home(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user_id = auth()->id();

        $locations = BusinessLocation::forDropdown($business_id, false);
        $locationIds = array_map('strval', array_keys($locations->toArray()));
        $current_location_id = $request->session()->get('user.default_location_id');
        if (empty($current_location_id) || ! in_array((string) $current_location_id, $locationIds, true)) {
            $current_location_id = count($locations) ? array_key_first($locations->toArray()) : null;
            if ($current_location_id) {
                $request->session()->put('user.default_location_id', $current_location_id);
            }
        }
        $current_location_name = $current_location_id
            ? ($locations[$current_location_id] ?? $locations[(string) $current_location_id] ?? '—')
            : 'No location';

        $location = null;
        if ($current_location_id) {
            $location = BusinessLocation::where('business_id', $business_id)
                ->where('id', $current_location_id)
                ->first();
        }

        $role_name = 'Sales Rep';
        try {
            $rawRole = (string) (auth()->user()->getRoleNames()->first() ?? '');
            if ($rawRole !== '') {
                $role_name = trim(explode('#', $rawRole)[0]) ?: 'Sales Rep';
                if (strcasecmp($role_name, 'Sales Representative') === 0) {
                    $role_name = 'Sales Rep';
                }
            }
        } catch (\Throwable $e) {
            $role_name = 'Sales Rep';
        }

        $base = Transaction::where('business_id', $business_id)
            ->where('type', 'sales_order')
            ->where('created_by', $user_id);

        $todayStart = Carbon::now()->startOfDay();
        $monthStart = Carbon::now()->startOfMonth();

        $invoiceMap = $this->saleAppInvoiceMap((int) $business_id);
        $convertedIds = array_keys($invoiceMap);
        $invoiceTotals = $this->saleAppInvoiceTotals($invoiceMap);

        $todayRows = (clone $base)->where('transaction_date', '>=', $todayStart)->get(['id', 'final_total', 'prefer_payment_method']);
        $monthRows = (clone $base)->where('transaction_date', '>=', $monthStart)->get(['id', 'final_total', 'prefer_payment_method']);
        $todayCreditRows = $todayRows->filter(fn ($o) => $this->saleAppIsCreditOrder($o));
        $todayCashRows = $todayRows->reject(fn ($o) => $this->saleAppIsCreditOrder($o));

        $stats = [
            'total_orders' => (clone $base)->count(),
            'today_orders' => $todayRows->count(),
            'today_total' => $this->saleAppSumDisplayTotals($todayRows, $invoiceMap, $invoiceTotals),
            'today_cash_total' => $this->saleAppSumDisplayTotals($todayCashRows, $invoiceMap, $invoiceTotals),
            'today_credit_total' => $this->saleAppSumDisplayTotals($todayCreditRows, $invoiceMap, $invoiceTotals),
            'today_credit_orders' => $todayCreditRows->count(),
            'month_orders' => $monthRows->count(),
            'month_total' => $this->saleAppSumDisplayTotals($monthRows, $invoiceMap, $invoiceTotals),
            'paid_orders' => (clone $base)->where('status', 'completed')
                ->when($convertedIds !== [], fn ($q) => $q->whereIn('id', $convertedIds), fn ($q) => $q->whereRaw('1 = 0'))
                ->count(),
            'due_orders' => (clone $base)->where(function ($q) use ($convertedIds) {
                $q->where('status', '!=', 'completed');
                if ($convertedIds !== []) {
                    $q->orWhereNotIn('id', $convertedIds);
                }
            })->count(),
        ];

        $recent = (clone $base)
            ->with(['contact:id,name,mobile,supplier_business_name'])
            ->orderByDesc('id')
            ->limit(8)
            ->get();
        $this->applySaleAppOrderStatus($recent, $invoiceMap, $invoiceTotals);

        // Newest products
        $newProducts = Product::where('business_id', $business_id)
            ->where('type', '!=', 'modifier')
            ->where('is_inactive', 0)
            ->where(function ($q) {
                $q->whereNull('not_for_selling')->orWhere('not_for_selling', 0);
            })
            ->with(['variations' => function ($q) {
                $q->whereNull('deleted_at')->orderBy('id');
            }])
            ->orderByDesc('id')
            ->limit(6)
            ->get()
            ->map(function ($p) use ($current_location_id) {
                $v = $p->variations->first();
                $price = $v ? (float) ($v->sell_price_inc_tax ?: $v->default_sell_price ?: 0) : 0;
                $img = $this->saleProductImage($p->image);
                $inStock = (int) $p->enable_stock !== 1;
                if (! $inStock && $v && $current_location_id) {
                    $qty = DB::table('variation_location_details')
                        ->where('variation_id', $v->id)
                        ->where('location_id', $current_location_id)
                        ->value('qty_available');
                    $inStock = (float) $qty > 0;
                }

                return [
                    'id' => $p->id,
                    'variation_id' => $v ? $v->id : null,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'price' => $price,
                    'image' => $img['url'],
                    'has_image' => $img['has_image'],
                    'in_stock' => $inStock,
                ];
            });

        // Top sellers = products with the highest sold qty (final invoices + this rep's sales orders)
        $topSelect = function ($types) use ($business_id, $user_id) {
            $q = DB::table('transaction_sell_lines as tsl')
                ->join('transactions as t', 't.id', '=', 'tsl.transaction_id')
                ->join('products as p', 'p.id', '=', 'tsl.product_id')
                ->leftJoin('variations as v', 'v.id', '=', 'tsl.variation_id')
                ->where('t.business_id', $business_id)
                ->whereNull('tsl.parent_sell_line_id')
                ->where('p.is_inactive', 0)
                ->where(function ($w) {
                    $w->whereNull('p.not_for_selling')->orWhere('p.not_for_selling', 0);
                })
                ->where(function ($w) use ($types, $user_id) {
                    if (in_array('sell', $types, true)) {
                        $w->orWhere(function ($inner) {
                            $inner->where('t.type', 'sell')->where('t.status', 'final');
                        });
                    }
                    if (in_array('sales_order', $types, true)) {
                        $w->orWhere(function ($inner) use ($user_id) {
                            $inner->where('t.type', 'sales_order')
                                ->where('t.created_by', $user_id)
                                ->where('t.status', '!=', 'cancelled');
                        });
                    }
                })
                ->select(
                    'p.id as product_id',
                    'p.name',
                    'p.sku',
                    'p.image',
                    'p.enable_stock',
                    DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(tsl.variation_id ORDER BY tsl.quantity DESC), ",", 1) as variation_id'),
                    DB::raw('SUM(tsl.quantity) as qty_sold'),
                    DB::raw('SUM(tsl.quantity * COALESCE(tsl.unit_price_inc_tax, 0)) as sales_amount'),
                    DB::raw('MAX(COALESCE(v.sell_price_inc_tax, v.default_sell_price, tsl.unit_price_inc_tax, 0)) as price')
                )
                ->groupBy('p.id', 'p.name', 'p.sku', 'p.image', 'p.enable_stock')
                ->orderByDesc('qty_sold')
                ->limit(8);

            return $q->get();
        };

        $topProducts = $topSelect(['sell', 'sales_order']);
        if ($topProducts->isEmpty()) {
            $topProducts = $topSelect(['sell']);
        }

        $topProducts = $topProducts->map(function ($row) use ($current_location_id) {
            $img = $this->saleProductImage($row->image);
            $row->image_url = $img['url'];
            $row->has_image = $img['has_image'];
            $inStock = (int) ($row->enable_stock ?? 0) !== 1;
            if (! $inStock && $row->variation_id && $current_location_id) {
                $qty = DB::table('variation_location_details')
                    ->where('variation_id', $row->variation_id)
                    ->where('location_id', $current_location_id)
                    ->value('qty_available');
                $inStock = (float) $qty > 0;
            }
            $row->in_stock = $inStock;

            return $row;
        });

        $userName = auth()->user()->first_name ?? auth()->user()->username ?? 'Sales Rep';

        return view('sale.dashboard', compact(
            'stats',
            'recent',
            'locations',
            'current_location_id',
            'current_location_name',
            'userName',
            'location',
            'role_name',
            'newProducts',
            'topProducts'
        ));
    }

    public function orders(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user_id = auth()->id();
        $q = trim((string) $request->get('q', ''));
        $status = (string) $request->get('status', '');
        $section = (string) $request->get('section', '');
        if (! in_array($section, ['orders', 'invoices', 'payments', 'visits', 'day', 'quotes', 'credit'], true)) {
            $section = '';
        }
        $contactFilter = (int) $request->get('contact_id', 0);

        $invoiceMap = $this->saleAppInvoiceMap((int) $business_id);
        $convertedIds = array_keys($invoiceMap);

        $period = (string) $request->get('period', 'monthly');
        if (! in_array($period, ['daily', 'weekly', 'monthly'], true)) {
            $period = 'monthly';
        }
        $monthKey = (string) $request->get('month', now()->format('Y-m'));
        if (! preg_match('/^\d{4}-\d{2}$/', $monthKey)) {
            $monthKey = now()->format('Y-m');
        }
        $dayKey = (string) $request->get('day', now()->format('Y-m-d'));
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dayKey)) {
            $dayKey = now()->format('Y-m-d');
        }
        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfMonth();
        if ($period === 'daily' || $section === 'day') {
            try {
                $periodStart = Carbon::createFromFormat('Y-m-d', $dayKey)->startOfDay();
                $periodEnd = (clone $periodStart)->endOfDay();
            } catch (\Throwable $e) {
                $periodStart = now()->startOfDay();
                $periodEnd = now()->endOfDay();
                $dayKey = now()->format('Y-m-d');
            }
            $period = $section === 'day' ? 'daily' : $period;
        } elseif ($period === 'weekly') {
            try {
                $anchor = Carbon::createFromFormat('Y-m-d', $dayKey);
            } catch (\Throwable $e) {
                $anchor = now();
            }
            $periodStart = $anchor->copy()->startOfWeek();
            $periodEnd = $anchor->copy()->endOfWeek();
        } else {
            try {
                $periodStart = Carbon::createFromFormat('Y-m', $monthKey)->startOfMonth();
                $periodEnd = (clone $periodStart)->endOfMonth();
            } catch (\Throwable $e) {
                $periodStart = now()->startOfMonth();
                $periodEnd = now()->endOfMonth();
                $monthKey = now()->format('Y-m');
            }
        }

        $periodRows = Transaction::where('business_id', $business_id)
            ->where('created_by', $user_id)
            ->where('type', 'sales_order')
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->get(['id', 'final_total', 'contact_id', 'prefer_payment_method', 'transaction_date']);

        $invoiceTotals = $this->saleAppInvoiceTotals($invoiceMap);
        $invoicedTotal = 0.0;
        $periodConvertedIds = [];
        foreach ($periodRows as $row) {
            $stubs = $invoiceMap[(int) $row->id] ?? [];
            if ($stubs !== []) {
                $periodConvertedIds[] = (int) $row->id;
            }
            foreach ($stubs as $inv) {
                $invoicedTotal += (float) ($invoiceTotals[(int) $inv->id] ?? 0);
            }
        }
        $cashTotal = (float) $periodRows->filter(fn ($o) => ! $this->saleAppIsCreditOrder($o))->sum('final_total');
        $orderTotal = (float) $periodRows->sum('final_total');
        $visits = $periodRows->pluck('contact_id')->filter()->unique()->count();
        $creditRows = $periodRows->filter(fn ($o) => $this->saleAppIsCreditOrder($o));
        $cashRows = $periodRows->filter(fn ($o) => ! $this->saleAppIsCreditOrder($o));

        $quoteRows = Transaction::where('business_id', $business_id)
            ->where('created_by', $user_id)
            ->where('type', 'sell')
            ->where('is_quotation', 1)
            ->where('source', 'sale_app')
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->get(['id', 'final_total', 'contact_id']);

        $tx = [
            'quotes' => $quoteRows->count(),
            'quotes_total' => (float) $quoteRows->sum('final_total'),
            'orders' => $periodRows->count(),
            'orders_total' => $orderTotal,
            'invoices' => count($periodConvertedIds),
            'invoices_total' => $invoicedTotal,
            'credit' => $creditRows->count(),
            'credit_total' => (float) $creditRows->sum('final_total'),
            'payments' => $cashRows->count(),
            'payments_total' => $cashTotal,
        ];

        if ($period === 'daily') {
            $prevStart = $periodStart->copy()->subDay()->startOfDay();
            $prevEnd = $periodStart->copy()->subDay()->endOfDay();
        } elseif ($period === 'weekly') {
            $prevStart = $periodStart->copy()->subWeek()->startOfWeek();
            $prevEnd = $periodStart->copy()->subWeek()->endOfWeek();
        } else {
            $prevStart = $periodStart->copy()->subMonth()->startOfMonth();
            $prevEnd = $periodStart->copy()->subMonth()->endOfMonth();
        }

        $prevGoals = $this->saleAppGoalTotals($business_id, $user_id, $prevStart, $prevEnd, $invoiceMap, $invoiceTotals);
        $goalTarget = static function ($previous, $current, $defaultTarget = 1000) {
            $previous = (float) $previous;
            $current = (float) $current;
            // If there's previous data, use it as target
            if ($previous > 0) {
                return $previous;
            }
            // If no previous data but has current, set target 20% higher
            if ($current > 0) {
                return $current * 1.2;
            }
            // Default target if no data at all
            return $defaultTarget;
        };

        $goals = [
            'orders_current' => $orderTotal,
            'invoices_current' => $invoicedTotal,
            'payments_current' => $cashTotal,
            'visits_current' => $visits,
            'orders_target' => $goalTarget($prevGoals['orders'], $orderTotal, 1000),
            'invoices_target' => $goalTarget($prevGoals['invoices'], $invoicedTotal, 1000),
            'payments_target' => $goalTarget($prevGoals['payments'], $cashTotal, 1000),
            'visits_target' => (int) max(1, $goalTarget($prevGoals['visits'], $visits, 10)),
        ];
        $periodLabel = $period === 'daily'
            ? $periodStart->format('M j, Y')
            : ($period === 'weekly'
                ? $periodStart->format('M j').' – '.$periodEnd->format('M j')
                : $periodStart->format('M Y'));

        $dayTimes = $periodRows->pluck('transaction_date')->filter()->sort()->values();
        $dayReport = [
            'date' => $periodStart->format('l, M j, Y'),
            'start' => $dayTimes->isNotEmpty() ? Carbon::parse($dayTimes->first())->format('g:i A') : '—',
            'end' => $dayTimes->isNotEmpty() ? Carbon::parse($dayTimes->last())->format('g:i A') : '—',
            'orders' => $periodRows->count(),
            'orders_total' => $orderTotal,
            'cash' => $cashTotal,
            'credit' => round($orderTotal - $cashTotal, 2),
            'invoices' => count($periodConvertedIds),
            'invoices_total' => $invoicedTotal,
            'visits' => $visits,
        ];

        $visitCustomers = new LengthAwarePaginator([], 0, 20, 1, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        if ($section === 'visits') {
            $visitIds = $periodRows->pluck('contact_id')->filter()->unique()->values()->all();
            $grouped = $periodRows->groupBy('contact_id');
            $visitCustomers = Contact::where('business_id', $business_id)
                ->whereIn('id', $visitIds ?: [0])
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString();
            $visitCustomers->getCollection()->transform(function ($c) use ($grouped) {
                $rows = $grouped->get($c->id, collect());
                $c->visit_orders = $rows->count();
                $c->visit_total = (float) $rows->sum('final_total');

                return $c;
            });
            $orders = new LengthAwarePaginator([], 0, 20, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
        } elseif (in_array($section, ['orders', 'invoices', 'payments', 'day', 'quotes', 'credit'], true) || $status !== '') {
            $listStatus = $section === 'invoices' ? 'invoiced' : ($section === 'payments' ? 'payments' : ($section === 'quotes' ? 'estimate' : ($section === 'credit' ? 'credit' : ($section === 'orders' || $section === 'day' ? 'orders' : $status))));
            $orders = Transaction::where('business_id', $business_id)
                ->where('created_by', $user_id)
                ->where(function ($query) {
                    $query->where('type', 'sales_order')
                        ->orWhere(function ($q) {
                            $q->where('type', 'sell')
                                ->where('is_quotation', 1)
                                ->where('source', 'sale_app');
                        });
                })
                ->with(['contact:id,name,mobile,supplier_business_name'])
                ->when($section !== '', function ($query) use ($periodStart, $periodEnd) {
                    $query->whereBetween('transaction_date', [$periodStart, $periodEnd]);
                })
                ->when($q !== '', function ($query) use ($q, $invoiceMap) {
                    $term = '%'.$q.'%';
                    $matchedSoIds = [];
                    foreach ($invoiceMap as $soId => $invoices) {
                        foreach ($invoices as $inv) {
                            if (stripos((string) ($inv->invoice_no ?? ''), $q) !== false) {
                                $matchedSoIds[] = (int) $soId;
                                break;
                            }
                        }
                    }
                    $query->where(function ($inner) use ($term, $matchedSoIds) {
                        $inner->where('invoice_no', 'like', $term)
                            ->orWhereHas('contact', function ($c) use ($term) {
                                $c->where('name', 'like', $term)
                                    ->orWhere('mobile', 'like', $term)
                                    ->orWhere('supplier_business_name', 'like', $term);
                            });
                        if ($matchedSoIds !== []) {
                            $inner->orWhereIn('id', $matchedSoIds);
                        }
                    });
                })
                ->when($listStatus === 'orders', function ($query) {
                    $query->where('type', 'sales_order');
                })
                ->when($listStatus === 'sale', function ($query) use ($convertedIds) {
                    $query->where('type', 'sales_order');
                    if ($convertedIds !== []) {
                        $query->whereNotIn('id', $convertedIds);
                    }
                })
                ->when($listStatus === 'estimate', function ($query) {
                    $query->where('type', 'sell')->where('is_quotation', 1);
                })
                ->when($listStatus === 'invoiced', function ($query) use ($periodConvertedIds, $convertedIds, $section) {
                    $ids = $section === 'invoices' ? $periodConvertedIds : $convertedIds;
                    $query->where('type', 'sales_order');
                    if ($ids === []) {
                        $query->whereRaw('1 = 0');
                    } else {
                        $query->whereIn('id', $ids);
                    }
                })
                ->when($listStatus === 'payments', function ($query) {
                    $query->where('type', 'sales_order')
                        ->where(function ($q) {
                            $q->whereNull('prefer_payment_method')
                                ->orWhere('prefer_payment_method', '!=', 'credit_sale');
                        });
                })
                ->when($listStatus === 'credit', function ($query) {
                    $query->where('type', 'sales_order')
                        ->where('prefer_payment_method', 'credit_sale');
                })
                ->when($contactFilter > 0, function ($query) use ($contactFilter) {
                    $query->where('contact_id', $contactFilter);
                })
                ->orderByDesc('id')
                ->paginate(20)
                ->withQueryString();
            $this->applySaleAppOrderStatus($orders->getCollection(), $invoiceMap);
        } else {
            $orders = new LengthAwarePaginator([], 0, 20, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
        }

        $reportBase = Transaction::where('business_id', $business_id)
            ->where('created_by', $user_id)
            ->where(function ($query) {
                $query->where('type', 'sales_order')
                    ->orWhere(function ($q) {
                        $q->where('type', 'sell')
                            ->where('is_quotation', 1)
                            ->where('source', 'sale_app');
                    });
            });
        $creditBase = (clone $reportBase)->where('type', 'sales_order')->where('prefer_payment_method', 'credit_sale');
        $cashBase = (clone $reportBase)->where('type', 'sales_order')->where(function ($q) {
            $q->whereNull('prefer_payment_method')
                ->orWhere('prefer_payment_method', '!=', 'credit_sale');
        });
        $reportStats = [
            'orders' => (clone $reportBase)->where('type', 'sales_order')->count(),
            'orders_total' => (float) (clone $reportBase)->where('type', 'sales_order')->sum('final_total'),
            'quotes' => (clone $reportBase)->where('type', 'sell')->where('is_quotation', 1)->count(),
            'quotes_total' => (float) (clone $reportBase)->where('type', 'sell')->where('is_quotation', 1)->sum('final_total'),
            'invoiced' => count($convertedIds),
            'credit' => (clone $creditBase)->count(),
            'credit_total' => (float) (clone $creditBase)->sum('final_total'),
            'cash' => (clone $cashBase)->count(),
            'cash_total' => (float) (clone $cashBase)->sum('final_total'),
        ];

        $listTitle = $section === 'invoices' ? 'Invoices'
            : ($section === 'payments' ? 'Payments'
            : ($section === 'visits' ? 'Visits'
            : ($section === 'quotes' ? 'Quotes'
            : ($section === 'credit' ? 'Credit'
            : ($section === 'day' ? 'Start / End of day' : 'Orders')))));

        return view($section !== '' ? 'sale.orders.list' : 'sale.orders.index', compact(
            'orders', 'q', 'status', 'reportStats', 'contactFilter',
            'period', 'monthKey', 'dayKey', 'goals', 'periodLabel', 'section', 'visitCustomers', 'dayReport', 'listTitle',
            'tx'
        ));
    }

    public function show(Request $request, $id)
    {
        $order = $this->findOwnOrder($id);
        $order->load(['contact', 'sell_lines.product.unit', 'sell_lines.sub_unit', 'sell_lines.variations', 'payment_lines']);
        $invoiceMap = $this->saleAppInvoiceMap((int) $order->business_id);
        $this->applySaleAppInvoiceSnapshot($order, $invoiceMap);
        $amounts = $this->saleAppAmountBreakdown($order);

        return view('sale.orders.show', compact('order', 'amounts'));
    }

    /**
     * Show the same invoice HTML as admin Sales Order → Print Invoice.
     */
    public function downloadInvoice(Request $request, $id)
    {
        $order = $this->findOwnOrder($id);
        $business_id = $request->session()->get('user.business_id');

        try {
            $invoiceMap = $this->saleAppInvoiceMap((int) $order->business_id);
            $printTx = $this->saleAppPrintTransaction($order, $invoiceMap);
            $printTx->loadMissing('location');
            $location = $printTx->location;
            if (empty($location)) {
                throw new \RuntimeException('Order location not found.');
            }

            $business_details = $this->businessUtil->getDetails($business_id);
            // Same layout selection as SellPosController::printInvoice / printInvoicePdf
            $invoice_layout_id = ! empty($printTx->is_direct_sale)
                ? ($location->sale_invoice_layout_id ?: $location->invoice_layout_id)
                : $location->invoice_layout_id;
            $invoice_layout = $this->businessUtil->invoiceLayout($business_id, $invoice_layout_id);

            $receipt_details = $this->transactionUtil->getReceiptDetails(
                $printTx->id,
                $printTx->location_id,
                $invoice_layout,
                $business_details,
                $location,
                'browser'
            );
            $receipt_details->currency = [
                'symbol' => $business_details->currency_symbol,
                'thousand_separator' => $business_details->thousand_separator,
                'decimal_separator' => $business_details->decimal_separator,
            ];

            // Keep the sales-order number (same series as admin Sales Orders).
            $receipt_details->invoice_no = $order->invoice_no;
            if ((int) $printTx->id !== (int) $order->id) {
                $soHeading = data_get($invoice_layout->common_settings, 'sales_order_heading');
                $receipt_details->invoice_heading = ! empty($soHeading) ? $soHeading : __('lang_v1.sales_order');
                if (! empty($invoice_layout->quotation_no_prefix)) {
                    $receipt_details->invoice_no_prefix = $invoice_layout->quotation_no_prefix;
                }
                if (! empty($receipt_details->barcode)) {
                    $receipt_details->barcode = $order->invoice_no;
                }
            }

            // Sale App always uses US Letter invoice design
            $receipt_details->design = 'us_letter';
            $layout = 'sale_pos.receipts.us_letter';
            if (! view()->exists($layout)) {
                $layout = 'sale_pos.receipts.classic';
            }

            $html = view($layout, compact('receipt_details'))->render();
            $title = $order->invoice_no;
            $orderId = $order->id;

            return response()->view('sale.orders.invoice', compact('html', 'title', 'orderId'));
        } catch (\Throwable $e) {
            \Log::error('Sale app invoice show failed: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('status', [
                'success' => 0,
                'msg' => 'Could not open invoice.',
            ]);
        }
    }

    public function printCustomerInvoice(Request $request, $id, $invoiceId)
    {
        $this->saleAppFindCustomer($request, $id);
        $business_id = $request->session()->get('user.business_id');
        $order = Transaction::where('business_id', $business_id)
            ->where('contact_id', $id)
            ->where('id', $invoiceId)
            ->where(function ($q) {
                $q->where(function ($inner) {
                    $inner->where('type', 'sell')->where('status', 'final');
                })->orWhere('type', 'sales_order');
            })
            ->firstOrFail();

        try {
            $printTx = $order;
            if ($order->type === 'sales_order') {
                $invoiceMap = $this->saleAppInvoiceMap((int) $order->business_id);
                $printTx = $this->saleAppPrintTransaction($order, $invoiceMap);
            }
            $printTx->loadMissing('location');
            $location = $printTx->location;
            if (empty($location) && ! empty($printTx->location_id)) {
                $location = BusinessLocation::find($printTx->location_id);
            }
            if (empty($location)) {
                $location = BusinessLocation::where('business_id', $business_id)->first();
            }
            if (empty($location)) {
                throw new \RuntimeException('Invoice location not found.');
            }

            $business_details = $this->businessUtil->getDetails($business_id);
            $invoice_layout_id = ! empty($printTx->is_direct_sale)
                ? ($location->sale_invoice_layout_id ?: $location->invoice_layout_id)
                : $location->invoice_layout_id;
            $invoice_layout = $this->businessUtil->invoiceLayout($business_id, $invoice_layout_id);
            $receipt_details = $this->transactionUtil->getReceiptDetails(
                $printTx->id,
                $printTx->location_id ?: $location->id,
                $invoice_layout,
                $business_details,
                $location,
                'browser'
            );
            $receipt_details->currency = [
                'symbol' => $business_details->currency_symbol,
                'thousand_separator' => $business_details->thousand_separator,
                'decimal_separator' => $business_details->decimal_separator,
            ];
            $receipt_details->invoice_no = $order->invoice_no;
            $receipt_details->design = 'us_letter';
            $layout = view()->exists('sale_pos.receipts.us_letter')
                ? 'sale_pos.receipts.us_letter'
                : 'sale_pos.receipts.classic';
            $html = view($layout, compact('receipt_details'))->render();
            $title = $order->invoice_no;
            $orderId = $order->id;

            return response()->view('sale.orders.invoice', compact('html', 'title', 'orderId'));
        } catch (\Throwable $e) {
            \Log::error('Sale app customer invoice print failed: '.$e->getMessage());

            return back()->with('status', [
                'success' => 0,
                'msg' => 'Could not open invoice.',
            ]);
        }
    }

    /**
     * Delete own sale (same stock/mapping cleanup as admin).
     */
    public function destroy(Request $request, $id)
    {
        $order = $this->findOwnOrder($id);

        if (! SalesOrderPermission::canDelete(auth()->user(), $order)) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        try {
            DB::beginTransaction();
            $output = $this->transactionUtil->deleteSale($business_id, $order->id);
            DB::commit();

            $ok = ! empty($output['success']);
            $msg = $output['msg'] ?? ($ok ? 'Order deleted.' : 'Could not delete order.');

            return redirect()
                ->route('sale.orders')
                ->with('status', ['success' => $ok ? 1 : 0, 'msg' => $msg]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Sale app order delete failed: '.$e->getMessage());

            return redirect()
                ->route('sale.orders')
                ->with('status', ['success' => 0, 'msg' => 'Could not delete order.']);
        }
    }

    /**
     * Own sales order only (created by logged-in sales rep).
     */
    protected function findOwnOrder($id): Transaction
    {
        return Transaction::where('business_id', session('user.business_id'))
            ->where('created_by', auth()->id())
            ->where(function ($query) {
                $query->where('type', 'sales_order')
                    ->orWhere(function ($q) {
                        $q->where('type', 'sell')
                            ->where('is_quotation', 1)
                            ->where('source', 'sale_app');
                    });
            })
            ->findOrFail($id);
    }

    public function create(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $locations = BusinessLocation::forDropdown($business_id, false);
        $default_location = null;
        if (count($locations) === 1) {
            $default_location = array_key_first($locations->toArray());
        } elseif ($request->session()->has('user.default_location_id')) {
            $default_location = $request->session()->get('user.default_location_id');
        } elseif (count($locations) > 0) {
            $default_location = array_key_first($locations->toArray());
        }

        // Only prefill after validation error — create starts on Select Customer
        $default_customer = null;
        $oldContactId = old('contact_id', $request->get('contact_id'));
        if (! empty($oldContactId)) {
            $c = Contact::where('business_id', $business_id)
                ->onlyCustomers()
                ->find($oldContactId);
            if ($c) {
                $default_customer = [
                    'id' => $c->id,
                    'text' => trim(($c->supplier_business_name ? $c->supplier_business_name.' — ' : '').$c->name.
                        ($c->mobile ? ' ('.$c->mobile.')' : '')),
                    'shipping_address' => $c->shipping_address ?: trim(implode(', ', array_filter([
                        $c->address_line_1, $c->city,
                    ]))),
                    'credit_limit' => $c->credit_limit,
                    'acct' => $c->contact_id,
                    'display_name' => trim((string) ($c->supplier_business_name ?: $c->name)),
                    'address' => $c->shipping_address ?: trim(implode(', ', array_filter([$c->address_line_1, $c->city]))),
                ];
            }
        }

        if (empty($default_customer)) {
            return redirect()->route('sale.customers', $request->only('add'));
        }

        $order_draft = $this->saleAppFindSavedDraft(
            (string) $request->get('saved', ''),
            (string) ($default_customer['id'] ?? '')
        );

        return view('sale.orders.create', compact('locations', 'default_location', 'default_customer', 'order_draft'));
    }

    protected function saleAppDraftSessionKey(): string
    {
        return 'sale_order_draft_'.(string) auth()->id();
    }

    protected function saleAppSavedOrdersKey(): string
    {
        return 'sale_saved_orders_'.(string) auth()->id();
    }

    protected function saleAppGetSavedOrders(): array
    {
        $rows = Cache::get($this->saleAppSavedOrdersKey(), []);

        return is_array($rows) ? array_values($rows) : [];
    }

    protected function saleAppPutSavedOrders(array $rows): void
    {
        Cache::forever($this->saleAppSavedOrdersKey(), array_values($rows));
    }

    protected function saleAppFindSavedDraft(string $savedId, string $contactId): ?array
    {
        foreach ($this->saleAppGetSavedOrders() as $row) {
            if ($savedId !== '' && (string) ($row['id'] ?? '') === $savedId) {
                return $row;
            }
            if ($savedId === '' && $contactId !== '' && (string) ($row['contact_id'] ?? '') === $contactId) {
                return $row;
            }
        }

        return null;
    }

    protected function saleAppRemoveSavedForContact($contactId): void
    {
        $rows = array_values(array_filter(
            $this->saleAppGetSavedOrders(),
            fn ($row) => (string) ($row['contact_id'] ?? '') !== (string) $contactId
        ));
        $this->saleAppPutSavedOrders($rows);
    }

    public function savedOrders()
    {
        $saved_orders = $this->saleAppGetSavedOrders();
        usort($saved_orders, function ($a, $b) {
            return strcmp((string) ($b['saved_at'] ?? ''), (string) ($a['saved_at'] ?? ''));
        });

        return view('sale.orders.saved', compact('saved_orders'));
    }

    public function destroySavedOrder($id)
    {
        $rows = array_values(array_filter(
            $this->saleAppGetSavedOrders(),
            fn ($row) => (string) ($row['id'] ?? '') !== (string) $id
        ));
        $this->saleAppPutSavedOrders($rows);

        return redirect()->route('sale.saved_orders')->with('status', [
            'success' => 1,
            'msg' => 'Saved order removed.',
        ]);
    }

    public function saveOrderDraft(Request $request)
    {
        $data = $request->validate([
            'contact_id' => 'required',
            'contact_name' => 'nullable|string',
            'payment_type' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'shipping_details' => 'nullable|string',
            'cart' => 'required|array|min:1',
        ]);

        $total = 0.0;
        foreach ($data['cart'] as $line) {
            $total += ((float) ($line['quantity'] ?? 0)) * ((float) ($line['unit_price'] ?? 0));
        }

        $contactName = trim((string) ($data['contact_name'] ?? ''));
        if ($contactName === '') {
            $c = Contact::find($data['contact_id']);
            if ($c) {
                $contactName = trim((string) ($c->supplier_business_name ?: $c->name));
            }
        }

        $rows = $this->saleAppGetSavedOrders();
        $id = null;
        foreach ($rows as $i => $row) {
            if ((string) ($row['contact_id'] ?? '') === (string) $data['contact_id']) {
                $id = $row['id'] ?? uniqid('so', true);
                $rows[$i] = array_merge($row, $data, [
                    'id' => $id,
                    'contact_name' => $contactName,
                    'total' => round($total, 2),
                    'saved_at' => now()->toDateTimeString(),
                ]);
                break;
            }
        }
        if ($id === null) {
            $id = uniqid('so', true);
            $rows[] = array_merge($data, [
                'id' => $id,
                'contact_name' => $contactName,
                'total' => round($total, 2),
                'saved_at' => now()->toDateTimeString(),
            ]);
        }
        $this->saleAppPutSavedOrders($rows);
        $request->session()->put($this->saleAppDraftSessionKey(), $data);

        return response()->json(['ok' => true, 'id' => $id]);
    }

    public function clearOrderDraft(Request $request)
    {
        $request->session()->forget($this->saleAppDraftSessionKey());

        // Also drop saved draft for this customer so clear cart does not come back on reload
        $contactId = $request->input('contact_id', $request->query('contact_id'));
        if (! empty($contactId)) {
            $this->saleAppRemoveSavedForContact($contactId);
        }

        return response()->json(['ok' => true]);
    }

    public function syncApp(Request $request)
    {
        $request->session()->forget('sale_catalog_cache');

        return response()->json([
            'ok' => true,
            'synced_at' => now()->toIso8601String(),
        ]);
    }

    public function searchCustomers(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $term = trim((string) $request->get('q', ''));

        $query = Contact::where('business_id', $business_id)
            ->onlyCustomers()
            ->where('contact_status', 'active');

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('mobile', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('supplier_business_name', 'like', "%{$term}%")
                    ->orWhere('contact_id', 'like', "%{$term}%")
                    ->orWhere('address_line_1', 'like', "%{$term}%")
                    ->orWhere('city', 'like', "%{$term}%");
            });
        }

        $rows = $query->orderBy('name')->limit(40)->get([
            'id', 'name', 'mobile', 'supplier_business_name', 'shipping_address',
            'address_line_1', 'address_line_2', 'city', 'credit_limit', 'contact_id',
        ]);

        return response()->json($rows->map(function ($c) {
            $display = trim((string) ($c->supplier_business_name ?: $c->name));
            $address = trim(implode(', ', array_filter([
                $c->address_line_1,
                $c->address_line_2,
                $c->city,
            ])));
            if ($address === '' && ! empty($c->shipping_address)) {
                $address = trim((string) $c->shipping_address);
            }
            $initialsSource = preg_replace('/[^A-Za-z0-9\s]/', '', $display) ?: 'C';
            $parts = preg_split('/\s+/', trim($initialsSource)) ?: [];
            $initials = '';
            foreach (array_slice($parts, 0, 2) as $p) {
                $initials .= strtoupper(mb_substr($p, 0, 1));
            }
            if ($initials === '') {
                $initials = 'C';
            }

            return [
                'id' => $c->id,
                'text' => trim(($c->supplier_business_name ? $c->supplier_business_name.' — ' : '').$c->name.
                    ($c->mobile ? ' ('.$c->mobile.')' : '')),
                'display_name' => $display,
                'name' => $c->name,
                'mobile' => $c->mobile,
                'address' => $address,
                'initials' => $initials,
                'shipping_address' => $c->shipping_address ?: $address,
                'credit_limit' => $c->credit_limit,
                'acct' => $c->contact_id,
            ];
        }));
    }

    public function searchProducts(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $location_id = (int) $request->get('location_id');
        $term = trim((string) $request->get('q', ''));
        $category_id = (int) $request->get('category_id', 0);
        $sub_category_id = (int) $request->get('sub_category_id', 0);
        $variation_id = (int) $request->get('variation_id', 0);
        $limit = min(100, max(1, (int) $request->get('limit', 30)));

        if (empty($location_id)) {
            return response()->json([]);
        }

        $query = Variation::join('products as p', 'variations.product_id', '=', 'p.id')
            ->leftJoin('variation_location_details as vld', function ($join) use ($location_id) {
                $join->on('variations.id', '=', 'vld.variation_id')
                    ->where('vld.location_id', '=', $location_id);
            })
            ->leftJoin('units as u', 'p.unit_id', '=', 'u.id')
            ->leftJoin('categories as cat', 'p.category_id', '=', 'cat.id')
            ->where('p.business_id', $business_id)
            ->where('p.type', '!=', 'modifier')
            ->where('p.is_inactive', 0)
            ->whereNull('variations.deleted_at')
            ->where(function ($q) {
                $q->whereNull('p.not_for_selling')->orWhere('p.not_for_selling', 0);
            });

        if ($variation_id > 0) {
            $query->where('variations.id', $variation_id);
        }

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('p.name', 'like', "%{$term}%")
                    ->orWhere('p.sku', 'like', "%{$term}%")
                    ->orWhere('variations.sub_sku', 'like', "%{$term}%")
                    ->orWhere('variations.name', 'like', "%{$term}%");
            });
        }

        if ($sub_category_id > 0) {
            $query->where('p.sub_category_id', $sub_category_id);
        } elseif ($category_id > 0) {
            $query->where('p.category_id', $category_id);
        }

        $rows = $query->select(
            'variations.id as variation_id',
            'variations.name as variation_name',
            'variations.sub_sku',
            'variations.sell_price_inc_tax',
            'variations.default_sell_price',
            'p.id as product_id',
            'p.name as product_name',
            'p.type as product_type',
            'p.enable_stock',
            'p.tax as tax_id',
            'p.category_id',
            'p.sub_category_id',
            'cat.name as category_name',
            'p.image as product_image',
            'u.id as unit_id',
            'u.actual_name as unit_name',
            'u.short_name as unit_short',
            'u.allow_decimal as allow_decimal',
            DB::raw('COALESCE(vld.qty_available, 0) as stock')
        )
            ->orderBy('p.name')
            ->limit($limit)
            ->get();

        return response()->json($rows->map(function ($r) use ($business_id) {
            $label = $r->product_name;
            if (! empty($r->variation_name) && $r->variation_name !== 'DUMMY') {
                $label .= ' - '.$r->variation_name;
            }
            if (! empty($r->sub_sku)) {
                $label .= ' ('.$r->sub_sku.')';
            }

            $price = (float) ($r->sell_price_inc_tax ?: $r->default_sell_price ?: 0);
            $img = $this->saleProductImage($r->product_image);
            $units = $this->saleAppUnits((int) $business_id, (int) $r->product_id, (int) $r->unit_id);

            return [
                'product_id' => (int) $r->product_id,
                'variation_id' => (int) $r->variation_id,
                'name' => $label,
                'sku' => $r->sub_sku,
                'price' => $price,
                'base_price' => $price,
                'stock' => (float) $r->stock,
                'enable_stock' => (int) $r->enable_stock,
                'product_type' => $r->product_type,
                'unit_id' => (int) $r->unit_id,
                'unit_name' => $r->unit_name ?: ($r->unit_short ?: 'Pc'),
                'allow_decimal' => (int) $r->allow_decimal,
                'units' => $units,
                'tax_id' => $r->tax_id,
                'category_id' => (int) $r->category_id,
                'sub_category_id' => (int) $r->sub_category_id,
                'category_name' => $r->category_name ?: '',
                'image' => $img['url'],
                'has_image' => $img['has_image'],
            ];
        }));
    }

    /**
     * Last purchased lines for a customer (existing sell / sales_order history).
     */
    public function lastPurchases(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $contact_id = (int) $request->get('contact_id');
        $location_id = (int) $request->get('location_id');

        if ($contact_id < 1) {
            return response()->json([]);
        }

        Contact::where('business_id', $business_id)->onlyCustomers()->findOrFail($contact_id);

        $txIds = Transaction::where('business_id', $business_id)
            ->where('contact_id', $contact_id)
            ->where(function ($q) {
                $q->where('type', 'sales_order')
                    ->orWhere(function ($inner) {
                        $inner->where('type', 'sell')->where('status', 'final');
                    });
            })
            ->orderByDesc('id')
            ->limit(40)
            ->pluck('id');

        if ($txIds->isEmpty()) {
            return response()->json([]);
        }

        $query = DB::table('transaction_sell_lines as tsl')
            ->join('transactions as t', 't.id', '=', 'tsl.transaction_id')
            ->join('products as p', 'p.id', '=', 'tsl.product_id')
            ->leftJoin('variations as v', 'v.id', '=', 'tsl.variation_id')
            ->leftJoin('units as u', 'p.unit_id', '=', 'u.id')
            ->whereIn('tsl.transaction_id', $txIds)
            ->whereNull('tsl.parent_sell_line_id')
            ->orderByDesc('t.id');

        if ($location_id > 0) {
            $query->leftJoin('variation_location_details as vld', function ($join) use ($location_id) {
                $join->on('v.id', '=', 'vld.variation_id')
                    ->where('vld.location_id', '=', $location_id);
            });
        }

        $lines = $query->select(
            'tsl.product_id',
            'tsl.variation_id',
            'tsl.quantity',
            'tsl.unit_price_inc_tax',
            'tsl.sub_unit_id',
            't.transaction_date',
            'p.name as product_name',
            'p.type as product_type',
            'p.enable_stock',
            'p.unit_id',
            'v.name as variation_name',
            'v.sub_sku',
            'u.actual_name as unit_name',
            'u.allow_decimal',
            DB::raw($location_id > 0 ? 'COALESCE(vld.qty_available, 0) as stock' : '0 as stock')
        )->get();

        $seen = [];
        $out = [];
        foreach ($lines as $r) {
            $vid = (int) $r->variation_id;
            if ($vid < 1 || isset($seen[$vid])) {
                continue;
            }
            $seen[$vid] = true;

            $name = $r->product_name;
            if (! empty($r->variation_name) && $r->variation_name !== 'DUMMY') {
                $name .= ' - '.$r->variation_name;
            }

            $units = $this->saleAppUnits((int) $business_id, (int) $r->product_id, (int) $r->unit_id);
            $subId = (int) ($r->sub_unit_id ?: $r->unit_id);
            $mult = $this->saleAppUnitMultiplier($units, $subId, (int) $r->unit_id);
            $basePrice = (float) $r->unit_price_inc_tax;
            $qty = $mult > 0 ? ((float) $r->quantity / $mult) : (float) $r->quantity;
            $unitName = $r->unit_name ?: 'Pc';
            foreach ($units as $u) {
                if ((int) $u['id'] === $subId) {
                    $unitName = $u['name'];
                    break;
                }
            }

            $out[] = [
                'product_id' => (int) $r->product_id,
                'variation_id' => $vid,
                'name' => $name.(! empty($r->sub_sku) ? ' ('.$r->sub_sku.')' : ''),
                'sku' => $r->sub_sku,
                'price' => $basePrice * $mult,
                'base_price' => $basePrice,
                'quantity' => $qty,
                'last_qty' => $qty,
                'last_price' => $basePrice * $mult,
                'last_date' => $r->transaction_date ? Carbon::parse($r->transaction_date)->format('m/d/Y') : null,
                'stock' => (float) $r->stock,
                'enable_stock' => (int) $r->enable_stock,
                'product_type' => $r->product_type,
                'unit_id' => (int) $r->unit_id,
                'unit_name' => $unitName,
                'sub_unit_id' => $subId,
                'allow_decimal' => (int) $r->allow_decimal,
                'units' => $units,
            ];
        }

        return response()->json($out);
    }

    /**
     * Past order lines for one product (last-ordered popup).
     */
    public function productOrderHistory(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $contact_id = (int) $request->get('contact_id');
        $variation_id = (int) $request->get('variation_id');

        if ($contact_id < 1 || $variation_id < 1) {
            return response()->json([]);
        }

        Contact::where('business_id', $business_id)->onlyCustomers()->findOrFail($contact_id);

        $rows = DB::table('transaction_sell_lines as tsl')
            ->join('transactions as t', 't.id', '=', 'tsl.transaction_id')
            ->leftJoin('units as u', 'u.id', '=', 'tsl.sub_unit_id')
            ->where('t.business_id', $business_id)
            ->where('t.contact_id', $contact_id)
            ->where('tsl.variation_id', $variation_id)
            ->whereNull('tsl.parent_sell_line_id')
            ->where(function ($q) {
                $q->where('t.type', 'sales_order')
                    ->orWhere(function ($inner) {
                        $inner->where('t.type', 'sell')->where('t.status', 'final');
                    });
            })
            ->orderByDesc('t.transaction_date')
            ->orderByDesc('t.id')
            ->limit(20)
            ->select(
                't.invoice_no',
                't.transaction_date',
                'tsl.quantity',
                'tsl.unit_price_inc_tax',
                'u.actual_name as unit_name'
            )
            ->get();

        return response()->json($rows->map(function ($r) {
            $date = $r->transaction_date ? Carbon::parse($r->transaction_date)->format('m/d/Y') : '';

            return [
                'invoice_no' => $r->invoice_no,
                'date' => $date,
                'quantity' => (float) $r->quantity,
                'unit_price' => (float) $r->unit_price_inc_tax,
                'unit_name' => $r->unit_name ?: 'Pc',
            ];
        })->values());
    }

    /**
     * Category → subcategory tree for catalog / filters.
     */
    public function categoriesTree(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $tree = Category::catAndSubCategories($business_id, false);

        return response()->json(collect($tree)->map(function ($cat) {
            return [
                'id' => (int) $cat['id'],
                'name' => $cat['name'],
                'sub_categories' => collect($cat['sub_categories'] ?? [])->map(function ($sub) {
                    return [
                        'id' => (int) $sub['id'],
                        'name' => $sub['name'],
                    ];
                })->values(),
            ];
        })->values());
    }

    /**
     * Browse all products with category filters (Sales App Products tab).
     */
    public function products(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $locations = BusinessLocation::forDropdown($business_id, false);
        $locationIds = array_map('strval', array_keys($locations->toArray()));
        $default_location = $request->session()->get('user.default_location_id');
        if (empty($default_location) || ! in_array((string) $default_location, $locationIds, true)) {
            $default_location = count($locations) ? array_key_first($locations->toArray()) : null;
        }
        $categories = Category::catAndSubCategories($business_id, false);
        $categoriesJson = collect($categories)->map(function ($c) {
            return [
                'id' => (int) $c['id'],
                'name' => $c['name'],
                'sub_categories' => collect($c['sub_categories'] ?? [])->map(function ($s) {
                    return [
                        'id' => (int) $s['id'],
                        'name' => $s['name'],
                    ];
                })->values(),
            ];
        })->values();

        return view('sale.products.index', compact('locations', 'default_location', 'categories', 'categoriesJson'));
    }

    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user_id = auth()->id();

        $data = $request->validate([
            'contact_id' => 'required|integer',
            'location_id' => 'required|integer',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer',
            'products.*.variation_id' => 'required|integer',
            'products.*.quantity' => 'required|numeric|min:0.0001',
            'products.*.unit_price' => 'required|numeric|min:0',
            'products.*.sub_unit_id' => 'nullable|integer',
            'payment_type' => 'required|in:cash,due',
            'sale_note' => 'nullable|string|max:500',
            'shipping_details' => 'required|string|max:1000',
            'shipping_address' => 'required|string|max:1000',
            'shipping_status' => 'nullable|in:ordered,packed,shipped,delivered,cancelled',
            'shipping_method' => 'nullable|string|max:100',
            'order_mode' => 'nullable|in:new_order,estimate,back_order',
        ]);

        $orderMode = $data['order_mode'] ?? 'new_order';

        // Verify customer belongs to business
        $contact = Contact::where('business_id', $business_id)
            ->onlyCustomers()
            ->findOrFail($data['contact_id']);

        // Verify location access
        $locations = BusinessLocation::forDropdown($business_id, false)->toArray();
        if (! array_key_exists($data['location_id'], $locations)) {
            return back()->withInput()->with('status', ['success' => 0, 'msg' => 'Invalid location.']);
        }

        try {
            $transaction = DB::transaction(function () use ($request, $data, $business_id, $user_id, $contact, $orderMode) {
                $products = [];
                foreach ($data['products'] as $line) {
                    $products[] = $this->saleAppSellLineFromRequest($business_id, $line);
                }

                $invoice_total = $this->productUtil->calculateInvoiceTotal($products, null, null, false);
                if ($invoice_total === false) {
                    throw new \RuntimeException('Could not calculate total.');
                }

                $shipping_charges = 0;
                $final_total = (float) $invoice_total['final_total'] + $shipping_charges;
                $preferPay = $this->saleAppPreferPayment($data['payment_type'] ?? 'due');

                $cg = $this->contactUtil->getCustomerGroup($business_id, $contact->id);

                // Existing project: Estimate = Quotation; New/Back Order = Sales Order
                if ($orderMode === 'estimate') {
                    $input = [
                        'type' => 'sell',
                        'status' => 'draft',
                        'is_quotation' => 1,
                        'sub_status' => 'quotation',
                        'contact_id' => $contact->id,
                        'customer_group_id' => (empty($cg) || empty($cg->id)) ? null : $cg->id,
                        'transaction_date' => Carbon::now(),
                        'location_id' => $data['location_id'],
                        'final_total' => $final_total,
                        'discount_type' => 'fixed',
                        'discount_amount' => 0,
                        'tax_rate_id' => null,
                        'sale_note' => $data['sale_note'] ?? null,
                        'is_direct_sale' => 1,
                        'source' => 'sale_app',
                        'commission_agent' => null,
                        'exchange_rate' => 1,
                        'products' => $products,
                        'shipping_details' => $data['shipping_details'] ?? null,
                        'shipping_address' => $data['shipping_address'],
                        'shipping_status' => null,
                        'shipping_method' => $data['shipping_method'] ?? null,
                        'shipping_charges' => 0,
                        'prefer_payment_method' => $preferPay,
                    ];
                } else {
                    $input = [
                        'type' => 'sales_order',
                        'status' => 'ordered',
                        'contact_id' => $contact->id,
                        'customer_group_id' => (empty($cg) || empty($cg->id)) ? null : $cg->id,
                        'transaction_date' => Carbon::now(),
                        'location_id' => $data['location_id'],
                        'final_total' => $final_total,
                        'discount_type' => 'fixed',
                        'discount_amount' => 0,
                        'tax_rate_id' => null,
                        'sale_note' => $data['sale_note'] ?? null,
                        'staff_note' => $orderMode === 'back_order' ? 'back_order' : null,
                        'is_direct_sale' => 1,
                        'source' => 'sale_app',
                        'commission_agent' => null,
                        'exchange_rate' => 1,
                        'products' => $products,
                        'shipping_details' => $data['shipping_details'] ?? null,
                        'shipping_address' => $data['shipping_address'],
                        'shipping_status' => $data['shipping_status'] ?? 'ordered',
                        'shipping_method' => $data['shipping_method'] ?? null,
                        'shipping_charges' => 0,
                        'prefer_payment_method' => $preferPay,
                    ];
                }

                $cmsn = $request->session()->get('business.sales_cmsn_agnt');
                if ($cmsn === 'logged_in_user') {
                    $input['commission_agent'] = $user_id;
                }

                $transaction = $this->transactionUtil->createSellTransaction(
                    $business_id,
                    $input,
                    array_merge($invoice_total, ['final_total' => $final_total]),
                    $user_id,
                    false
                );

                $this->transactionUtil->createOrUpdateSellLines(
                    $transaction,
                    $products,
                    $data['location_id'],
                    false,
                    null,
                    [],
                    false
                );

                $transaction->payment_status = ($data['payment_type'] ?? 'due') === 'due' ? 'due' : ($transaction->payment_status ?: 'due');
                $transaction->prefer_payment_method = $preferPay;
                $transaction->save();

                // Sales order / quotation: no stock decrease until invoiced

                return $transaction;
            });

            $msg = $orderMode === 'estimate'
                ? 'Estimate created: '.$transaction->invoice_no
                : ($orderMode === 'back_order'
                    ? 'Back order created: '.$transaction->invoice_no
                    : 'Sales order created: '.$transaction->invoice_no);

            $request->session()->forget($this->saleAppDraftSessionKey());
            $this->saleAppRemoveSavedForContact($request->input('contact_id'));

            return redirect()
                ->route('sale.orders')
                ->with('status', [
                    'success' => 1,
                    'msg' => $msg,
                ]);
        } catch (\Throwable $e) {
            \Log::error('Sale portal create failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()->withInput()->with('status', [
                'success' => 0,
                'msg' => 'Could not create order. '.$e->getMessage(),
            ]);
        }
    }

    public function edit(Request $request, $id)
    {
        $order = $this->findOwnOrder($id);
        $order->load(['contact', 'sell_lines.product.unit', 'sell_lines.variations']);

        if (! $this->saleAppCanEdit($order)) {
            return redirect()
                ->route('sale.orders')
                ->with('status', ['success' => 0, 'msg' => 'This order is invoiced and cannot be edited.']);
        }

        $business_id = $request->session()->get('user.business_id');
        $locations = BusinessLocation::forDropdown($business_id, false);
        $default_location = $order->location_id;

        $c = $order->contact;
        $default_customer = $c ? [
            'id' => $c->id,
            'text' => trim(($c->supplier_business_name ? $c->supplier_business_name.' — ' : '').$c->name.
                ($c->mobile ? ' ('.$c->mobile.')' : '')),
            'shipping_address' => $order->shipping_address ?: $c->shipping_address,
            'credit_limit' => $c->credit_limit,
            'acct' => $c->contact_id,
            'display_name' => trim((string) ($c->supplier_business_name ?: $c->name)),
            'address' => $order->shipping_address ?: ($c->shipping_address ?: ''),
        ] : null;

        $edit_lines = $order->sell_lines
            ->whereNull('parent_sell_line_id')
            ->values()
            ->map(function ($line) use ($business_id) {
                $product = $line->product;
                $name = optional($product)->name ?: 'Item';
                $vname = optional($line->variations)->name;
                if (! empty($vname) && $vname !== 'DUMMY') {
                    $name .= ' - '.$vname;
                }
                $unitId = (int) optional($product)->unit_id;
                $units = $product ? $this->saleAppUnits((int) $business_id, (int) $product->id, $unitId) : [];
                $subId = (int) ($line->sub_unit_id ?: $unitId);
                $mult = $this->saleAppUnitMultiplier($units, $subId, $unitId);
                $soldBase = (float) $line->unit_price_inc_tax;
                $listBase = (float) ($line->unit_price_before_discount ?: $soldBase);
                if ($listBase <= 0) {
                    $listBase = $soldBase;
                }
                $unitName = optional(optional($product)->unit)->actual_name ?: 'Pc';
                foreach ($units as $u) {
                    if ((int) $u['id'] === $subId) {
                        $unitName = $u['name'];
                        break;
                    }
                }

                return [
                    'product_id' => (int) $line->product_id,
                    'variation_id' => (int) $line->variation_id,
                    'transaction_sell_lines_id' => (int) $line->id,
                    'name' => $name,
                    'price' => $soldBase * $mult,
                    'base_price' => $soldBase,
                    'catalog_base_price' => $listBase,
                    'quantity' => $mult > 0 ? ((float) $line->quantity / $mult) : (float) $line->quantity,
                    'enable_stock' => (int) optional($product)->enable_stock,
                    'product_type' => optional($product)->type,
                    'unit_id' => $unitId,
                    'unit_name' => $unitName,
                    'sub_unit_id' => $subId,
                    'allow_decimal' => (int) optional(optional($product)->unit)->allow_decimal,
                    'units' => $units,
                ];
            });

        $edit_order = $order;

        return view('sale.orders.create', compact(
            'locations',
            'default_location',
            'default_customer',
            'edit_order',
            'edit_lines'
        ));
    }

    public function update(Request $request, $id)
    {
        $order = $this->findOwnOrder($id);

        if (! $this->saleAppCanEdit($order)) {
            return redirect()
                ->route('sale.orders')
                ->with('status', ['success' => 0, 'msg' => 'This order is invoiced and cannot be edited.']);
        }

        $business_id = $request->session()->get('user.business_id');
        $user_id = auth()->id();

        $data = $request->validate([
            'contact_id' => 'required|integer',
            'location_id' => 'required|integer',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer',
            'products.*.variation_id' => 'required|integer',
            'products.*.quantity' => 'required|numeric|min:0.0001',
            'products.*.unit_price' => 'required|numeric|min:0',
            'products.*.sub_unit_id' => 'nullable|integer',
            'products.*.transaction_sell_lines_id' => 'nullable|integer',
            'payment_type' => 'required|in:cash,due',
            'sale_note' => 'nullable|string|max:500',
            'shipping_details' => 'required|string|max:1000',
            'shipping_address' => 'required|string|max:1000',
            'shipping_status' => 'nullable|in:ordered,packed,shipped,delivered,cancelled',
        ]);

        $contact = Contact::where('business_id', $business_id)
            ->onlyCustomers()
            ->findOrFail($data['contact_id']);

        $locations = BusinessLocation::forDropdown($business_id, false)->toArray();
        if (! array_key_exists($data['location_id'], $locations)) {
            return back()->withInput()->with('status', ['success' => 0, 'msg' => 'Invalid location.']);
        }

        try {
            DB::transaction(function () use ($data, $business_id, $user_id, $contact, $order) {
                $products = [];
                foreach ($data['products'] as $line) {
                    $products[] = $this->saleAppSellLineFromRequest($business_id, $line);
                }

                $invoice_total = $this->productUtil->calculateInvoiceTotal($products, null, null, false);
                if ($invoice_total === false) {
                    throw new \RuntimeException('Could not calculate total.');
                }

                $final_total = (float) $invoice_total['final_total'];
                $cg = $this->contactUtil->getCustomerGroup($business_id, $contact->id);

                $input = [
                    'status' => $order->status ?: 'ordered',
                    'contact_id' => $contact->id,
                    'customer_group_id' => (empty($cg) || empty($cg->id)) ? null : $cg->id,
                    'final_total' => $final_total,
                    'discount_type' => $order->discount_type ?: 'fixed',
                    'discount_amount' => $order->discount_amount ?: 0,
                    'tax_rate_id' => $order->tax_id,
                    'sale_note' => $data['sale_note'] ?? null,
                    'commission_agent' => $order->commission_agent,
                    'exchange_rate' => $order->exchange_rate ?: 1,
                    'shipping_details' => $data['shipping_details'] ?? null,
                    'shipping_address' => $data['shipping_address'],
                    'shipping_status' => $data['shipping_status'] ?? ($order->shipping_status ?: 'ordered'),
                    'shipping_charges' => $order->shipping_charges ?: 0,
                    'source' => $order->source ?: 'sale_app',
                    'prefer_payment_method' => $this->saleAppPreferPayment($data['payment_type'] ?? 'due'),
                ];

                $this->transactionUtil->updateSellTransaction(
                    $order,
                    $business_id,
                    $input,
                    array_merge($invoice_total, ['final_total' => $final_total]),
                    $user_id,
                    false,
                    false
                );

                $lines_before_edit = $order->sell_lines()->whereNull('parent_sell_line_id')->with('product')->get();
                $deleted_lines = $this->transactionUtil->createOrUpdateSellLines(
                    $order->fresh(),
                    $products,
                    $data['location_id'],
                    true,
                    'draft',
                    [],
                    false
                );
                $deleted_ids = collect($deleted_lines ?? [])->map(function ($row) {
                    if (is_array($row)) {
                        return (int) ($row['id'] ?? 0);
                    }

                    return (int) (is_object($row) ? ($row->id ?? 0) : $row);
                })->filter()->values()->all();
                $removed_items = [];
                foreach ($lines_before_edit as $old_line) {
                    if (in_array((int) $old_line->id, $deleted_ids, true)) {
                        $removed_items[] = $old_line->product->name ?? ('Item #'.$old_line->product_id);
                    }
                }

                $fresh = $order->fresh();
                $fresh->prefer_payment_method = $this->saleAppPreferPayment($data['payment_type'] ?? 'due');
                if (($data['payment_type'] ?? 'due') === 'due') {
                    $fresh->payment_status = 'due';
                }
                $fresh->save();
                $this->transactionUtil->activityLog($fresh, 'edited', $order, [
                    'invoice_no' => $fresh->invoice_no,
                    'doc_type' => 'sales_order',
                    'edited_by' => trim((auth()->user()->first_name ?? '').' '.(auth()->user()->last_name ?? '')) ?: (auth()->user()->username ?? 'Staff'),
                    'edited_by_id' => auth()->id(),
                    'removed_items' => array_values(array_unique($removed_items)),
                    'removed_count' => count($removed_items),
                    'lines' => (function () use ($removed_items, $fresh) {
                        $edit_lines = [];
                        foreach (array_values(array_unique($removed_items)) as $pname) {
                            $edit_lines[] = ['product' => $pname, 'change' => 'removed'];
                        }
                        if (empty($edit_lines)) {
                            foreach ($fresh->sell_lines()->whereNull('parent_sell_line_id')->with('product')->get() as $kl) {
                                $edit_lines[] = ['product' => $kl->product->name ?? 'Item', 'change' => 'edited'];
                            }
                        }
                        if (empty($edit_lines)) {
                            $edit_lines[] = ['product' => '—', 'change' => 'edited'];
                        }

                        return $edit_lines;
                    })(),
                ]);
            });

            $request->session()->forget($this->saleAppDraftSessionKey());
            $this->saleAppRemoveSavedForContact($request->input('contact_id'));

            return redirect()
                ->route('sale.orders')
                ->with('status', [
                    'success' => 1,
                    'msg' => 'Sales order updated: '.$order->invoice_no,
                ]);
        } catch (\Throwable $e) {
            \Log::error('Sale portal update failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()->withInput()->with('status', [
                'success' => 0,
                'msg' => 'Could not update order. '.$e->getMessage(),
            ]);
        }
    }

    public function account(Request $request)
    {
        $user = auth()->user();
        $business_id = $request->session()->get('user.business_id');
        $locations = BusinessLocation::forDropdown($business_id, false);
        $locationIds = array_map('strval', array_keys($locations->toArray()));

        $current_location_id = $request->session()->get('user.default_location_id');
        if (empty($current_location_id) || ! in_array((string) $current_location_id, $locationIds, true)) {
            $current_location_id = count($locations) ? array_key_first($locations->toArray()) : null;
            if ($current_location_id) {
                $request->session()->put('user.default_location_id', $current_location_id);
            }
        }

        return view('sale.account', compact('user', 'locations', 'current_location_id'));
    }

    /**
     * Expense list inside Sales app shell (uses existing expense data / permissions).
     */
    public function expenses(Request $request)
    {
        if (! auth()->user()->can('view_own_expense') && ! auth()->user()->can('all_expense.access')) {
            if (auth()->user()->can('expense.add')) {
                return redirect()->route('sale.expenses.create');
            }
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $user_id = auth()->id();

        $query = Transaction::where('business_id', $business_id)
            ->whereIn('type', ['expense', 'expense_refund'])
            ->with(['expense_category:id,name', 'location:id,name', 'contact:id,name,supplier_business_name'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        if (! auth()->user()->can('all_expense.access')) {
            $query->where(function ($q) use ($user_id) {
                $q->where('created_by', $user_id)->orWhere('expense_for', $user_id);
            });
        }

        $expenses = $query->paginate(20)->withQueryString();
        $canAdd = auth()->user()->can('expense.add');

        return view('sale.expenses.index', compact('expenses', 'canAdd'));
    }

    /**
     * Add expense form inside Sales app — same fields as admin expense (TransactionUtil::createExpense).
     */
    public function createExpense(Request $request)
    {
        if (! auth()->user()->can('expense.add')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $locations = BusinessLocation::forDropdown($business_id, false);
        $categories = \App\ExpenseCategory::where('business_id', $business_id)
            ->whereNull('parent_id')
            ->pluck('name', 'id');

        $subCategories = \App\ExpenseCategory::where('business_id', $business_id)
            ->whereNotNull('parent_id')
            ->get(['id', 'name', 'parent_id'])
            ->groupBy('parent_id')
            ->map(function ($rows) {
                return $rows->map(fn ($r) => ['id' => $r->id, 'name' => $r->name])->values();
            });

        $payment_types_by_location = [];
        $locationModels = BusinessLocation::where('business_id', $business_id)
            ->get(['id', 'business_id', 'default_payment_accounts']);
        foreach ($locationModels as $loc) {
            $payment_types_by_location[(string) $loc->id] = $this->transactionUtil->payment_types($loc, false, $business_id);
        }

        $current_location_id = $request->session()->get('user.default_location_id');
        $locationIds = array_map('strval', array_keys($locations->toArray()));
        if (empty($current_location_id) || ! in_array((string) $current_location_id, $locationIds, true)) {
            $current_location_id = count($locations) ? array_key_first($locations->toArray()) : null;
        }

        // Only location-enabled (active) payment methods — not the full list
        $payment_types = $payment_types_by_location[(string) $current_location_id]
            ?? ($current_location_id ? $this->transactionUtil->payment_types($current_location_id, false, $business_id) : []);

        $selected_contact = null;
        $oldContactId = old('contact_id');
        if (! empty($oldContactId)) {
            $c = Contact::where('business_id', $business_id)->onlyCustomers()->find($oldContactId);
            if ($c) {
                $selected_contact = [
                    'id' => $c->id,
                    'text' => trim(($c->supplier_business_name ? $c->supplier_business_name.' — ' : '').$c->name.
                        ($c->mobile ? ' ('.$c->mobile.')' : '')),
                ];
            }
        }

        return view('sale.expenses.create', compact(
            'locations',
            'categories',
            'subCategories',
            'payment_types',
            'payment_types_by_location',
            'current_location_id',
            'selected_contact'
        ));
    }

    public function storeExpense(Request $request)
    {
        if (! auth()->user()->can('expense.add')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $request->validate([
            'location_id' => 'required|integer',
            'final_total' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'expense_category_id' => 'nullable|integer',
            'expense_sub_category_id' => 'nullable|integer',
            'contact_id' => 'nullable|integer',
            'additional_notes' => 'nullable|string|max:1000',
            'ref_no' => 'nullable|string|max:191',
            'payment_method' => 'nullable|string|max:50',
            'document' => 'nullable|file|max:'.(config('constants.document_size_limit') / 1000),
        ]);

        $locations = BusinessLocation::forDropdown($business_id, false)->toArray();
        $locationId = (int) $request->input('location_id');
        if (! isset($locations[$locationId]) && ! isset($locations[(string) $locationId])) {
            return back()->withInput()->with('status', ['success' => 0, 'msg' => 'Invalid location.']);
        }

        if (! empty($request->input('contact_id'))) {
            Contact::where('business_id', $business_id)
                ->onlyCustomers()
                ->findOrFail((int) $request->input('contact_id'));
        }

        try {
            $locationId = (int) $request->input('location_id');
            $method = $request->input('payment_method') ?: 'cash';
            // Only allow payment methods enabled for this location
            $paymentTypes = $this->transactionUtil->payment_types($locationId, false, $business_id);
            if (! array_key_exists($method, $paymentTypes)) {
                $method = array_key_first($paymentTypes) ?: 'cash';
            }

            // Same engine as admin ExpenseController@store
            $request->merge([
                'expense_for' => auth()->id(),
                'payment' => [[
                    'amount' => $request->input('final_total'),
                    'method' => $method,
                    'paid_on' => $request->input('transaction_date').' '.now()->format('H:i:s'),
                ]],
            ]);

            DB::beginTransaction();
            $expense = $this->transactionUtil->createExpense($request, $business_id, auth()->id(), false);
            $this->transactionUtil->activityLog($expense, 'added');
            DB::commit();

            return redirect()->route('sale.expenses')->with('status', [
                'success' => 1,
                'msg' => __('expense.expense_add_success'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Sale expense create failed: '.$e->getMessage());

            return back()->withInput()->with('status', [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
    }

    /**
     * Delivery: only this sales representative's own sales orders (created_by).
     */
    public function delivery(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user_id = auth()->id();
        $q = trim((string) $request->get('q', ''));

        $start = $request->get('start_date', Carbon::now()->subDays(30)->toDateString());
        $end = $request->get('end_date', Carbon::now()->toDateString());

        try {
            $startAt = Carbon::parse($start)->startOfDay();
            $endAt = Carbon::parse($end)->endOfDay();
        } catch (\Throwable $e) {
            $startAt = Carbon::now()->subDays(30)->startOfDay();
            $endAt = Carbon::now()->endOfDay();
            $start = $startAt->toDateString();
            $end = $endAt->toDateString();
        }

        $locationId = $request->session()->get('user.default_location_id');
        $origin = BusinessLocation::where('business_id', $business_id)
            ->when($locationId, fn ($q) => $q->where('id', $locationId))
            ->first(['name', 'city', 'zip_code']);
        $originZip = (int) preg_replace('/\D+/', '', (string) optional($origin)->zip_code);
        $originCity = trim((string) optional($origin)->city);
        $originName = trim((string) optional($origin)->name);

        $rows = Transaction::where('business_id', $business_id)
            ->where('type', 'sales_order')
            ->where('created_by', $user_id)
            ->whereBetween('transaction_date', [$startAt, $endAt])
            ->with(['contact:id,name,mobile,supplier_business_name,city,state,zip_code,address_line_1,shipping_address'])
            ->when($q !== '', function ($query) use ($q) {
                $term = '%'.$q.'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('invoice_no', 'like', $term)
                        ->orWhere('shipping_address', 'like', $term)
                        ->orWhereHas('contact', function ($c) use ($term) {
                            $c->where('name', 'like', $term)
                                ->orWhere('mobile', 'like', $term)
                                ->orWhere('supplier_business_name', 'like', $term)
                                ->orWhere('city', 'like', $term);
                        });
                });
            })
            ->orderBy('transaction_date')
            ->limit(200)
            ->get();

        $zipNum = function ($order) {
            $z = preg_replace('/\D+/', '', (string) ($order->contact->zip_code ?? ''));

            return (int) $z;
        };
        $street = function ($order) {
            return strtolower(trim((string) (
                $order->shipping_address
                ?: ($order->contact->address_line_1 ?? '')
            )));
        };

        // One route from the POS location: same city first, then closer zip, then street.
        $distanceFromPos = function ($order) use ($originZip, $originCity, $zipNum, $street) {
            $zip = $zipNum($order);
            $zipDist = $originZip > 0 && $zip > 0 ? abs($zip - $originZip) : ($zip ?: 999999);
            $city = trim((string) ($order->contact->city ?? ''));
            $sameCity = ($originCity !== '' && strcasecmp($city, $originCity) === 0) ? 0 : 1;

            return sprintf('%d-%08d-%s', $sameCity, $zipDist, $street($order));
        };

        $sorted = $rows->sortBy($distanceFromPos)->values();
        $routeLabel = $originName !== '' ? $originName : ($originCity !== '' ? $originCity : 'POS');
        $routes = $sorted->isEmpty()
            ? collect()
            : collect([$routeLabel => $sorted]);

        return view('sale.delivery', compact('routes', 'start', 'end', 'q', 'originCity', 'originName'));
    }

    /**
     * Change default business location for Sales App (used on Create order).
     */
    public function updateLocation(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $data = $request->validate([
            'location_id' => 'required|integer',
        ]);

        $locations = BusinessLocation::forDropdown($business_id, false)->toArray();
        $locationId = (int) $data['location_id'];
        if (! isset($locations[$locationId]) && ! isset($locations[(string) $locationId])) {
            return back()->with('status', ['success' => 0, 'msg' => 'Invalid location.']);
        }

        $request->session()->put('user.default_location_id', $locationId);
        $label = $locations[$locationId] ?? $locations[(string) $locationId];

        return back()->with('status', [
            'success' => 1,
            'msg' => 'Location updated: '.$label,
        ]);
    }

    /**
     * Customers menu always visible in app; access checked on open.
     */
    public static function userCanAccessCustomers(): bool
    {
        return true;
    }

    /** View all or own customers (list permission). */
    public static function userCanListCustomers(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->can('customer.view') || $user->can('customer.view_own');
    }

    public static function userCanCreateCustomers(): bool
    {
        return auth()->check() && auth()->user()->can('customer.create');
    }

    public function customers(Request $request)
    {
        $canList = self::userCanListCustomers();
        $canCreate = self::userCanCreateCustomers();

        // No list / create → still open Customers screen with no-permission message
        if (! $canList && ! $canCreate) {
            return view('sale.customers.no_permission');
        }

        // Create-only: form (no list data)
        if (! $canList && $canCreate) {
            return redirect()->route('sale.customers.create');
        }

        $business_id = $request->session()->get('user.business_id');
        $term = trim((string) $request->get('q', ''));

        $query = Contact::where('business_id', $business_id)
            ->onlyCustomers()
            ->where('contact_status', 'active')
            ->select(
                'contacts.id',
                'contacts.name',
                'contacts.mobile',
                'contacts.email',
                'contacts.supplier_business_name',
                'contacts.contact_id',
                'contacts.address_line_1',
                'contacts.city',
                'contacts.shipping_address',
                'contacts.credit_limit',
                'contacts.created_at'
            );

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('contacts.name', 'like', "%{$term}%")
                    ->orWhere('contacts.mobile', 'like', "%{$term}%")
                    ->orWhere('contacts.email', 'like', "%{$term}%")
                    ->orWhere('contacts.supplier_business_name', 'like', "%{$term}%")
                    ->orWhere('contacts.contact_id', 'like', "%{$term}%");
            });
        }

        $customers = $query->orderBy('contacts.name')
            ->distinct()
            ->paginate(20)
            ->appends(['q' => $term]);

        $pageIds = $customers->getCollection()->pluck('id')->filter()->all();
        $lastOrderTotals = [];
        if (! empty($pageIds)) {
            $lastIds = Transaction::query()
                ->where('business_id', $business_id)
                ->whereIn('contact_id', $pageIds)
                ->where(function ($q) {
                    $q->where('type', 'sales_order')
                        ->orWhere(function ($inner) {
                            $inner->where('type', 'sell')->where('status', 'final');
                        });
                })
                ->select('contact_id', DB::raw('MAX(id) as last_id'))
                ->groupBy('contact_id')
                ->pluck('last_id', 'contact_id');

            if ($lastIds->isNotEmpty()) {
                $lastOrderTotals = Transaction::query()
                    ->whereIn('id', $lastIds->values()->all())
                    ->pluck('final_total', 'contact_id')
                    ->map(fn ($v) => (float) $v)
                    ->all();
            }
        }

        $customers->getCollection()->transform(function ($c) use ($lastOrderTotals) {
            $c->last_order_total = $lastOrderTotals[$c->id] ?? 0;

            return $c;
        });

        $zeroSaleReasons = $this->saleAppZeroSaleReasons();

        return view('sale.customers.index', compact('customers', 'term', 'canCreate', 'canList', 'zeroSaleReasons'));
    }

    public function createCustomer()
    {
        if (! self::userCanCreateCustomers()) {
            return view('sale.customers.no_permission', ['action' => 'create']);
        }

        $canList = self::userCanListCustomers();
        $business_id = session('user.business_id');
        $customer_groups = CustomerGroup::forDropdown($business_id);

        return view('sale.customers.create', compact('canList', 'customer_groups'));
    }

    public function storeCustomer(Request $request)
    {
        if (! self::userCanCreateCustomers()) {
            return view('sale.customers.no_permission', ['action' => 'create']);
        }

        $data = $request->validate([
            'contact_type_radio' => 'nullable|in:individual,business',
            'first_name' => 'required|string|max:191',
            'last_name' => 'nullable|string|max:191',
            'supplier_business_name' => 'nullable|string|max:191',
            'contact_id' => 'nullable|string|max:191',
            'customer_group_id' => 'nullable|integer',
            'mobile' => 'nullable|string|max:50',
            'alternate_number' => 'nullable|string|max:50',
            'landline' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:191',
            'tax_number' => 'nullable|string|max:191',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:191',
            'state' => 'nullable|string|max:191',
            'zip_code' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:191',
            'shipping_address' => 'nullable|string|max:1000',
            'credit_limit' => 'nullable|numeric',
            'allow_customer_app' => 'nullable|in:0,1',
            'customer_app_password' => 'nullable|string|max:191',
        ]);

        $business_id = $request->session()->get('user.business_id');
        $user_id = auth()->id();

        try {
            $first = trim($data['first_name']);
            $last = trim((string) ($data['last_name'] ?? ''));
            $name = trim($first.' '.$last);
            $allowCustomerApp = (int) ($data['allow_customer_app'] ?? 0) === 1;
            $input = [
                'type' => 'customer',
                'contact_type' => $data['contact_type_radio'] ?? 'individual',
                'business_id' => $business_id,
                'created_by' => $user_id,
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last !== '' ? $last : null,
                'mobile' => $data['mobile'] ?? null,
                'alternate_number' => $data['alternate_number'] ?? null,
                'landline' => $data['landline'] ?? null,
                'email' => $data['email'] ?? null,
                'tax_number' => $data['tax_number'] ?? null,
                'address_line_1' => $data['address_line_1'] ?? null,
                'address_line_2' => $data['address_line_2'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'zip_code' => $data['zip_code'] ?? null,
                'country' => $data['country'] ?? null,
                'shipping_address' => $data['shipping_address'] ?? null,
                'supplier_business_name' => $data['supplier_business_name'] ?? null,
                'customer_group_id' => $data['customer_group_id'] ?: null,
                'contact_id' => $data['contact_id'] ?: null,
                'credit_limit' => $data['credit_limit'] !== null && $data['credit_limit'] !== '' ? $data['credit_limit'] : null,
                'contact_status' => 'active',
                'allow_customer_app' => $allowCustomerApp ? 1 : 0,
            ];
            if ($allowCustomerApp) {
                $appPass = trim((string) ($data['customer_app_password'] ?? ''));
                if ($appPass === '') {
                    $appPass = (string) ($input['mobile'] ?? '');
                }
                if ($appPass !== '') {
                    $input['password'] = bcrypt($appPass);
                }
            }

            $output = $this->contactUtil->createNewContact($input);

            $redirect = self::userCanListCustomers()
                ? route('sale.customers')
                : route('sale.customers.create');

            return redirect()
                ->to($redirect)
                ->with('status', ['success' => 1, 'msg' => 'Customer added: '.$output['data']->name]);
        } catch (\Throwable $e) {
            \Log::error('Sale portal customer create failed: '.$e->getMessage());

            return back()->withInput()->with('status', [
                'success' => 0,
                'msg' => 'Could not add customer. '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Previous-period totals used as live Sales Goals targets.
     */
    protected function saleAppGoalTotals($businessId, $userId, $start, $end, array $invoiceMap, array $invoiceTotals): array
    {
        $rows = Transaction::where('business_id', $businessId)
            ->where('created_by', $userId)
            ->where('type', 'sales_order')
            ->whereBetween('transaction_date', [$start, $end])
            ->get(['id', 'final_total', 'contact_id', 'prefer_payment_method']);

        $invoices = 0.0;
        foreach ($rows as $row) {
            foreach ($invoiceMap[(int) $row->id] ?? [] as $inv) {
                $invoices += (float) ($invoiceTotals[(int) $inv->id] ?? 0);
            }
        }

        return [
            'orders' => (float) $rows->sum('final_total'),
            'invoices' => $invoices,
            'payments' => (float) $rows->filter(fn ($o) => ! $this->saleAppIsCreditOrder($o))->sum('final_total'),
            'visits' => $rows->pluck('contact_id')->filter()->unique()->count(),
        ];
    }

    /**
     * Invoices created from sales orders (Sale App display only).
     */
    protected function saleAppInvoiceMap(int $businessId): array
    {
        return Transaction::invoicesBySalesOrderId($businessId);
    }

    /**
     * @return array<int, float>
     */
    protected function saleAppInvoiceTotals(array $invoiceMap): array
    {
        $ids = [];
        foreach ($invoiceMap as $invoices) {
            foreach ($invoices as $inv) {
                $ids[] = (int) $inv->id;
            }
        }
        $ids = array_values(array_unique(array_filter($ids)));
        if ($ids === []) {
            return [];
        }

        return Transaction::whereIn('id', $ids)->pluck('final_total', 'id')->all();
    }

    protected function saleAppDisplayTotal(Transaction $order, array $invoiceMap, array $invoiceTotals): float
    {
        $invoices = $invoiceMap[(int) $order->id] ?? [];
        if ($invoices === []) {
            return (float) $order->final_total;
        }

        $sum = 0.0;
        foreach ($invoices as $inv) {
            $sum += (float) ($invoiceTotals[(int) $inv->id] ?? 0);
        }

        return $sum;
    }

    protected function saleAppSumDisplayTotals($orders, array $invoiceMap, array $invoiceTotals): float
    {
        $sum = 0.0;
        foreach ($orders as $order) {
            $sum += $this->saleAppDisplayTotal($order, $invoiceMap, $invoiceTotals);
        }

        return $sum;
    }

    /**
     * Completed only if this sales order was converted to an invoice.
     */
    protected function saleAppDisplayStatus(Transaction $order, array $invoiceMap): string
    {
        if ((int) $order->is_quotation === 1 || ($order->type === 'sell' && $order->sub_status === 'quotation')) {
            return 'estimate';
        }
        if ($order->type === 'sales_order' && trim((string) $order->staff_note) === 'back_order') {
            $converted = ! empty($invoiceMap[(int) $order->id]);

            return $converted ? 'invoiced' : 'back_order';
        }
        $converted = ! empty($invoiceMap[(int) $order->id]);

        return $converted ? 'invoiced' : 'sale';
    }

    protected function saleAppHasEditPermission(Transaction $order): bool
    {
        if ((int) $order->created_by !== (int) auth()->id()) {
            return false;
        }

        // Estimates (quotations) from sale app: view/delete only for now
        if ((int) $order->is_quotation === 1 || $order->type !== 'sales_order') {
            return false;
        }

        $user = auth()->user();

        return $user && ($user->can('so.update_own') || $user->can('so.update'));
    }

    protected function saleAppCanEdit(Transaction $order, ?array $invoiceMap = null): bool
    {
        if (! $this->saleAppHasEditPermission($order)) {
            return false;
        }

        $map = $invoiceMap ?? $this->saleAppInvoiceMap((int) $order->business_id);

        return empty($map[(int) $order->id]);
    }

    protected function applySaleAppOrderStatus($orders, array $invoiceMap, ?array $invoiceTotals = null): void
    {
        $invoiceTotals = $invoiceTotals ?? $this->saleAppInvoiceTotals($invoiceMap);
        foreach ($orders as $order) {
            $order->sale_status = $this->saleAppDisplayStatus($order, $invoiceMap);
            $order->can_show_edit = $this->saleAppHasEditPermission($order);
            $order->can_edit = $this->saleAppCanEdit($order, $invoiceMap);
            $order->sale_display_total = $this->saleAppDisplayTotal($order, $invoiceMap, $invoiceTotals);
            $invoices = $invoiceMap[(int) $order->id] ?? [];
            $order->converted_invoice_no = ! empty($invoices)
                ? ($invoices[count($invoices) - 1]->invoice_no ?? null)
                : null;
            $order->is_credit_sale = $this->saleAppIsCreditOrder($order);
        }
    }

    protected function saleAppPreferPayment(string $paymentType): string
    {
        return $paymentType === 'due' ? 'credit_sale' : 'cash';
    }

    protected function saleAppIsCreditOrder($order): bool
    {
        return ($order->prefer_payment_method ?? '') === 'credit_sale';
    }

    /**
     * After admin converts (and may change price/qty), Sale App shows the invoice snapshot.
     */
    protected function applySaleAppInvoiceSnapshot(Transaction $order, array $invoiceMap): void
    {
        $this->applySaleAppOrderStatus(collect([$order]), $invoiceMap);

        $stubs = $invoiceMap[(int) $order->id] ?? [];
        if ($stubs === []) {
            return;
        }

        $ids = array_values(array_unique(array_map(fn ($inv) => (int) $inv->id, $stubs)));
        $invoices = Transaction::with(['contact', 'sell_lines.product', 'sell_lines.variations', 'payment_lines'])
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get();
        if ($invoices->isEmpty()) {
            return;
        }

        $lines = $invoices->flatMap(function ($invoice) {
            return $invoice->sell_lines->whereNull('parent_sell_line_id')->values();
        })->values();
        $order->setRelation('sell_lines', $lines);

        $latest = $invoices->last();
        $order->sale_display_total = (float) $invoices->sum('final_total');
        $order->total_before_tax = (float) $invoices->sum('total_before_tax');
        $order->discount_amount = (float) $invoices->sum('discount_amount');
        $order->discount_type = $latest->discount_type;
        $order->tax_amount = (float) $invoices->sum('tax_amount');
        $order->shipping_charges = (float) $invoices->sum('shipping_charges');
        $order->packing_charge = (float) $invoices->sum('packing_charge');
        $order->packing_charge_type = $latest->packing_charge_type;
        $order->round_off_amount = (float) $invoices->sum('round_off_amount');
        $order->additional_notes = $latest->additional_notes;
        $order->shipping_address = $latest->shipping_address;
        $order->shipping_details = $latest->shipping_details;
        $order->shipping_status = $latest->shipping_status;
        $order->converted_invoice_no = $latest->invoice_no;
        for ($i = 1; $i <= 4; $i++) {
            $order->{'additional_expense_key_'.$i} = $latest->{'additional_expense_key_'.$i};
            $order->{'additional_expense_value_'.$i} = (float) $invoices->sum('additional_expense_value_'.$i);
        }
        if ($latest->relationLoaded('contact') && $latest->contact) {
            $order->setRelation('contact', $latest->contact);
        }
        $payments = $invoices->flatMap(function ($invoice) {
            return $invoice->payment_lines ?? collect();
        })->values();
        $order->setRelation('payment_lines', $payments);
    }

    /**
     * Subtotal, discount, tax, shipping, extras, total (Sale App display).
     */
    protected function saleAppAmountBreakdown(Transaction $order): array
    {
        $soldSubtotal = 0.0;
        $listSubtotal = 0.0;
        $lineDiscountTotal = 0.0;
        foreach ($order->sell_lines ?? [] as $line) {
            if (! empty($line->parent_sell_line_id)) {
                continue;
            }
            $qty = (float) $line->quantity;
            $sold = (float) $line->unit_price_inc_tax;
            $list = (float) ($line->unit_price_before_discount ?: $sold);
            if ($list < $sold) {
                $list = $sold;
            }
            $soldSubtotal += $qty * $sold;
            $listSubtotal += $qty * $list;
            if ($list > $sold) {
                $lineDiscountTotal += $qty * ($list - $sold);
            }
        }
        $subtotal = $listSubtotal > 0 ? $listSubtotal : $soldSubtotal;
        if ($subtotal <= 0 && (float) ($order->total_before_tax ?? 0) > 0) {
            $subtotal = (float) $order->total_before_tax;
        }

        $discountRaw = (float) ($order->discount_amount ?? 0);
        $discountType = $order->discount_type ?: 'fixed';
        $orderDiscountMoney = $discountType === 'percentage'
            ? round($soldSubtotal * $discountRaw / 100, 2)
            : $discountRaw;
        $discountMoney = round($orderDiscountMoney + $lineDiscountTotal, 2);
        $discountLabel = 'Discount';
        if ($lineDiscountTotal > 0.009 && $orderDiscountMoney <= 0.009) {
            $discountLabel = 'Item discount';
        } elseif ($discountType === 'percentage' && $discountRaw > 0) {
            $discountLabel = 'Discount ('.rtrim(rtrim(number_format($discountRaw, 2), '0'), '.').'%)';
        }

        $packRaw = (float) ($order->packing_charge ?? 0);
        $packType = $order->packing_charge_type;
        $packMoney = $packType === 'percent'
            ? round($subtotal * $packRaw / 100, 2)
            : $packRaw;

        $extras = [];
        for ($i = 1; $i <= 4; $i++) {
            $key = trim((string) ($order->{'additional_expense_key_'.$i} ?? ''));
            $val = (float) ($order->{'additional_expense_value_'.$i} ?? 0);
            if ($key !== '' || $val != 0.0) {
                $extras[] = [
                    'label' => $key !== '' ? $key : ('Extra charge '.$i),
                    'amount' => $val,
                ];
            }
        }

        $total = (float) ($order->sale_display_total ?? $order->final_total ?? 0);
        $paid = 0.0;
        foreach ($order->payment_lines ?? [] as $pay) {
            $amt = (float) ($pay->amount ?? 0);
            $paid += ! empty($pay->is_return) ? -$amt : $amt;
        }

        return [
            'subtotal' => $subtotal,
            'discount' => $discountMoney,
            'line_discount' => round($lineDiscountTotal, 2),
            'discount_label' => $discountLabel,
            'tax' => (float) ($order->tax_amount ?? 0),
            'shipping' => (float) ($order->shipping_charges ?? 0),
            'packing' => $packMoney,
            'packing_label' => $packType === 'percent' && $packRaw > 0
                ? 'Packing ('.rtrim(rtrim(number_format($packRaw, 2), '0'), '.').'%)'
                : 'Packing charge',
            'round_off' => (float) ($order->round_off_amount ?? 0),
            'extras' => $extras,
            'total' => $total,
            'paid' => $paid,
            'due' => round($total - $paid, 2),
            'show_paid' => ($order->sale_status ?? '') === 'invoiced',
        ];
    }

    protected function saleAppPrintTransaction(Transaction $order, array $invoiceMap): Transaction
    {
        $stubs = $invoiceMap[(int) $order->id] ?? [];
        if ($stubs === []) {
            return $order;
        }

        $invoiceId = (int) ($stubs[count($stubs) - 1]->id ?? 0);
        if ($invoiceId < 1) {
            return $order;
        }

        $invoice = Transaction::find($invoiceId);

        return $invoice ?: $order;
    }

    /**
     * Product image for Sale App grids (real image or placeholder flag).
     *
     * @return array{url: string, has_image: bool}
     */
    protected function saleProductImage(?string $image): array
    {
        $default = asset('/img/default.png');
        if (empty($image)) {
            return ['url' => $default, 'has_image' => false];
        }

        $path = public_path('uploads/'.config('constants.product_img_path').'/'.$image);
        if (! is_file($path)) {
            return ['url' => $default, 'has_image' => false];
        }

        return [
            'url' => asset('/uploads/'.config('constants.product_img_path').'/'.rawurlencode($image)),
            'has_image' => true,
        ];
    }

    /**
     * POS units for this product only: base (Pc) plus related Box/Carton/etc.
     * Products with no related sub-units stay Pc-only (do not inherit every packing unit on the unit type).
     */
    protected function saleAppUnits(int $business_id, int $product_id, int $unit_id): array
    {
        $key = $business_id.'-'.$product_id.'-'.$unit_id;
        if (isset($this->saleAppUnitsCache[$key])) {
            return $this->saleAppUnitsCache[$key];
        }
        if ($unit_id < 1) {
            return $this->saleAppUnitsCache[$key] = [
                ['id' => 0, 'name' => 'Pc', 'multiplier' => 1.0, 'allow_decimal' => 0],
            ];
        }

        $related = [];
        try {
            $product = Product::where('business_id', $business_id)->find($product_id);
            $ids = $product ? ($product->sub_unit_ids ?? []) : [];
            if (is_array($ids)) {
                $related = array_values(array_filter(array_map('intval', $ids)));
            }
        } catch (\Throwable $e) {
            $related = [];
        }

        try {
            $map = $this->productUtil->getSubUnits($business_id, $unit_id, true, $product_id);
        } catch (\Throwable $e) {
            return $this->saleAppUnitsCache[$key] = [
                ['id' => $unit_id, 'name' => 'Pc', 'multiplier' => 1.0, 'allow_decimal' => 0],
            ];
        }

        $out = [];
        $seen = [];
        foreach ($map as $id => $u) {
            $id = (int) $id;
            $isBase = $id === $unit_id;
            if (! $isBase && ($related === [] || ! in_array($id, $related, true))) {
                continue;
            }
            if (isset($seen[$id])) {
                continue;
            }
            $seen[$id] = true;
            $out[] = [
                'id' => $id,
                'name' => $u['name'],
                'multiplier' => (float) ($u['multiplier'] ?: 1),
                'allow_decimal' => (int) $u['allow_decimal'],
            ];
        }

        if ($related !== []) {
            $extra = Unit::where('business_id', $business_id)->whereIn('id', $related)->get();
            foreach ($extra as $row) {
                $id = (int) $row->id;
                if (isset($seen[$id])) {
                    continue;
                }
                $seen[$id] = true;
                $mult = (float) ($row->base_unit_multiplier ?: 1);
                $out[] = [
                    'id' => $id,
                    'name' => $row->actual_name ?: ($row->short_name ?: 'Unit'),
                    'multiplier' => $mult > 0 ? $mult : 1.0,
                    'allow_decimal' => (int) $row->allow_decimal,
                ];
            }
        }

        usort($out, function ($a, $b) {
            return ($a['multiplier'] <=> $b['multiplier']);
        });
        if ($out === []) {
            $out[] = ['id' => $unit_id, 'name' => 'Pc', 'multiplier' => 1.0, 'allow_decimal' => 0];
        }

        return $this->saleAppUnitsCache[$key] = $out;
    }

    protected function saleAppUnitMultiplier(array $units, int $subId, int $baseUnitId): float
    {
        foreach ($units as $u) {
            if ((int) $u['id'] === $subId) {
                return max(0.0001, (float) ($u['multiplier'] ?: 1));
            }
        }
        if ($subId && $subId !== $baseUnitId) {
            $m = (float) $this->productUtil->getMultiplierOf2Units($baseUnitId, $subId);

            return $m > 0 ? $m : 1.0;
        }

        return 1.0;
    }

    protected function saleAppSellLineFromRequest(int $business_id, array $line): array
    {
        $product = Product::where('business_id', $business_id)->findOrFail($line['product_id']);
        $variation = Variation::where('product_id', $product->id)->findOrFail($line['variation_id']);
        $units = $this->saleAppUnits($business_id, $product->id, (int) $product->unit_id);
        $subId = isset($line['sub_unit_id']) ? (int) $line['sub_unit_id'] : (int) $product->unit_id;
        $multiplier = $this->saleAppUnitMultiplier($units, $subId, (int) $product->unit_id);

        // Catalog / list price for this unit (never updates product master price).
        $catalogBase = (float) ($variation->sell_price_inc_tax ?: $variation->default_sell_price ?: 0);
        $catalogUnit = round($catalogBase * $multiplier, 4);

        // Only users with this permission may override the line price for this order.
        $canEditOrderPrice = auth()->user() && auth()->user()->can('edit_product_price_from_sales_app');
        $soldUnit = $canEditOrderPrice
            ? round((float) ($line['unit_price'] ?? $catalogUnit), 4)
            : $catalogUnit;
        if ($soldUnit < 0) {
            $soldUnit = 0.0;
        }

        // POS-style item discount when sold price is lower than list price.
        $lineDiscountType = null;
        $lineDiscountAmount = 0.0;
        $unitPriceBeforeDiscount = $soldUnit;
        if ($soldUnit + 0.00005 < $catalogUnit) {
            $unitPriceBeforeDiscount = $catalogUnit;
            $lineDiscountType = 'fixed';
            $lineDiscountAmount = round($catalogUnit - $soldUnit, 4);
        }

        $row = [
            'product_id' => $product->id,
            'variation_id' => $variation->id,
            'quantity' => (float) $line['quantity'],
            // unit_price = before discount (selected unit); unit_price_inc_tax = sold price
            'unit_price' => $unitPriceBeforeDiscount,
            'unit_price_inc_tax' => $soldUnit,
            'item_tax' => 0,
            'tax_id' => null,
            'enable_stock' => $product->enable_stock,
            'product_type' => $product->type,
            'line_discount_type' => $lineDiscountType,
            'line_discount_amount' => $lineDiscountAmount,
            'sub_unit_id' => $subId,
            'product_unit_id' => (int) $product->unit_id,
            'base_unit_multiplier' => $multiplier,
        ];
        if (! empty($line['transaction_sell_lines_id'])) {
            $row['transaction_sell_lines_id'] = (int) $line['transaction_sell_lines_id'];
        }

        return $row;
    }

    /**
     * Timesheet: existing POS Time Clock (clock in / out / break) for this sales user.
     */
    public function timesheet(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user_id = auth()->id();

        $entries = EmployeeAttendance::with('breaks')
            ->where('business_id', $business_id)
            ->where('user_id', $user_id)
            ->orderByDesc('clock_in_at')
            ->limit(14)
            ->get();

        return view('sale.timesheet', compact('entries'));
    }

    /**
     * Display list of stock transfers for this sales user.
     */
    public function stockTransfers(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user_id = auth()->id();

        // Get stock transfers - either created by user or location-based
        $transfers = Transaction::where('business_id', $business_id)
            ->where('type', 'sell_transfer')
            ->where(function ($q) use ($user_id) {
                $q->where('created_by', $user_id)
                  ->orWhereHas('transfer_child', function ($child) use ($user_id) {
                      $child->where('created_by', $user_id);
                  });
            })
            ->with([
                'location:id,name',
                'transfer_child' => function ($q) {
                    $q->select('id', 'transfer_parent_id', 'location_id', 'status')
                      ->with('location:id,name');
                }
            ])
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('sale.transfers.index', compact('transfers'));
    }

    /**
     * Show stock transfer details.
     */
    public function showStockTransfer(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        $user_id = auth()->id();

        $transfer = Transaction::where('business_id', $business_id)
            ->where('type', 'sell_transfer')
            ->where(function ($q) use ($user_id) {
                $q->where('created_by', $user_id)
                  ->orWhereHas('transfer_child', function ($child) use ($user_id) {
                      $child->where('created_by', $user_id);
                  });
            })
            ->with([
                'location:id,name',
                'transfer_child' => function ($q) {
                    $q->with([
                        'location:id,name',
                        'sell_lines' => function ($line) {
                            $line->with([
                                'product:id,name,sku',
                                'variations:id,name,sub_sku'
                            ]);
                        }
                    ]);
                }
            ])
            ->findOrFail($id);

        return view('sale.transfers.show', compact('transfer'));
    }

    /**
     * Show create stock transfer form (redirect to main system).
     */
    public function createStockTransfer(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        
        // Get business locations
        $business_locations = BusinessLocation::forDropdown($business_id, false);
        
        // Get statuses
        $statuses = [
            'pending' => 'Pending',
            'in_transit' => 'In Transit',
            'completed' => 'Completed'
        ];
        
        return view('sale.transfers.create', compact('business_locations', 'statuses'));
    }

    /**
     * Store stock transfer (redirect to main system store method).
     */
    public function storeStockTransfer(Request $request)
    {
        // Redirect to main stock transfer controller to handle the actual storage
        // This reuses existing business logic
        return app(\App\Http\Controllers\StockTransferController::class)->store($request);
    }

    public function storeCustomerZeroSale(Request $request, $id)
    {
        $this->saleAppFindCustomer($request, $id);
        $data = $request->validate([
            'reason_id' => 'required|integer|exists:sale_zero_sale_reasons,id',
            'comment' => 'nullable|string|max:2000',
        ]);
        $reason = SaleZeroSaleReason::where('id', $data['reason_id'])->where('is_active', 1)->firstOrFail();

        SaleZeroSale::create([
            'business_id' => $request->session()->get('user.business_id'),
            'contact_id' => (int) $id,
            'reason_id' => $reason->id,
            'created_by' => auth()->id(),
            'location_id' => $request->session()->get('user.default_location_id'),
            'comment' => $data['comment'] ?? null,
        ]);

        return redirect()
            ->route('sale.customers')
            ->with('status', ['success' => 1, 'msg' => 'Zero sale saved.']);
    }

    protected function saleAppZeroSaleReasons()
    {
        try {
            return SaleZeroSaleReason::active()->get(['id', 'name']);
        } catch (\Throwable $e) {
            return collect();
        }
    }

    public function showCustomer(Request $request, $id)
    {
        $data = $this->saleAppCustomerBundle($request, $id);
        $tab = $request->get('tab', 'details');
        if (! in_array($tab, ['details', 'invoices', 'history'], true)) {
            $tab = 'invoices';
        }
        $data['tab'] = $tab;
        $data['zeroSaleReasons'] = $this->saleAppZeroSaleReasons();

        return view('sale.customers.show', $data);
    }

    public function customerOpenInvoices(Request $request, $id)
    {
        $data = $this->saleAppCustomerBundle($request, $id, true);
        $q = trim((string) $request->get('q', ''));
        if ($q !== '') {
            $data['openInvoices'] = $data['openInvoices']->filter(function ($row) use ($q) {
                return stripos($row['invoice_no'].' '.$row['name'], $q) !== false;
            })->values();
        }
        $data['q'] = $q;

        return view('sale.customers.open_invoices', $data);
    }

    public function customerHistory(Request $request, $id)
    {
        $data = $this->saleAppCustomerBundle($request, $id);
        $q = trim((string) $request->get('q', ''));
        if ($q !== '') {
            $data['history'] = $data['history']->filter(function ($row) use ($q) {
                return stripos($row['invoice_no'].' '.$row['kind'].' '.$row['name'], $q) !== false;
            })->values();
        }
        $data['q'] = $q;

        return view('sale.customers.history', $data);
    }

    public function customerPayment(Request $request, $id)
    {
        $data = $this->saleAppCustomerBundle($request, $id, true);
        $data['paymentTypes'] = $this->saleAppActivePaymentTypes($request);
        $data['tabPay'] = $request->get('view', 'amount') === 'payments' ? 'payments' : 'amount';
        $data['preselect'] = (int) $request->get('invoice_id', 0);

        return view('sale.customers.payment', $data);
    }

    public function storeCustomerPayment(Request $request, $id)
    {
        $this->saleAppFindCustomer($request, $id);
        $activeMethods = $this->saleAppActivePaymentTypes($request);
        $data = $request->validate([
            'amount' => 'required',
            'method' => 'required|string',
            'paid_on' => 'nullable|string',
            'note' => 'nullable|string|max:500',
            'signature' => 'nullable|string',
            'pay_mode' => 'nullable|in:pay,credit',
        ]);
        if (! array_key_exists($data['method'], $activeMethods)) {
            return back()->with('status', ['success' => 0, 'msg' => 'This payment method is not active for your location.']);
        }
        $amount = (float) str_replace([',', '$'], '', (string) $data['amount']);
        if ($amount <= 0) {
            return back()->with('status', ['success' => 0, 'msg' => 'Enter a payment amount.']);
        }

        $payRequest = new Request([
            'contact_id' => (int) $id,
            'amount' => $amount,
            'method' => $data['method'],
            'paid_on' => $data['paid_on'] ?: Carbon::now()->toDateTimeString(),
            'note' => $data['note'] ?? '',
            'due_payment_type' => 'sell',
            'is_reverse' => ($data['pay_mode'] ?? 'pay') === 'credit' ? 1 : 0,
        ]);

        try {
            DB::beginTransaction();
            $tp = $this->transactionUtil->payContact($payRequest, false);
            $sigFile = $this->saleAppStorePaymentSignature($request->input('signature'));
            if ($sigFile && $tp) {
                $tp->signature = $sigFile;
                $tp->save();
                TransactionPayment::where('parent_id', $tp->id)->update(['signature' => $sigFile]);
            }
            DB::commit();

            return redirect()
                ->route('sale.customers.payment', $id)
                ->with('status', ['success' => 1, 'msg' => 'Payment saved.']);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('status', ['success' => 0, 'msg' => $e->getMessage()]);
        }
    }

    protected function saleAppActivePaymentTypes(Request $request): array
    {
        $business_id = $request->session()->get('user.business_id');
        $location_id = $request->session()->get('user.default_location_id');
        $loc = null;
        if (! empty($location_id)) {
            $loc = BusinessLocation::where('business_id', $business_id)->find($location_id);
        }
        if (empty($loc)) {
            $loc = BusinessLocation::where('business_id', $business_id)->first();
        }
        if (empty($loc)) {
            return [];
        }

        return $this->transactionUtil->payment_types($loc, false, $business_id);
    }

    protected function saleAppStorePaymentSignature(?string $dataUrl): ?string
    {
        if (empty($dataUrl) || ! str_starts_with($dataUrl, 'data:image/png;base64,')) {
            return null;
        }
        $raw = base64_decode(substr($dataUrl, strlen('data:image/png;base64,')), true);
        if ($raw === false || strlen($raw) < 50) {
            return null;
        }
        $dir = public_path('uploads/signatures');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = 'sig_'.auth()->id().'_'.time().'_'.bin2hex(random_bytes(4)).'.png';
        file_put_contents($dir.DIRECTORY_SEPARATOR.$name, $raw);

        return $name;
    }

    protected function saleAppFindCustomer(Request $request, $id): Contact
    {
        if (! self::userCanListCustomers()) {
            abort(403);
        }
        $business_id = $request->session()->get('user.business_id');

        return Contact::where('business_id', $business_id)
            ->onlyCustomers()
            ->findOrFail($id);
    }

    protected function saleAppCustomerAddress(Contact $c): string
    {
        if (! empty($c->shipping_address)) {
            return trim(preg_replace('/\s+/', ' ', strip_tags(str_replace(['<br>', '<br/>', '<br />'], ', ', $c->shipping_address))));
        }

        return trim(implode(', ', array_filter([
            $c->address_line_1,
            $c->address_line_2,
            $c->city,
            $c->state,
            $c->zip_code,
        ])));
    }

    protected function saleAppTxnPaid(Transaction $tx): float
    {
        $paid = 0.0;
        foreach ($tx->payment_lines ?? [] as $pay) {
            $amt = (float) ($pay->amount ?? 0);
            $paid += ! empty($pay->is_return) ? -$amt : $amt;
        }

        return $paid;
    }

    protected function saleAppCustomerBundle(Request $request, $id, bool $openOnly = false): array
    {
        $customer = $this->saleAppFindCustomer($request, $id);
        $business_id = $request->session()->get('user.business_id');
        $name = trim((string) ($customer->supplier_business_name ?: $customer->name));
        $address = $this->saleAppCustomerAddress($customer);
        $groupName = '';
        if (! empty($customer->customer_group_id)) {
            $groupName = (string) (CustomerGroup::where('business_id', $business_id)
                ->where('id', $customer->customer_group_id)
                ->value('name') ?? '');
        }

        $sells = Transaction::where('business_id', $business_id)
            ->where('contact_id', $customer->id)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->with('payment_lines')
            ->orderByDesc('transaction_date')
            ->get();

        $orders = Transaction::where('business_id', $business_id)
            ->where('contact_id', $customer->id)
            ->where('type', 'sales_order')
            ->orderByDesc('transaction_date')
            ->get();

        $openInvoices = collect();
        $aging = ['current' => 0.0, 'd30' => 0.0, 'd60' => 0.0, 'd90' => 0.0];
        $now = Carbon::now();

        foreach ($sells as $tx) {
            $paid = $this->saleAppTxnPaid($tx);
            $due = round((float) $tx->final_total - $paid, 2);
            $row = [
                'id' => $tx->id,
                'kind' => 'Invoice',
                'name' => $name,
                'invoice_no' => $tx->invoice_no ?: (string) $tx->id,
                'date' => Carbon::parse($tx->transaction_date)->format('m/d/Y'),
                'due_date' => Carbon::parse($tx->transaction_date)->addDays(4)->format('m/d/Y'),
                'amount' => (float) $tx->final_total,
                'balance' => $due,
                'status' => $due <= 0.009 ? 'Paid' : 'Sent',
                'acct' => $customer->contact_id,
            ];
            if ($due > 0.009) {
                $openInvoices->push($row);
                $age = abs((int) Carbon::parse($tx->transaction_date)->diffInDays($now));
                if ($age < 30) {
                    $aging['current'] += $due;
                } elseif ($age < 60) {
                    $aging['d30'] += $due;
                } elseif ($age < 90) {
                    $aging['d60'] += $due;
                } else {
                    $aging['d90'] += $due;
                }
            }
        }

        $history = collect();
        foreach ($sells as $tx) {
            $paid = $this->saleAppTxnPaid($tx);
            $due = round((float) $tx->final_total - $paid, 2);
            $history->push([
                'id' => $tx->id,
                'kind' => 'Invoice',
                'name' => $name,
                'invoice_no' => $tx->invoice_no ?: (string) $tx->id,
                'date' => Carbon::parse($tx->transaction_date)->format('m/d/Y'),
                'amount' => (float) $tx->final_total,
                'balance' => $due,
                'status' => $due <= 0.009 ? 'Paid' : 'Sent',
                'show_url' => (int) $tx->created_by === (int) auth()->id()
                    ? route('sale.orders.show', $tx->id)
                    : route('sale.customers.show', [$customer->id, 'tab' => 'history']),
            ]);
        }
        foreach ($orders as $tx) {
            $history->push([
                'id' => $tx->id,
                'kind' => 'Order',
                'name' => $name,
                'invoice_no' => $tx->invoice_no ?: (string) $tx->id,
                'date' => Carbon::parse($tx->transaction_date)->format('m/d/Y'),
                'amount' => (float) $tx->final_total,
                'balance' => null,
                'status' => 'Sent',
                'show_url' => (int) $tx->created_by === (int) auth()->id()
                    ? route('sale.orders.show', $tx->id)
                    : route('sale.customers.show', [$customer->id, 'tab' => 'history']),
            ]);
        }
        $history = $history->sortByDesc(function ($r) {
            return $r['date'].$r['id'];
        })->values();

        $openBalance = round((float) array_sum($aging), 2);

        return [
            'customer' => $customer,
            'custName' => $name,
            'custAddr' => $address,
            'openInvoices' => $openInvoices->values(),
            'history' => $history,
            'aging' => $aging,
            'openBalance' => $openBalance,
            'openOnly' => $openOnly,
            'groupName' => $groupName,
            'printBase' => url('/sale/customers/'.$customer->id.'/invoices'),
        ];
    }
}
