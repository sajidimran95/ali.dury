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
        if (str_contains($m, 'invoice') || str_contains($m, 'unpaid') || str_contains($m, 'overdue') || (str_contains($m, 'due') && ! str_contains($m, 'today'))) {
            return __('aiassistance::lang.tool_lookup_invoices');
        }
        if (
            str_contains($m, 'sale') || str_contains($m, 'sell') || str_contains($m, 'revenue')
            || str_contains($m, 'week') || str_contains($m, 'today') || str_contains($m, 'turnover')
            || str_contains($m, 'income') || str_contains($m, 'earning')
        ) {
            return __('aiassistance::lang.tool_lookup_sales');
        }
        if (str_contains($m, 'pipeline') || str_contains($m, 'lead')) {
            return __('aiassistance::lang.tool_lookup_pipeline');
        }
        if (str_contains($m, 'customer') || str_contains($m, 'how many customer')) {
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

    /**
     * Instant answers for common questions using live snapshot (works even if OpenAI is down).
     */
    public function localReply(int $business_id, string $message): ?array
    {
        $data = $this->insights->forBusiness($business_id);
        $symbol = session('currency')['symbol'] ?? '$';
        $m = strtolower(trim($message));
        $m = preg_replace('/\s+/', ' ', $m) ?? $m;
        $tool = $this->detectTool($message);
        $name = self::ASSISTANT_NAME;

        // ---- Identity ----
        if (
            preg_match('/\b(your name|who are you|what are you|what is your name|whats your name)\b/', $m)
            || $m === 'name?'
            || $m === 'name'
        ) {
            return [
                'success' => true,
                'reply' => "I'm {$name} — your business assistant for sales, inventory, invoices, and pipeline. Ask me anything about your store data.",
                'tool' => null,
            ];
        }

        if (preg_match('/\b(hello|hi|hey|good morning|good afternoon|good evening)\b/', $m) && strlen($m) < 40) {
            return [
                'success' => true,
                'reply' => "Hi! I'm {$name}. Ask me about today's sales, inventory, unpaid invoices, customers, or your pipeline.",
                'tool' => null,
            ];
        }

        // ---- Sales: today / yesterday / week / month / 30 days ----
        $salesKeywords = str_contains($m, 'sale') || str_contains($m, 'sell') || str_contains($m, 'revenue')
            || str_contains($m, 'turnover') || str_contains($m, 'income') || str_contains($m, 'earning')
            || str_contains($m, 'order') || preg_match('/\bhow much\b/', $m)
            || preg_match('/\btotal\b/', $m);

        $wantsToday = (bool) preg_match('/\b(today|todays|to-?day)\b/', $m);
        $wantsYesterday = (bool) preg_match('/\b(yesterday|yester-?day)\b/', $m);
        $wantsWeek = (bool) preg_match('/\b(this week|week|weekly)\b/', $m);
        $wantsMonth = (bool) preg_match('/\b(this month|month|monthly)\b/', $m);
        $wants30 = (bool) preg_match('/\b(30|thirty|last month)\b/', $m)
            || (str_contains($m, 'last') && str_contains($m, 'days'));

        // "today sale?" / "today's sales" / "sale today" even without the word "sale" if message is short
        if ($wantsToday && ($salesKeywords || preg_match('/\b(total|amount|summary|report)\b/', $m) || strlen($m) <= 24)) {
            $s = $data['sales']['today'];
            $lines = [
                "Here's today's sales (".$data['as_of'].", {$data['timezone']}):",
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
            $s = $data['sales']['yesterday'];

            return [
                'success' => true,
                'reply' => $this->formatSalesBlock("Yesterday's sales", $s, $symbol)."\n"
                    .$this->formatSalesBlock("Today so far", $data['sales']['today'], $symbol),
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_sales'),
            ];
        }

        if ($wantsWeek && ($salesKeywords || str_contains($m, 'down') || str_contains($m, 'why') || strlen($m) <= 40)) {
            $week = $data['sales']['this_week'];
            $today = $data['sales']['today'];
            $prev30 = $data['sales']['last_30_days'];
            $reply = $this->formatSalesBlock('This week (Sun–now)', $week, $symbol)."\n"
                .$this->formatSalesBlock('Today', $today, $symbol)."\n"
                .$this->formatSalesBlock('Last 30 days', $prev30, $symbol);
            if (str_contains($m, 'down') || str_contains($m, 'why')) {
                $reply .= "\nTip: Compare week-to-date with last 30-day totals and review top products in Reports for deeper trends.";
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
                'reply' => $this->formatSalesBlock('This month', $data['sales']['this_month'], $symbol)."\n"
                    .$this->formatSalesBlock('Today', $data['sales']['today'], $symbol)."\n"
                    .$this->formatSalesBlock('Last 30 days', $data['sales']['last_30_days'], $symbol),
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_sales'),
            ];
        }

        // Generic sales (defaults to today + context, not only 30-day)
        if (
            $salesKeywords
            || preg_match('/\b(sales|sale|revenue|how much did we sell|how much sold)\b/', $m)
        ) {
            $lines = [
                "Sales summary from {$name}:",
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

        // ---- Inventory ----
        if (
            str_contains($m, 'low stock') || str_contains($m, 'out of stock')
            || (str_contains($m, 'stock') && (str_contains($m, 'show') || str_contains($m, 'list') || str_contains($m, 'alert')))
            || str_contains($m, 'inventory')
            || str_contains($m, 'reorder')
        ) {
            $lines = ["{$name} inventory snapshot:"];
            $lines[] = 'Products: '.$data['inventory']['product_count']
                .' · Out of stock: '.$data['inventory']['out_of_stock_count']
                .' · Low stock: '.$data['inventory']['low_stock_count'];
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
            $reply = "{$name} invoices:\n"
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
            $reply = "{$name} customer & pipeline:\n"
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
                'reply' => "I'm {$name}. Try asking:\n"
                    ."• today sale / today's sales\n"
                    ."• this week sales / this month sales\n"
                    ."• show low stock\n"
                    ."• list unpaid invoices\n"
                    ."• how many customers\n"
                    .'• how is my pipeline looking?',
                'tool' => null,
            ];
        }

        return null;
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
            // Last-resort business overview without API
            $symbol = session('currency')['symbol'] ?? '$';
            $data = $this->insights->forBusiness($business_id);
            $overview = "I'm ".self::ASSISTANT_NAME.'. Here is a quick snapshot (OpenAI not configured for free-form chat):'."\n"
                .$this->formatSalesBlock('Today', $data['sales']['today'], $symbol)."\n"
                .$this->formatSalesBlock('Last 30 days', $data['sales']['last_30_days'], $symbol)."\n"
                .'Low stock items: '.$data['inventory']['low_stock_count']
                .' · Open invoices: '.$data['invoices']['open'];

            return [
                'success' => true,
                'reply' => $overview,
                'tool' => $tool,
            ];
        }

        $snapshot = $this->insights->snapshotText($business_id);

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are JapsAI (never say you have no name — your name is JapsAI), '
                    .'the business assistant for this POS / wholesale system. '
                    .'Answer using ONLY the JSON business data snapshot provided. Do not invent numbers. '
                    .'When the user asks about "today", use sales.today and today_invoices. '
                    .'When they ask about week / month / 30 days, use the matching period in the snapshot. '
                    .'If a period total is 0, say so clearly (do not claim no data exists if the field is present with 0). '
                    .'Be concise, friendly, and practical. Use short bullet lists when helpful. '
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
                    'temperature' => 0.2,
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
