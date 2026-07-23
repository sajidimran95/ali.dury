<?php

namespace Modules\AiAssistance\Services;

use Illuminate\Support\Facades\Http;
use Modules\AiAssistance\Entities\AiAssistanceSetting;

class ChatService
{
    public function __construct(protected InsightsService $insights)
    {
    }

    public function detectTool(string $message): ?string
    {
        $m = strtolower($message);
        if (str_contains($m, 'stock') || str_contains($m, 'inventory') || str_contains($m, 'reorder')) {
            return __('aiassistance::lang.tool_lookup_inventory');
        }
        if (str_contains($m, 'invoice') || str_contains($m, 'unpaid') || str_contains($m, 'overdue') || str_contains($m, 'due')) {
            return __('aiassistance::lang.tool_lookup_invoices');
        }
        if (str_contains($m, 'sales') || str_contains($m, 'sell') || str_contains($m, 'revenue') || str_contains($m, 'week')) {
            return __('aiassistance::lang.tool_lookup_sales');
        }
        if (str_contains($m, 'pipeline') || str_contains($m, 'lead')) {
            return __('aiassistance::lang.tool_lookup_pipeline');
        }

        return null;
    }

    /**
     * Instant answers for common chips / questions using live snapshot (no API required).
     */
    public function localReply(int $business_id, string $message): ?array
    {
        $data = $this->insights->forBusiness($business_id);
        $symbol = session('currency')['symbol'] ?? '';
        $m = strtolower($message);
        $tool = $this->detectTool($message);

        if (str_contains($m, 'low stock') || (str_contains($m, 'stock') && str_contains($m, 'show'))) {
            $lines = ["Here are the low stock / attention items:"];
            $items = $data['inventory']['lowest'];
            if (empty($items)) {
                $lines[] = 'No stock alerts right now.';
            } else {
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

        if (str_contains($m, 'unpaid') || (str_contains($m, 'invoice') && (str_contains($m, 'list') || str_contains($m, 'overdue')))) {
            $inv = $data['invoices'];
            $reply = "Open invoices: {$inv['open']}\n"
                ."Overdue: {$inv['overdue']}\n"
                ."Outstanding: {$symbol}".number_format($inv['outstanding'], 2);
            if (! empty($inv['oldest'])) {
                $reply .= "\nOldest: {$inv['oldest']['invoice_no']} ({$symbol}".number_format($inv['oldest']['amount'], 2).')';
            }

            return [
                'success' => true,
                'reply' => $reply,
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_invoices'),
            ];
        }

        if (str_contains($m, 'pipeline') || str_contains($m, 'lead')) {
            $reply = $data['sales']['leads'].' active leads in the pipeline.'."\n"
                .$data['sales']['customers'].' customers on file.';

            return [
                'success' => true,
                'reply' => $reply,
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_pipeline'),
            ];
        }

        if (str_contains($m, 'sales') && (str_contains($m, 'week') || str_contains($m, 'down') || str_contains($m, 'why'))) {
            $s = $data['sales'];
            $reply = "Last 30 days sales: {$symbol}".number_format($s['total'], 2)
                ." across {$s['order_count']} orders.\n"
                .'Average order value: '.$symbol.number_format($s['aov'], 2)."\n"
                .'Compare with prior periods in Reports for a deeper trend.';

            return [
                'success' => true,
                'reply' => $reply,
                'tool' => $tool ?: __('aiassistance::lang.tool_lookup_sales'),
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
            return [
                'success' => false,
                'msg' => __('aiassistance::lang.no_api_key'),
                'tool' => $tool,
            ];
        }

        $snapshot = $this->insights->snapshotText($business_id);

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a helpful business assistant for a POS / wholesale system. '
                    .'Answer using ONLY the business data snapshot provided. Do not invent numbers. '
                    .'Be concise. Use short bullet lists when helpful. '
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
                    'temperature' => 0.3,
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
