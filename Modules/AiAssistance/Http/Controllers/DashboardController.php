<?php

namespace Modules\AiAssistance\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\AiAssistance\Entities\AiAssistanceSetting;
use Modules\AiAssistance\Services\ChatMemoryService;
use Modules\AiAssistance\Services\InsightsService;

class DashboardController extends Controller
{
    public function __construct(protected InsightsService $insights, protected ChatMemoryService $memory)
    {
    }

    public function index()
    {
        if (! auth()->user()->can('ai.access') && ! auth()->user()->can('ai.chat')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = (int) request()->session()->get('user.business_id');
        $settings = AiAssistanceSetting::forBusiness($business_id);

        if (! $settings->is_enabled) {
            return redirect()->route('ai.settings')->with('status', [
                'success' => 0,
                'msg' => 'AI Assistant is disabled. Enable it in settings.',
            ]);
        }

        $insights = $this->insights->forBusiness($business_id);
        $tab = request()->get('tab', 'insights');
        $user_id = (int) request()->session()->get('user.id');
        $chat_history = $this->memory->load($business_id, $user_id, 'japsai');
        if ($chat_history === []) {
            $chat_history = session('ai_chat_history_'.$business_id, []);
        }
        $has_api_key = ! empty($settings->resolveApiKey());

        return view('aiassistance::dashboard', compact(
            'insights',
            'tab',
            'chat_history',
            'has_api_key',
            'settings'
        ));
    }

    public function panel()
    {
        try {
            if (! auth()->user()->can('ai.access') && ! auth()->user()->can('ai.chat')) {
                abort(403, 'Unauthorized action.');
            }

            $business_id = (int) request()->session()->get('user.business_id');
            $settings = AiAssistanceSetting::forBusiness($business_id);
            if (! $settings->is_enabled) {
                return response('AI Assistant is disabled.', 403);
            }

            $insights = $this->insights->forBusiness($business_id);
            $tab = request()->get('tab', 'insights');
            $user_id = (int) request()->session()->get('user.id');
            $chat_history = $this->memory->load($business_id, $user_id, 'japsai');
            if ($chat_history === []) {
                $chat_history = session('ai_chat_history_'.$business_id, []);
            }
            $has_api_key = ! empty($settings->resolveApiKey());
            $is_modal = true;

            return view('aiassistance::partials.panel', compact(
                'insights',
                'tab',
                'chat_history',
                'has_api_key',
                'settings',
                'is_modal'
            ));
        } catch (\Throwable $e) {
            \Log::error('JapsAI panel: '.$e->getMessage());

            return response(
                '<div style="padding:24px;"><p class="text-danger">JapsAI could not load. '.$e->getMessage().'</p></div>',
                200
            );
        }
    }
}