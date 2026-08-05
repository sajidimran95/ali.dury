<?php

namespace Modules\Crm\Http\Controllers;

use App\User;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Crm\Entities\CrmContact;
use Yajra\DataTables\Facades\DataTables;

class LeadController extends Controller
{
    public function __construct(
        protected ModuleUtil $moduleUtil,
        protected ContactUtil $contactUtil,
        protected Util $commonUtil
    ) {
    }

    public function index()
    {
        if (! auth()->user()->can('crm.lead.view') && ! auth()->user()->can('crm.access') && ! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $leads = CrmContact::where('business_id', $business_id)
                ->leadsOnly()
                ->with('leadUsers')
                ->select('contacts.*');

            return DataTables::of($leads)
                ->addColumn('assigned', function ($row) {
                    return $row->leadUsers->map(fn ($u) => $u->user_full_name)->implode(', ') ?: __('crm::lang.unassigned');
                })
                ->editColumn('crm_source', function ($row) use ($business_id) {
                    static $sources = null;
                    $sources = $sources ?? CrmContact::getSourcesDropdown($business_id);

                    return $sources[$row->crm_source] ?? $row->crm_source;
                })
                ->editColumn('crm_life_stage', function ($row) use ($business_id) {
                    static $stages = null;
                    $stages = $stages ?? CrmContact::getLifeStagesDropdown($business_id);

                    return $stages[$row->crm_life_stage] ?? $row->crm_life_stage;
                })
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">';
                    $html .= '<a href="'.route('crm.leads.show', $row->id).'" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info">'.__('crm::lang.view').'</a> ';
                    if (auth()->user()->can('crm.lead.update') || auth()->user()->can('crm.access') || auth()->user()->can('superadmin')) {
                        $html .= '<a href="'.route('crm.leads.edit', $row->id).'" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary">'.__('crm::lang.edit').'</a> ';
                    }
                    if (auth()->user()->can('crm.lead.delete') || auth()->user()->can('crm.access') || auth()->user()->can('superadmin')) {
                        $html .= '<button data-href="'.route('crm.leads.destroy', $row->id).'" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-error delete-lead">'.__('crm::lang.delete').'</button>';
                    }
                    $html .= '</div>';

                    return $html;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $business_id = (int) $business_id;
        $lead_stats = $this->getLeadListStats($business_id);

        return view('crm::leads.index', compact('lead_stats'));
    }

    /**
     * KPI stats for leads list cards.
     */
    protected function getLeadListStats(int $businessId): array
    {
        $base = CrmContact::where('business_id', $businessId)->leadsOnly();

        $total = (int) (clone $base)->count();
        $withStage = (int) (clone $base)
            ->whereNotNull('crm_life_stage')
            ->where('crm_life_stage', '!=', '')
            ->count();
        $noStage = max(0, $total - $withStage);
        $assigned = (int) (clone $base)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('crm_lead_users')
                    ->whereColumn('crm_lead_users.contact_id', 'contacts.id');
            })
            ->count();
        $unassigned = max(0, $total - $assigned);
        $thisMonth = (int) (clone $base)
            ->whereBetween('created_at', [
                \Carbon\Carbon::now()->startOfMonth()->toDateTimeString(),
                \Carbon\Carbon::now()->endOfMonth()->toDateTimeString(),
            ])
            ->count();

        return [
            'total_leads' => $total,
            'with_stage' => $withStage,
            'no_stage' => $noStage,
            'assigned' => $assigned,
            'unassigned' => $unassigned,
            'this_month' => $thisMonth,
        ];
    }

    public function create()
    {
        if (! auth()->user()->can('crm.lead.create') && ! auth()->user()->can('crm.access') && ! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $sources = CrmContact::getSourcesDropdown($business_id);
        $life_stages = CrmContact::getLifeStagesDropdown($business_id);
        $users = User::forDropdown($business_id, false, false, false, true);

        return view('crm::leads.create', compact('sources', 'life_stages', 'users'));
    }

    public function store(Request $request)
    {
        if (! auth()->user()->can('crm.lead.create') && ! auth()->user()->can('crm.access') && ! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            $input = $request->only([
                'supplier_business_name', 'prefix', 'first_name', 'middle_name', 'last_name',
                'tax_number', 'mobile', 'landline', 'alternate_number', 'city', 'state', 'country',
                'address_line_1', 'address_line_2', 'zip_code', 'contact_id', 'email', 'position', 'dob',
                'crm_source', 'crm_life_stage',
            ]);

            $name_array = array_filter([
                $input['prefix'] ?? null,
                $input['first_name'] ?? null,
                $input['middle_name'] ?? null,
                $input['last_name'] ?? null,
            ]);

            $input['name'] = trim(implode(' ', $name_array));
            $input['type'] = 'lead';
            $input['contact_type'] = $request->input('contact_type_radio', 'individual');
            $input['business_id'] = $business_id;
            $input['created_by'] = $request->session()->get('user.id');
            $input['opening_balance'] = 0;

            if (! empty($input['dob'])) {
                $input['dob'] = $this->commonUtil->uf_date($input['dob']);
            }

            DB::beginTransaction();
            $output = $this->contactUtil->createNewContact($input);
            $contact = $output['data'];

            $crmContact = CrmContact::find($contact->id);
            $crmContact->crm_source = $request->input('crm_source') ?: null;
            $crmContact->crm_life_stage = $request->input('crm_life_stage') ?: null;
            $crmContact->save();

            if ($request->filled('user_id')) {
                $crmContact->leadUsers()->sync($request->input('user_id', []));
            }

            DB::commit();

            $output = [
                'success' => true,
                'msg' => __('crm::lang.lead_added'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        if ($request->ajax()) {
            return $output;
        }

        return redirect()->route('crm.leads.index')->with('status', $output);
    }

    public function show($id)
    {
        if (! auth()->user()->can('crm.lead.view') && ! auth()->user()->can('crm.access') && ! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        $lead = CrmContact::where('business_id', $business_id)
            ->leadsOnly()
            ->with('leadUsers')
            ->findOrFail($id);

        $sources = CrmContact::getSourcesDropdown($business_id);
        $life_stages = CrmContact::getLifeStagesDropdown($business_id);

        $notes = \App\DocumentAndNote::where('business_id', $business_id)
            ->where('notable_id', $lead->id)
            ->where('notable_type', \App\Contact::class)
            ->where(function ($query) use ($user_id) {
                $query->where('is_private', 0)
                    ->orWhere(function ($q) use ($user_id) {
                        $q->where('is_private', 1)->where('created_by', $user_id);
                    });
            })
            ->with('createdBy')
            ->latest()
            ->get();

        $schedules = \Modules\Crm\Entities\Schedule::where('business_id', $business_id)
            ->where('contact_id', $lead->id)
            ->with('createdBy')
            ->latest('start_datetime')
            ->get();

        $call_logs = \Modules\Crm\Entities\CallLog::where('business_id', $business_id)
            ->where('contact_id', $lead->id)
            ->with('createdBy')
            ->latest('called_on')
            ->get();

        $timeline = collect();

        $timeline->push([
            'type' => 'created',
            'at' => $lead->created_at,
            'title' => __('crm::lang.lead_created'),
            'body' => null,
            'by' => null,
            'meta' => null,
        ]);

        foreach ($notes as $note) {
            $timeline->push([
                'type' => 'note',
                'at' => $note->created_at,
                'title' => $note->heading ?: __('crm::lang.note'),
                'body' => $note->description,
                'by' => optional($note->createdBy)->user_full_name,
                'meta' => null,
            ]);
        }

        foreach ($schedules as $schedule) {
            $timeline->push([
                'type' => 'follow_up',
                'at' => $schedule->start_datetime ?: $schedule->created_at,
                'title' => $schedule->title,
                'body' => $schedule->description,
                'by' => optional($schedule->createdBy)->user_full_name,
                'meta' => $schedule->status,
            ]);
        }

        foreach ($call_logs as $log) {
            $timeline->push([
                'type' => 'call',
                'at' => $log->called_on ?: $log->created_at,
                'title' => $log->subject ?: __('crm::lang.call_log'),
                'body' => $log->description,
                'by' => optional($log->createdBy)->user_full_name,
                'meta' => $log->duration ? $log->duration.' '.__('crm::lang.min') : null,
            ]);
        }

        $timeline = $timeline->sortByDesc(function ($item) {
            return $item['at'] ? $item['at']->timestamp : 0;
        })->values();

        return view('crm::leads.show', compact(
            'lead',
            'sources',
            'life_stages',
            'timeline',
            'schedules',
            'call_logs'
        ));
    }

    public function edit($id)
    {
        if (! auth()->user()->can('crm.lead.update') && ! auth()->user()->can('crm.access') && ! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $contact = CrmContact::where('business_id', $business_id)
            ->leadsOnly()
            ->with('leadUsers')
            ->findOrFail($id);
        $sources = CrmContact::getSourcesDropdown($business_id);
        $life_stages = CrmContact::getLifeStagesDropdown($business_id);
        $users = User::forDropdown($business_id, false, false, false, true);

        return view('crm::leads.edit', compact('contact', 'sources', 'life_stages', 'users'));
    }

    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('crm.lead.update') && ! auth()->user()->can('crm.access') && ! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');
            $contact = CrmContact::where('business_id', $business_id)->leadsOnly()->findOrFail($id);

            $input = $request->only([
                'supplier_business_name', 'prefix', 'first_name', 'middle_name', 'last_name',
                'tax_number', 'mobile', 'landline', 'alternate_number', 'city', 'state', 'country',
                'address_line_1', 'address_line_2', 'zip_code', 'contact_id', 'email', 'position', 'dob',
            ]);

            $name_array = array_filter([
                $input['prefix'] ?? null,
                $input['first_name'] ?? null,
                $input['middle_name'] ?? null,
                $input['last_name'] ?? null,
            ]);

            $input['name'] = trim(implode(' ', $name_array));
            $input['contact_type'] = $request->input('contact_type_radio', $contact->contact_type);
            $input['type'] = 'lead';

            if (! empty($input['dob'])) {
                $input['dob'] = $this->commonUtil->uf_date($input['dob']);
            }

            DB::beginTransaction();
            $this->contactUtil->updateContact($input, $id, $business_id);

            $contact->crm_source = $request->input('crm_source') ?: null;
            $contact->crm_life_stage = $request->input('crm_life_stage') ?: null;
            $contact->save();

            if ($request->has('user_id')) {
                $contact->leadUsers()->sync($request->input('user_id', []));
            }

            DB::commit();

            $output = [
                'success' => true,
                'msg' => __('crm::lang.lead_updated'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        if ($request->ajax()) {
            return $output;
        }

        return redirect()->route('crm.leads.index')->with('status', $output);
    }

    public function destroy($id)
    {
        if (! auth()->user()->can('crm.lead.delete') && ! auth()->user()->can('crm.access') && ! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');
            $lead = CrmContact::where('business_id', $business_id)->leadsOnly()->findOrFail($id);
            $lead->leadUsers()->detach();
            $lead->delete();

            $output = [
                'success' => true,
                'msg' => __('crm::lang.lead_deleted'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
}
