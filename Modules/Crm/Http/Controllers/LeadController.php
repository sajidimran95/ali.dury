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

        return view('crm::leads.index');
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
        $lead = CrmContact::where('business_id', $business_id)
            ->leadsOnly()
            ->with('leadUsers')
            ->findOrFail($id);

        $sources = CrmContact::getSourcesDropdown($business_id);
        $life_stages = CrmContact::getLifeStagesDropdown($business_id);

        return view('crm::leads.show', compact('lead', 'sources', 'life_stages'));
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
