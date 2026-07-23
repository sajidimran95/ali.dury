<?php

namespace Modules\AiAssistance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AiAssistance\Entities\AiAssistanceSetting;
use Modules\AiAssistance\Services\ChatService;

class ChatController extends Controller
{
    public function __construct(protected ChatService $chat)
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
        $settings = AiAssistanceSetting::forBusiness($business_id);
        if (! $settings->is_enabled) {
            return response()->json(['success' => false, 'msg' => 'AI Assistant is disabled.'], 403);
        }

        $message = trim($request->input('message'));
        if ($message === '') {
            return response()->json(['success' => false, 'msg' => __('aiassistance::lang.empty_message')]);
        }

        $session_key = 'ai_chat_history_'.$business_id;
        $history = $request->session()->get($session_key, []);
        $limit = (int) config('aiassistance.chat_history_limit', 20);

        $result = $this->chat->reply($business_id, $message, $history);

        if (! empty($result['success'])) {
            $history[] = ['role' => 'user', 'content' => $message];
            $history[] = [
                'role' => 'assistant',
                'content' => $result['reply'],
                'tool' => $result['tool'] ?? null,
            ];
            if (count($history) > $limit) {
                $history = array_slice($history, -$limit);
            }
            $request->session()->put($session_key, $history);
        }

        return response()->json($result);
    }

    public function clear(Request $request)
    {
        if (! auth()->user()->can('ai.chat') && ! auth()->user()->can('ai.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = (int) $request->session()->get('user.business_id');
        $request->session()->forget('ai_chat_history_'.$business_id);

        return redirect()->route('ai.dashboard', ['tab' => 'chat'])->with('status', [
            'success' => 1,
            'msg' => __('lang_v1.success'),
        ]);
    }
}
