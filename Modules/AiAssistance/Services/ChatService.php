<?php

namespace Modules\AiAssistance\Services;

use Illuminate\Support\Facades\Http;
use Modules\AiAssistance\Entities\AiAssistanceSetting;

class ChatService
{
    public const ASSISTANT_NAME = 'JapsAI';

    public function __construct(protected InsightsService $insights)
    {
    }

    public function detectTool(string $message): ?string
    {
        $m = strtolower($message);
        if (str_contains($m, 'stock') || str_contains($m, 'inventory') || str_contains($m, 'reorder')) {
            return __('aiassistance::lang.tool_lookup_inventory');
        }
        if (str_contains($m, 'purchase') || str_contains($m, 'supplier') || str_contains($m, 'payable')) {
            return __('aiassistance::lang.tool_lookup_purchases');
        }
        if (str_contains($m, 'expense')) {
            return __('aiassistance::lang.tool_lookup_expenses');
        }
        if (str_contains($m, 'payment') || str_contains($m, 'cash collected') || str_contains($m, 'by method')) {
            return __('aiassistance::lang.tool_lookup_payments');
        }
        if (str_contains($m, 'top product') || str_contains($m, 'best sell') || str_contains($m, 'best-selling')) {
            return __('aiassistance::lang.tool_lookup_products');
        }
        if (str_contains($m, 'invoice') || str_contains($m, 'unpaid') || str_contains($m, 'overdue') || str_contains($m, 'receivable')) {
            return __('aiassistance::lang.tool_lookup_invoices');
        }
        if (str_contains($m, 'report') || str_contains($m, 'overview') || str_contains($m, 'dashboard') || str_contains($m, 'profit')) {
            return __('aiassistance::lang.tool_lookup_reports');
        }
        if (
            str_contains($m, 'sale') || str_contains($m, 'sell') || str_contains($m, 'revenue')
            || str_contains($m, 'week') || str_contains($m, 'today') || str_contains($m, 'turnover')
            || str_contains($m, 'income') || str_contains($m, 'earning')
        ) {
            return __('aiassistance::lang.tool_lookup_sales');
        }
        if (str_contains($m, 'pipeline') || str_contains($m, 'lead') || str_contains($m, 'customer')) {
            return __('aiassistance::lang.tool_lookup_pipeline');
        }

        return null;
    }

    protected function money(float $amount, string $symbol): string
    {
        return $symbol.number_format($amount, 2);
    }

    protected function formatSalesBlock(string $label, array $s, string $symbol): string
    {
        return "{$label}: ".$this->money((float) ($s['total'] ?? 0), $symbol)
            .' across '.(int) ($s['order_count'] ?? 0).' order(s)'
            .' (avg '.$this->money((float) ($s['aov'] ?? 0), $symbol).')';
    }

    protected function formatCountTotal(string $label, array $row, string $symbol): string
    {
        return "{$label}: ".$this->money((float) ($row['total'] ?? 0), $symbol)
            .' ('.(int) ($row['count'] ?? 0).' txn)';
    }

    protected function asOfLine(array $data): string
    {
        return 'As of '.$data['as_of'].' ('.$data['timezone'].') — live POS database numbers.';
    }

    /**
     * Instant answers from DB snapshot (works without OpenAI for structured report questions).
     */
    public function localReply(int $business_id, string $message): ?array
    {
        $m = strtolower(trim($message));
        $m = preg_replace('/\s+/', ' ', $m) ?? $m;
        $wantsFresh = (bool) preg_match('/\b(live|latest|refresh|realtime|real-time|exact now|no cache)\b/', $m);

        $data = $this->insights->forBusiness($business_id, $wantsFresh);
        $symbol = session('currency')['symbol'] ?? '$';
        $tool = $this->detectTool($message);
        $name = self::ASSISTANT_NAME;
        $reports = $data['reports']['views'] ?? [];

        // ---- Identity ----
        if (
            preg_match('/\b(your name|who are you|what are you|what is your name|whats your name)\b/', $m)
            || $m === 'name?' || $m === 'name'
        ) {
            return [
                'success' => true,
                'reply' => "I'm {$name} — your POS business assistant. I read live data from your database for sales, purchases, expenses, stock, payments, invoices, and customer reports.",
                'tool' => null,
            ];
        }

        if (preg_match('/\b(hello|hi|hey|good morning|good afternoon|good evening)\b/', $m) && strlen($m) < 40) {
            return [
                'success' => true,
                'reply' => "Hi! I'm {$name}. Ask for reports like today's sales, purchases, expenses, payments by method, top products, receivables, or a full business overview.",
                'tool' => null,
            ];
        }

        // ---- Full overview / dashboard report ----
        if (
            preg_match('/\b(overview|dashboard|business report|full report|summary report|kpi)\b/', $m)
            || (str_contains($m, 'report') && (str_contains($m, 'all') || str_contains($m, 'whole') || str_contains($m, 'complete')))
        ) {
            return [
                'success' => true,
                'reply' => $this->renderOverviewReport($data, $symbol, $name),
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_reports'),
            ];
        }

        // ---- Purchases ----
        if (str_contains($m, 'purchase') || str_contains($m, 'buying') || str_contains($m, 'bought')) {
            $p = $reports['purchases'] ?? [];
            $lines = [
                "{$name} purchase report",
                $this->asOfLine($data),
                $this->formatCountTotal('Today', $p['today'] ?? [], $symbol),
                $this->formatCountTotal('This week', $p['this_week'] ?? [], $symbol),
                $this->formatCountTotal('This month', $p['this_month'] ?? [], $symbol),
                $this->formatCountTotal('Last 30 days', $p['last_30_days'] ?? [], $symbol),
            ];
            $bal = $reports['receivables_payables'] ?? [];
            if (! empty($bal)) {
                $lines[] = 'Supplier payables (due/partial): '.$this->money((float) ($bal['supplier_payable'] ?? 0), $symbol);
                $lines[] = 'Suppliers on file: '.(int) ($bal['suppliers'] ?? 0);
            }

            return [
                'success' => true,
                'reply' => implode("\n", $lines),
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_purchases'),
            ];
        }

        // ---- Expenses ----
        if (str_contains($m, 'expense') || str_contains($m, 'spending')) {
            $e = $reports['expenses'] ?? [];
            $lines = [
                "{$name} expense report",
                $this->asOfLine($data),
                $this->formatCountTotal('Today', $e['today'] ?? [], $symbol),
                $this->formatCountTotal('This week', $e['this_week'] ?? [], $symbol),
                $this->formatCountTotal('This month', $e['this_month'] ?? [], $symbol),
                $this->formatCountTotal('Last 30 days', $e['last_30_days'] ?? [], $symbol),
            ];

            return [
                'success' => true,
                'reply' => implode("\n", $lines),
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_expenses'),
            ];
        }

        // ---- Payments by method ----
        if (
            str_contains($m, 'payment') || str_contains($m, 'cash collected')
            || str_contains($m, 'by method') || str_contains($m, 'payment method')
        ) {
            $period = preg_match('/\b(today|todays)\b/', $m) ? 'today' : 'last_30_days';
            $pay = $reports['payments'][$period] ?? ['total' => 0, 'count' => 0, 'by_method' => []];
            $label = $period === 'today' ? 'Today' : 'Last 30 days';
            $lines = [
                "{$name} payments received ({$label})",
                $this->asOfLine($data),
                'Total collected: '.$this->money((float) ($pay['total'] ?? 0), $symbol)
                    .' across '.(int) ($pay['count'] ?? 0).' payment(s)',
            ];
            foreach (($pay['by_method'] ?? []) as $row) {
                $lines[] = '• '.ucfirst((string) $row['method']).': '
                    .$this->money((float) $row['total'], $symbol)
                    .' ('.(int) $row['count'].')';
            }
            if (empty($pay['by_method'])) {
                $lines[] = 'No payments recorded for this period.';
            }

            return [
                'success' => true,
                'reply' => implode("\n", $lines),
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_payments'),
            ];
        }

        // ---- Top products ----
        if (
            str_contains($m, 'top product') || str_contains($m, 'best sell') || str_contains($m, 'best-selling')
            || str_contains($m, 'fastest selling') || (str_contains($m, 'top') && str_contains($m, 'sell'))
        ) {
            $period = preg_match('/\b(today|todays)\b/', $m) ? 'today' : 'last_30_days';
            $items = $reports['top_products'][$period] ?? [];
            $label = $period === 'today' ? 'Today' : 'Last 30 days';
            $lines = ["{$name} top selling products ({$label})", $this->asOfLine($data)];
            if (empty($items)) {
                $lines[] = 'No sell lines for this period.';
            } else {
                $i = 1;
                foreach ($items as $row) {
                    $lines[] = $i.'. '.$row['name']
                        .($row['sku'] ? ' ['.$row['sku'].']' : '')
                        .' — qty '.number_format((float) $row['qty'], 2)
                        .' · '.$this->money((float) $row['revenue'], $symbol);
                    $i++;
                }
            }

            return [
                'success' => true,
                'reply' => implode("\n", $lines),
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_products'),
            ];
        }

        // ---- Receivables / payables ----
        if (
            str_contains($m, 'receivable') || str_contains($m, 'payable')
            || (str_contains($m, 'supplier') && str_contains($m, 'due'))
            || str_contains($m, 'customer due')
        ) {
            $bal = $reports['receivables_payables'] ?? [];
            $lines = [
                "{$name} receivables & payables",
                $this->asOfLine($data),
                'Customer receivables (open sell dues): '.$this->money((float) ($bal['customer_receivable'] ?? 0), $symbol),
                'Supplier payables (open purchase dues): '.$this->money((float) ($bal['supplier_payable'] ?? 0), $symbol),
                'Customers: '.(int) ($data['sales']['customers'] ?? 0)
                    .' · Suppliers: '.(int) ($bal['suppliers'] ?? 0)
                    .' · Locations: '.(int) ($bal['locations'] ?? 0),
            ];

            return [
                'success' => true,
                'reply' => implode("\n", $lines),
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_invoices'),
            ];
        }

        // ---- Stock value ----
        if (str_contains($m, 'stock value') || str_contains($m, 'inventory value') || str_contains($m, 'stock worth')) {
            $st = $reports['stock'] ?? [];
            $lines = [
                "{$name} stock value",
                $this->asOfLine($data),
                'On-hand stock value (qty × default purchase price): '.$this->money((float) ($st['stock_value'] ?? 0), $symbol),
                'Total on-hand quantity: '.number_format((float) ($st['total_qty'] ?? 0), 2),
                'Out of stock SKUs: '.(int) ($data['inventory']['out_of_stock_count'] ?? 0)
                    .' · Low stock: '.(int) ($data['inventory']['low_stock_count'] ?? 0),
            ];

            return [
                'success' => true,
                'reply' => implode("\n", $lines),
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_inventory'),
            ];
        }

        // ---- Profit-view KPI ----
        if (str_contains($m, 'profit') || str_contains($m, 'p&l') || str_contains($m, 'pnl')) {
            $pl = $reports['profit_view_30d'] ?? [];
            $lines = [
                "{$name} 30-day profit view (directional)",
                $this->asOfLine($data),
                'Sales: '.$this->money((float) ($pl['sales'] ?? 0), $symbol),
                'Sales returns: '.$this->money((float) ($pl['sales_returns'] ?? 0), $symbol),
                'Expenses: '.$this->money((float) ($pl['expenses'] ?? 0), $symbol),
                'Purchases: '.$this->money((float) ($pl['purchases'] ?? 0), $symbol),
                'Sales − returns − expenses: '.$this->money((float) ($pl['sales_minus_returns_minus_expenses'] ?? 0), $symbol),
                'Note: '.(string) ($pl['note'] ?? 'Not full COGS accounting.'),
            ];

            return [
                'success' => true,
                'reply' => implode("\n", $lines),
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_reports'),
            ];
        }

        // ---- Generic "report" without specifics ----
        if (str_contains($m, 'report') && ! str_contains($m, 'sales')) {
            return [
                'success' => true,
                'reply' => $this->renderOverviewReport($data, $symbol, $name),
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_reports'),
            ];
        }

        // ---- Sales periods ----
        $salesKeywords = str_contains($m, 'sale') || str_contains($m, 'sell') || str_contains($m, 'revenue')
            || str_contains($m, 'turnover') || str_contains($m, 'income') || str_contains($m, 'earning')
            || str_contains($m, 'order') || preg_match('/\bhow much\b/', $m)
            || preg_match('/\btotal\b/', $m);

        $wantsToday = (bool) preg_match('/\b(today|todays|to-?day)\b/', $m);
        $wantsYesterday = (bool) preg_match('/\b(yesterday|yester-?day)\b/', $m);
        $wantsWeek = (bool) preg_match('/\b(this week|week|weekly)\b/', $m);
        $wantsMonth = (bool) preg_match('/\b(this month|month|monthly)\b/', $m);

        if ($wantsToday && ($salesKeywords || preg_match('/\b(total|amount|summary|report)\b/', $m) || strlen($m) <= 24)) {
            $s = $data['sales']['today'];
            $lines = [
                "Here's today's sales report",
                $this->asOfLine($data),
                $this->formatSalesBlock("Today's total", $s, $symbol),
            ];
            if (! empty($data['today_invoices'])) {
                $lines[] = 'Recent invoices today:';
                foreach (array_slice($data['today_invoices'], 0, 8) as $inv) {
                    $lines[] = '• '.$inv['invoice_no'].' — '.$this->money((float) $inv['amount'], $symbol)
                        .' ('.$inv['payment_status'].') at '.$inv['time'];
                }
            } else {
                $lines[] = 'No final sales recorded yet today.';
            }
            $lines[] = $this->formatSalesBlock('Yesterday', $data['sales']['yesterday'], $symbol);
            $lines[] = $this->formatSalesBlock('Last 30 days', $data['sales']['last_30_days'], $symbol);

            return [
                'success' => true,
                'reply' => implode("\n", $lines),
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_sales'),
            ];
        }

        if ($wantsYesterday && ($salesKeywords || strlen($m) <= 28)) {
            return [
                'success' => true,
                'reply' => $this->asOfLine($data)."\n"
                    .$this->formatSalesBlock("Yesterday's sales", $data['sales']['yesterday'], $symbol)."\n"
                    .$this->formatSalesBlock("Today so far", $data['sales']['today'], $symbol),
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_sales'),
            ];
        }

        if ($wantsWeek && ($salesKeywords || str_contains($m, 'down') || str_contains($m, 'why') || strlen($m) <= 40)) {
            $reply = $this->asOfLine($data)."\n"
                .$this->formatSalesBlock('This week (Sun–now)', $data['sales']['this_week'], $symbol)."\n"
                .$this->formatSalesBlock('Today', $data['sales']['today'], $symbol)."\n"
                .$this->formatSalesBlock('Last 30 days', $data['sales']['last_30_days'], $symbol);
            if (str_contains($m, 'down') || str_contains($m, 'why')) {
                $reply .= "\nCompare with top products (ask: top selling products) for product-level view.";
            }

            return [
                'success' => true,
                'reply' => $reply,
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_sales'),
            ];
        }

        if ($wantsMonth && $salesKeywords) {
            return [
                'success' => true,
                'reply' => $this->asOfLine($data)."\n"
                    .$this->formatSalesBlock('This month', $data['sales']['this_month'], $symbol)."\n"
                    .$this->formatSalesBlock('Today', $data['sales']['today'], $symbol)."\n"
                    .$this->formatSalesBlock('Last 30 days', $data['sales']['last_30_days'], $symbol),
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_sales'),
            ];
        }

        if (
            $salesKeywords
            || preg_match('/\b(sales|sale|revenue|how much did we sell|how much sold)\b/', $m)
        ) {
            $lines = [
                "Sales report from {$name}",
                $this->asOfLine($data),
                $this->formatSalesBlock('Today', $data['sales']['today'], $symbol),
                $this->formatSalesBlock('Yesterday', $data['sales']['yesterday'], $symbol),
                $this->formatSalesBlock('This week', $data['sales']['this_week'], $symbol),
                $this->formatSalesBlock('This month', $data['sales']['this_month'], $symbol),
                $this->formatSalesBlock('Last 30 days', $data['sales']['last_30_days'], $symbol),
            ];

            return [
                'success' => true,
                'reply' => implode("\n", $lines),
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_sales'),
            ];
        }

        // ---- Inventory alerts ----
        if (
            str_contains($m, 'low stock') || str_contains($m, 'out of stock')
            || (str_contains($m, 'stock') && (str_contains($m, 'show') || str_contains($m, 'list') || str_contains($m, 'alert')))
            || str_contains($m, 'inventory')
            || str_contains($m, 'reorder')
        ) {
            $lines = ["{$name} inventory report", $this->asOfLine($data)];
            $lines[] = 'Products: '.$data['inventory']['product_count']
                .' · Out of stock: '.$data['inventory']['out_of_stock_count']
                .' · Low stock: '.$data['inventory']['low_stock_count'];
            $st = $reports['stock'] ?? [];
            if (! empty($st)) {
                $lines[] = 'Stock value: '.$this->money((float) ($st['stock_value'] ?? 0), $symbol);
            }
            $items = $data['inventory']['lowest'];
            if (empty($items)) {
                $lines[] = 'No stock alerts right now.';
            } else {
                $lines[] = 'Lowest stock items:';
                foreach ($items as $item) {
                    $lines[] = '• '.$item['name'].' — qty '.$item['qty'].' / alert '.$item['alert'];
                }
            }

            return [
                'success' => true,
                'reply' => implode("\n", $lines),
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_inventory'),
            ];
        }

        // ---- Invoices ----
        if (
            str_contains($m, 'unpaid') || str_contains($m, 'overdue')
            || (str_contains($m, 'invoice') && (str_contains($m, 'list') || str_contains($m, 'due') || str_contains($m, 'open')))
            || str_contains($m, 'outstanding')
        ) {
            $inv = $data['invoices'];
            $reply = "{$name} invoice report\n"
                .$this->asOfLine($data)."\n"
                ."Open invoices: {$inv['open']}\n"
                ."Overdue: {$inv['overdue']}\n"
                .'Outstanding: '.$this->money((float) $inv['outstanding'], $symbol);
            if (! empty($inv['oldest'])) {
                $reply .= "\nOldest overdue: {$inv['oldest']['invoice_no']} ("
                    .$this->money((float) $inv['oldest']['amount'], $symbol).')';
            }

            return [
                'success' => true,
                'reply' => $reply,
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_invoices'),
            ];
        }

        // ---- Customers / pipeline ----
        if (str_contains($m, 'pipeline') || str_contains($m, 'lead') || str_contains($m, 'customer')) {
            $reply = "{$name} customers & pipeline\n"
                .$this->asOfLine($data)."\n"
                .$data['sales']['customers']." customers on file\n"
                .$data['sales']['leads'].' active leads in the pipeline';

            return [
                'success' => true,
                'reply' => $reply,
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_pipeline'),
            ];
        }

        // ---- Help ----
        if (preg_match('/\b(help|what can you do|commands)\b/', $m)) {
            return [
                'success' => true,
                'reply' => "I'm {$name}. I generate reports from your POS database with real numbers:\n"
                    ."• Sales — today / week / month / 30 days\n"
                    ."• Purchases & supplier payables\n"
                    ."• Expenses\n"
                    ."• Payments by method\n"
                    ."• Top selling products\n"
                    ."• Inventory + stock value\n"
                    ."• Unpaid invoices / receivables\n"
                    ."• Business overview report\n"
                    ."• Profit view (sales − returns − expenses)\n"
                    ."Say \"live\" or \"latest\" for a fresh DB pull (skips short cache).",
                'tool' => null,
            ];
        }

        return null;
    }

    protected function renderOverviewReport(array $data, string $symbol, string $name): string
    {
        $r = $data['reports']['views'] ?? [];
        $lines = [
            "{$name} — full business overview",
            $this->asOfLine($data),
            '',
            'SALES',
            $this->formatSalesBlock('  Today', $data['sales']['today'], $symbol),
            $this->formatSalesBlock('  This week', $data['sales']['this_week'], $symbol),
            $this->formatSalesBlock('  This month', $data['sales']['this_month'], $symbol),
            $this->formatSalesBlock('  Last 30 days', $data['sales']['last_30_days'], $symbol),
            '',
            'PURCHASES (30d): '.$this->money((float) ($r['purchases']['last_30_days']['total'] ?? 0), $symbol)
                .' · EXPENSES (30d): '.$this->money((float) ($r['expenses']['last_30_days']['total'] ?? 0), $symbol),
            'PAYMENTS TODAY: '.$this->money((float) ($r['payments']['today']['total'] ?? 0), $symbol)
                .' · 30d: '.$this->money((float) ($r['payments']['last_30_days']['total'] ?? 0), $symbol),
            'RECEIVABLES: '.$this->money((float) ($r['receivables_payables']['customer_receivable'] ?? 0), $symbol)
                .' · PAYABLES: '.$this->money((float) ($r['receivables_payables']['supplier_payable'] ?? 0), $symbol),
            'STOCK VALUE: '.$this->money((float) ($r['stock']['stock_value'] ?? 0), $symbol)
                .' · Low stock: '.(int) ($data['inventory']['low_stock_count'] ?? 0)
                .' · Out of stock: '.(int) ($data['inventory']['out_of_stock_count'] ?? 0),
            'OPEN INVOICES: '.(int) ($data['invoices']['open'] ?? 0)
                .' · Outstanding: '.$this->money((float) ($data['invoices']['outstanding'] ?? 0), $symbol),
            'CUSTOMERS: '.(int) ($data['sales']['customers'] ?? 0)
                .' · SUPPLIERS: '.(int) ($r['receivables_payables']['suppliers'] ?? 0)
                .' · LOCATIONS: '.(int) ($r['receivables_payables']['locations'] ?? 0),
        ];

        $top = $r['top_products']['last_30_days'] ?? [];
        if (! empty($top)) {
            $lines[] = '';
            $lines[] = 'TOP PRODUCTS (30d)';
            foreach (array_slice($top, 0, 5) as $i => $row) {
                $lines[] = '  '.($i + 1).'. '.$row['name'].' — '.$this->money((float) $row['revenue'], $symbol);
            }
        }

        $lines[] = '';
        $lines[] = 'Tip: ask a specific view, e.g. "purchase report", "payments by method today", "top selling products".';

        return implode("\n", $lines);
    }

    public function reply(int $business_id, string $user_message, array $history = []): array
    {
        $local = $this->localReply($business_id, $user_message);
        if ($local) {
            return $local;
        }

        $settings = AiAssistanceSetting::forBusiness($business_id);
        $api_key = $settings->resolveApiKey();
        $tool = $this->detectTool($user_message);

        if (empty($api_key)) {
            $symbol = session('currency')['symbol'] ?? '$';
            $data = $this->insights->forBusiness($business_id);
            $overview = $this->renderOverviewReport($data, $symbol, self::ASSISTANT_NAME)
                ."\n\n(OpenAI is not configured — this overview is still generated from your live POS database.)";

            return [
                'success' => true,
                'reply' => $overview,
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_reports'),
            ];
        }

        $snapshot = $this->insights->snapshotText($business_id);

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are JapsAI (your name is always JapsAI), the POS / wholesale business assistant. '
                    .'Answer using ONLY the JSON snapshot. All figures are from the live business database. '
                    .'Never invent numbers. Always mention the as_of timestamp when sharing key totals. '
                    .'You can present different report views: sales, purchases, expenses, payments by method, '
                    .'top products, inventory/stock value, receivables/payables, and overview. '
                    .'If a total is 0, say zero — do not claim data is missing. '
                    .'Be concise; use bullet lists for multi-period reports. '
                    ."Snapshot:\n".$snapshot,
            ],
        ];

        foreach ($history as $item) {
            if (empty($item['role']) || empty($item['content'])) {
                continue;
            }
            if (! in_array($item['role'], ['user', 'assistant'], true)) {
                continue;
            }
            $messages[] = [
                'role' => $item['role'],
                'content' => (string) $item['content'],
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $user_message,
        ];

        try {
            $response = Http::withToken($api_key)
                ->timeout(60)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $settings->resolveModel(),
                    'messages' => $messages,
                    'temperature' => 0.15,
                ]);

            if (! $response->successful()) {
                $err = $response->json('error.message') ?: $response->body();

                return [
                    'success' => false,
                    'msg' => __('aiassistance::lang.chat_error').' '.$err,
                    'tool' => $tool,
                ];
            }

            $content = data_get($response->json(), 'choices.0.message.content');
            if (empty($content)) {
                return [
                    'success' => false,
                    'msg' => __('aiassistance::lang.chat_error'),
                    'tool' => $tool,
                ];
            }

            return [
                'success' => true,
                'reply' => $content,
                'tool' => $tool,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'msg' => __('aiassistance::lang.chat_error').' '.$e->getMessage(),
                'tool' => $tool,
            ];
        }
    }
}
