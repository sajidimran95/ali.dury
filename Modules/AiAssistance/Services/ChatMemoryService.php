<?php

namespace Modules\AiAssistance\Services;

use Illuminate\Support\Facades\Schema;
use Modules\AiAssistance\Entities\AiChatMessage;

class ChatMemoryService
{
    public function load(int $business_id, int $user_id, string $channel = 'japsai', int $limit = 40): array
    {
        if (! Schema::hasTable('ai_chat_messages')) {
            return [];
        }

        return AiChatMessage::where('business_id', $business_id)
            ->where('user_id', $user_id)
            ->where('channel', $channel)
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['role', 'content', 'tool'])
            ->reverse()
            ->values()
            ->map(fn ($m) => [
                'role' => $m->role,
                'content' => $m->content,
                'tool' => $m->tool,
            ])
            ->all();
    }

    public function append(int $business_id, int $user_id, string $channel, array $turns): void
    {
        if (! Schema::hasTable('ai_chat_messages')) {
            return;
        }

        foreach ($turns as $turn) {
            if (empty($turn['role']) || empty($turn['content'])) {
                continue;
            }
            AiChatMessage::create([
                'business_id' => $business_id,
                'user_id' => $user_id,
                'channel' => $channel,
                'role' => $turn['role'],
                'content' => (string) $turn['content'],
                'tool' => $turn['tool'] ?? null,
            ]);
        }

        $keep = 200;
        $ids = AiChatMessage::where('business_id', $business_id)
            ->where('user_id', $user_id)
            ->where('channel', $channel)
            ->orderByDesc('id')
            ->skip($keep)
            ->take(500)
            ->pluck('id');
        if ($ids->isNotEmpty()) {
            AiChatMessage::whereIn('id', $ids)->delete();
        }
    }

    public function clear(int $business_id, int $user_id, string $channel = 'japsai'): void
    {
        if (! Schema::hasTable('ai_chat_messages')) {
            return;
        }

        AiChatMessage::where('business_id', $business_id)
            ->where('user_id', $user_id)
            ->where('channel', $channel)
            ->delete();
    }
}
