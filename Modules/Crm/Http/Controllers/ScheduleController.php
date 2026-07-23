<?php

namespace Modules\Crm\Http\Controllers;

use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Crm\Entities\CrmContact;
use Modules\Crm\Entities\Schedule;

class ScheduleController extends Controller
{
    public function __construct(protected Util $commonUtil)
    {
    }

    public function index()
    {
        if (! auth()->user()->can('crm.schedule') && ! auth()->user()->can('crm.access') && ! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $status = request()->get('status');

        $query = Schedule::where('business_id', $business_id)
            ->with(['contact', 'createdBy'])
            ->latest('start_datetime');

        if (in_array($status, ['open', 'completed'], true)) {
            $query->where('status', $status);
        }

        $schedules = $query->paginate(25);
        $contacts = CrmContact::where('business_id', $business_id)
            ->whereIn('type', ['lead', 'customer', 'both'])
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('crm::schedules.index', compact('schedules', 'contacts', 'status'));
    }

    public function store(Request $request)
    {
        if (! auth()->user()->can('crm.schedule') && ! auth()->user()->can('crm.access') && ! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'contact_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'start_datetime' => 'nullable|string',
        ]);

        $business_id = $request->session()->get('user.business_id');

        $start = $request->input('start_datetime');
        if (! empty($start)) {
            try {
                $start = $this->commonUtil->uf_date($start, true);
            } catch (\Throwable $e) {
                $start = date('Y-m-d H:i:s', strtotime($start));
            }
        }

        Schedule::create([
            'business_id' => $business_id,
            'contact_id' => $request->input('contact_id'),
            'created_by' => $request->session()->get('user.id'),
            'title' => $request->input('title'),
            'status' => 'open',
            'start_datetime' => $start ?: null,
            'description' => $request->input('description'),
        ]);

        return redirect()->route('crm.schedules.index')->with('status', [
            'success' => 1,
            'msg' => __('crm::lang.schedule_added'),
        ]);
    }

    public function complete($id)
    {
        if (! auth()->user()->can('crm.schedule') && ! auth()->user()->can('crm.access') && ! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $schedule = Schedule::where('business_id', $business_id)->findOrFail($id);
        $schedule->status = 'completed';
        $schedule->save();

        return redirect()->back()->with('status', [
            'success' => 1,
            'msg' => __('crm::lang.schedule_updated'),
        ]);
    }

    public function destroy($id)
    {
        if (! auth()->user()->can('crm.schedule') && ! auth()->user()->can('crm.access') && ! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        Schedule::where('business_id', $business_id)->findOrFail($id)->delete();

        return redirect()->back()->with('status', [
            'success' => 1,
            'msg' => __('crm::lang.schedule_deleted'),
        ]);
    }
}
