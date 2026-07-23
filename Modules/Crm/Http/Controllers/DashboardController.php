<?php

namespace Modules\Crm\Http\Controllers;

use App\Utils\ModuleUtil;
use Illuminate\Routing\Controller;
use Modules\Crm\Entities\CrmContact;
use Modules\Crm\Entities\Schedule;

class DashboardController extends Controller
{
    public function __construct(protected ModuleUtil $moduleUtil)
    {
    }

    public function index()
    {
        if (! auth()->user()->can('crm.access') && ! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $total_leads = CrmContact::where('business_id', $business_id)->leadsOnly()->count();
        $total_customers = CrmContact::where('business_id', $business_id)->customersOnly()->count();
        $due_follow_ups = Schedule::where('business_id', $business_id)
            ->where('status', 'open')
            ->whereNotNull('start_datetime')
            ->where('start_datetime', '<=', now())
            ->count();
        $open_follow_ups = Schedule::where('business_id', $business_id)
            ->where('status', 'open')
            ->count();

        $recent_leads = CrmContact::where('business_id', $business_id)
            ->leadsOnly()
            ->latest('id')
            ->limit(10)
            ->get();

        $stages = CrmContact::getLifeStagesDropdown($business_id);
        $pipeline_summary = [];
        foreach ($stages as $stageId => $stageName) {
            $pipeline_summary[] = [
                'name' => $stageName,
                'count' => CrmContact::where('business_id', $business_id)
                    ->leadsOnly()
                    ->where('crm_life_stage', $stageId)
                    ->count(),
            ];
        }
        $no_stage = CrmContact::where('business_id', $business_id)
            ->leadsOnly()
            ->where(function ($q) {
                $q->whereNull('crm_life_stage')->orWhere('crm_life_stage', '');
            })
            ->count();
        if ($no_stage > 0) {
            $pipeline_summary[] = ['name' => __('crm::lang.no_stage'), 'count' => $no_stage];
        }

        return view('crm::dashboard.index', compact(
            'total_leads',
            'total_customers',
            'due_follow_ups',
            'open_follow_ups',
            'recent_leads',
            'pipeline_summary'
        ));
    }
}
