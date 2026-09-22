<?php

namespace Modules\AiAssistance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AiAssistance\Entities\AiAssistanceSetting;
use Modules\AiAssistance\Services\ChatMemoryService;
use Modules\AiAssistance\Services\ChatService;

class ChatController extends Controller
{
    public function __construct(protected ChatService $chat, protected ChatMemoryService $memory)
    {
    }

    public function send(Request $request)
    {
        if (! auth()->user()->can('ai.chat') && ! auth()->user()->can('ai.access')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'message' => 'required|string|max:4000',
        ]);

        $business_id = (int) $request->session()->get('user.business_id');
        $user_id = (int) $request->session()->get('user.id');
        $settings = AiAssistanceSetting::forBusiness($business_id);
        if (! $settings->is_enabled) {
            return response()->json(['success' => false, 'msg' => 'AI Assistant is disabled.'], 403);
        }

        $message = trim($request->input('message'));
        if ($message === '') {
            return response()->json(['success' => false, 'msg' => __('aiassistance::lang.empty_message')]);
        }

        $limit = (int) config('aiassistance.chat_history_limit', 40);
        $history = $this->memory->load($business_id, $user_id, 'japsai', $limit);
        if ($history === []) {
            $history = $request->session()->get('ai_chat_history_'.$business_id, []);
        }

        $result = $this->chat->reply($business_id, $message, $history);

        if (! empty($result['success'])) {
            $this->memory->append($business_id, $user_id, 'japsai', [
                ['role' => 'user', 'content' => $message],
                ['role' => 'assistant', 'content' => $result['reply'], 'tool' => $result['tool'] ?? null],
            ]);
            $history[] = ['role' => 'user', 'content' => $message];
            $history[] = [
                'role' => 'assistant',
                'content' => $result['reply'],
                'tool' => $result['tool'] ?? null,
            ];
            $request->session()->put('ai_chat_history_'.$business_id, array_slice($history, -$limit));
        }

        return response()->json($result);
    }

    public function clear(Request $request)
    {
        if (! auth()->user()->can('ai.chat') && ! auth()->user()->can('ai.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = (int) $request->session()->get('user.business_id');
        $user_id = (int) $request->session()->get('user.id');
        $this->memory->clear($business_id, $user_id, 'japsai');
        $request->session()->forget('ai_chat_history_'.$business_id);

        return redirect()->route('ai.dashboard', ['tab' => 'chat'])->with('status', [
            'success' => 1,
            'msg' => __('lang_v1.success'),
        ]);
    }
}
