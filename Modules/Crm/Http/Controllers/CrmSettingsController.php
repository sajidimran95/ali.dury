<?php

namespace Modules\Crm\Http\Controllers;

use App\Business;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CrmSettingsController extends Controller
{
    public function index()
    {
        if (! auth()->user()->can('crm.settings') && ! auth()->user()->can('crm.access') && ! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $business = Business::findOrFail($business_id);
        $crm_settings = ! empty($business->crm_settings)
            ? (is_array($business->crm_settings) ? $business->crm_settings : json_decode($business->crm_settings, true))
            : [];

        return view('crm::settings.index', compact('crm_settings'));
    }

    public function update(Request $request)
    {
        if (! auth()->user()->can('crm.settings') && ! auth()->user()->can('crm.access') && ! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $business = Business::findOrFail($business_id);

        $settings = [
            'enable_order_request' => $request->boolean('enable_order_request') ? 1 : 0,
        ];

        $business->crm_settings = json_encode($settings);
        $business->save();

        return redirect()->back()->with('status', [
            'success' => 1,
            'msg' => __('crm::lang.settings_updated'),
        ]);
    }
}
