<?php

namespace Modules\AiAssistance\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\AiAssistance\Entities\AiAssistanceSetting;
use Modules\AiAssistance\Services\InsightsService;

class DashboardController extends Controller
{
    public function __construct(protected InsightsService $insights)
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
        $chat_history = session('ai_chat_history_'.$business_id, []);
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
        if (! auth()->user()->can('ai.access') && ! auth()->user()->can('ai.chat')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = (int) request()->session()->get('user.business_id');
        $settings = AiAssistanceSetting::forBusiness($business_id);
        if (! $settings->is_enabled) {
            return response('AI Assistant is disabled.', 403);
        }

        $insights = $this->insights->forBusiness($business_id);
        // Default modal open to chat so users can type immediately
        $tab = request()->get('tab', 'chat');
        $chat_history = session('ai_chat_history_'.$business_id, []);
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
    }
}