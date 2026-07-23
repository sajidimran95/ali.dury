<?php

namespace Modules\Crm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Crm\Entities\CrmContact;

class PipelineController extends Controller
{
    public function index()
    {
        if (! auth()->user()->can('crm.lead.view') && ! auth()->user()->can('crm.access') && ! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $stages = CrmContact::getLifeStagesDropdown($business_id);

        $columns = [];
        foreach ($stages as $stageId => $stageName) {
            $columns[$stageId] = [
                'id' => (string) $stageId,
                'name' => $stageName,
                'leads' => CrmContact::where('business_id', $business_id)
                    ->leadsOnly()
                    ->where('crm_life_stage', $stageId)
                    ->with('leadUsers')
                    ->orderBy('name')
                    ->get(),
            ];
        }

        $columns['__none__'] = [
            'id' => '',
            'name' => __('crm::lang.no_stage'),
            'leads' => CrmContact::where('business_id', $business_id)
                ->leadsOnly()
                ->where(function ($q) {
                    $q->whereNull('crm_life_stage')->orWhere('crm_life_stage', '');
                })
                ->with('leadUsers')
                ->orderBy('name')
                ->get(),
        ];

        return view('crm::pipeline.index', compact('columns'));
    }

    public function updateStage(Request $request, $id)
    {
        if (! auth()->user()->can('crm.lead.update') && ! auth()->user()->can('crm.access') && ! auth()->user()->can('superadmin')) {
            return response()->json(['success' => false, 'msg' => 'Unauthorized'], 403);
        }

        $business_id = $request->session()->get('user.business_id');
        $lead = CrmContact::where('business_id', $business_id)->leadsOnly()->findOrFail($id);

        $stage = $request->input('crm_life_stage');
        $lead->crm_life_stage = ($stage === '' || $stage === null || $stage === '__none__') ? null : $stage;
        $lead->save();

        return response()->json([
            'success' => true,
            'msg' => __('crm::lang.lead_updated'),
        ]);
    }
}
