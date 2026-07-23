<?php

namespace Modules\Crm\Http\Controllers;

use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Crm\Entities\CallLog;
use Modules\Crm\Entities\CrmContact;

class CallLogController extends Controller
{
    public function __construct(protected Util $commonUtil)
    {
    }

    public function index()
    {
        if (! auth()->user()->can('crm.call_log') && ! auth()->user()->can('crm.access') && ! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $contact_id = request()->get('contact_id');

        $query = CallLog::where('business_id', $business_id)
            ->with(['contact', 'createdBy'])
            ->latest('called_on');

        if (! empty($contact_id)) {
            $query->where('contact_id', $contact_id);
        }

        $call_logs = $query->paginate(25);
        $contacts = CrmContact::where('business_id', $business_id)
            ->whereIn('type', ['lead', 'customer', 'both'])
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('crm::call_logs.index', compact('call_logs', 'contacts', 'contact_id'));
    }

    public function store(Request $request)
    {
        if (! auth()->user()->can('crm.call_log') && ! auth()->user()->can('crm.access') && ! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'contact_id' => 'required|integer',
            'subject' => 'nullable|string|max:255',
        ]);

        $business_id = $request->session()->get('user.business_id');

        $called_on = $request->input('called_on');
        if (! empty($called_on)) {
            try {
                $called_on = $this->commonUtil->uf_date($called_on, true);
            } catch (\Throwable $e) {
                $called_on = date('Y-m-d H:i:s', strtotime($called_on));
            }
        } else {
            $called_on = now();
        }

        CallLog::create([
            'business_id' => $business_id,
            'contact_id' => $request->input('contact_id'),
            'created_by' => $request->session()->get('user.id'),
            'subject' => $request->input('subject'),
            'duration' => $request->input('duration'),
            'description' => $request->input('description'),
            'called_on' => $called_on,
        ]);

        return redirect()->route('crm.call-logs.index')->with('status', [
            'success' => 1,
            'msg' => __('crm::lang.call_log_added'),
        ]);
    }

    public function destroy($id)
    {
        if (! auth()->user()->can('crm.call_log') && ! auth()->user()->can('crm.access') && ! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        CallLog::where('business_id', $business_id)->findOrFail($id)->delete();

        return redirect()->back()->with('status', [
            'success' => 1,
            'msg' => __('crm::lang.call_log_deleted'),
        ]);
    }
}
