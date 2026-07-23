<?php

namespace Modules\Crm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Crm\Entities\CrmContact;

class ConvertLeadController extends Controller
{
    public function convert(Request $request, $id)
    {
        if (! auth()->user()->can('crm.lead.update') && ! auth()->user()->can('crm.access') && ! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $lead = CrmContact::where('business_id', $business_id)->findOrFail($id);

        if ($lead->type !== 'lead') {
            $output = [
                'success' => false,
                'msg' => __('crm::lang.already_customer'),
            ];

            return redirect()->back()->with('status', $output);
        }

        $lead->type = 'customer';
        $lead->save();

        $output = [
            'success' => true,
            'msg' => __('crm::lang.converted_success'),
        ];

        return redirect()
            ->action([\App\Http\Controllers\ContactController::class, 'show'], [$lead->id])
            ->with('status', $output);
    }
}
