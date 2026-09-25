<?php

namespace Modules\AiAssistance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AiAssistance\Entities\AiAssistanceSetting;

class SettingsController extends Controller
{
    public function index()
    {
        if (! auth()->user()->can('ai.settings') && ! auth()->user()->can('ai.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = (int) request()->session()->get('user.business_id');
        $settings = AiAssistanceSetting::forBusiness($business_id);

        return view('aiassistance::settings', compact('settings'));
    }

    public function update(Request $request)
    {
        if (! auth()->user()->can('ai.settings') && ! auth()->user()->can('ai.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = (int) $request->session()->get('user.business_id');
        $settings = AiAssistanceSetting::forBusiness($business_id);

        $settings->is_enabled = $request->boolean('is_enabled');
        $settings->openai_model = $request->input('openai_model') ?: config('aiassistance.openai_model');

        $key = trim((string) $request->input('openai_api_key'));
        // Keep existing key if user leaves the masked field blank intentionally via checkbox clear
        if ($request->boolean('clear_api_key')) {
            $settings->openai_api_key = null;
        } elseif ($key !== '' && $key !== '********') {
            $settings->openai_api_key = $key;
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('ai_assistance_settings', 'match_tolerance_pct')) {
            $settings->match_tolerance_pct = (float) $request->input('match_tolerance_pct', 2);
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('ai_assistance_settings', 'digest_enabled')) {
            $settings->digest_enabled = $request->boolean('digest_enabled');
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('ai_assistance_settings', 'digest_email')) {
            $settings->digest_email = $request->input('digest_email') ?: null;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('ai_assistance_settings', 'alert_email_enabled')) {
            $settings->alert_email_enabled = $request->boolean('alert_email_enabled');
        }

        $settings->save();

        return redirect()->back()->with('status', [
            'success' => 1,
            'msg' => __('aiassistance::lang.settings_updated'),
        ]);
    }
}
