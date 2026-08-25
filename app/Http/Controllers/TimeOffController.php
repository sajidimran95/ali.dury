<?php

namespace App\Http\Controllers;

use App\TeamShift;
use App\TimeOffRequest;
use App\User;
use App\Utils\BusinessUtil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TimeOffController extends Controller
{
    protected $businessUtil;

    public function __construct(BusinessUtil $businessUtil)
    {
        $this->businessUtil = $businessUtil;
    }

    public function index(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $can_request = $this->canRequest($business_id);
        $can_manage = $this->canManage($business_id);

        if (! $can_request && ! $can_manage) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->ajax()) {
            $query = TimeOffRequest::with(['user', 'reviewer'])
                ->where('business_id', $business_id);

            if (! $can_manage) {
                $query->where('user_id', auth()->id());
            } elseif ($request->filled('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            $query->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected', 'cancelled')")
                ->orderBy('starts_at', 'desc');

            return DataTables::of($query)
                ->addColumn('employee', function ($row) {
                    return e($this->userDisplayName($row->user));
                })
                ->editColumn('type', function ($row) {
                    return e(__('team_schedule.type_'.$row->type));
                })
                ->editColumn('starts_at', function ($row) {
                    return $this->formatDateTime($row->starts_at);
                })
                ->editColumn('ends_at', function ($row) {
                    return $this->formatDateTime($row->ends_at);
                })
                ->editColumn('status', function ($row) {
                    $map = [
                        'pending' => 'pl-status--low',
                        'approved' => 'pl-status--ok',
                        'rejected' => 'pl-status--inactive',
                        'cancelled' => 'pl-status--inactive',
                    ];
                    $cls = $map[$row->status] ?? 'pl-status--inactive';

                    return '<span class="pl-status '.$cls.'"><span class="pl-status-dot"></span>'
                        .e(__('team_schedule.status_'.$row->status)).'</span>';
                })
                ->addColumn('action', function ($row) use ($can_manage) {
                    $html = '';
                    if ($can_manage && $row->status === 'pending') {
                        $html .= '<button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-success to-approve" data-id="'.$row->id.'">'.e(__('team_schedule.approve')).'</button> ';
                        $html .= '<button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-error to-reject" data-id="'.$row->id.'">'.e(__('team_schedule.reject')).'</button> ';
                    }
                    if ((int) $row->user_id === (int) auth()->id() && $row->status === 'pending') {
                        $html .= '<button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline to-cancel" data-id="'.$row->id.'">'.e(__('team_schedule.cancel_request')).'</button>';
                    }

                    return $html;
                })
                ->rawColumns(['status', 'action', 'employee'])
                ->make(true);
        }

        $users = [];
        if ($can_manage) {
            $users = User::where('business_id', $business_id)
                ->select('id', 'first_name', 'last_name', 'username')
                ->orderBy('first_name')
                ->get()
                ->pluck('user_full_name', 'id');
        }

        $types = [];
        foreach (TimeOffRequest::TYPES as $t) {
            $types[$t] = __('team_schedule.type_'.$t);
        }

        return view('team_schedule.time_off', compact('can_request', 'can_manage', 'users', 'types'));
    }

    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        if (! $this->canRequest($business_id)) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'starts_at' => 'required|string',
            'ends_at' => 'required|string',
            'type' => 'required|in:'.implode(',', TimeOffRequest::TYPES),
            'reason' => 'nullable|string|max:2000',
        ]);

        try {
            $starts = $this->parseBusinessDateTime($request->input('starts_at'));
            $ends = $this->parseBusinessDateTime($request->input('ends_at'));
            if ($ends->lte($starts)) {
                return ['success' => false, 'msg' => __('team_schedule.ends_before_starts')];
            }

            $user_id = auth()->id();
            if ($this->canManage($business_id) && $request->filled('user_id')) {
                $user_id = (int) $request->input('user_id');
                if (! User::where('business_id', $business_id)->where('id', $user_id)->exists()) {
                    return ['success' => false, 'msg' => __('messages.something_went_wrong')];
                }
            }

            $conflict = TeamShift::where('business_id', $business_id)
                ->where('user_id', $user_id)
                ->where('starts_at', '<', $ends)
                ->where('ends_at', '>', $starts)
                ->exists();

            TimeOffRequest::create([
                'business_id' => $business_id,
                'user_id' => $user_id,
                'starts_at' => $starts,
                'ends_at' => $ends,
                'type' => $request->input('type'),
                'status' => 'pending',
                'reason' => $request->input('reason'),
            ]);

            $msg = __('team_schedule.request_submitted');
            if ($conflict) {
                $msg .= ' '.__('team_schedule.conflict_warning');
            }

            return [
                'success' => true,
                'msg' => $msg,
                'conflict' => $conflict,
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());

            return ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }
    }

    public function review(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        if (! $this->canManage($business_id)) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        try {
            $row = TimeOffRequest::where('business_id', $business_id)->findOrFail($id);
            if ($row->status !== 'pending') {
                return ['success' => false, 'msg' => __('messages.something_went_wrong')];
            }

            $row->status = $request->input('status');
            $row->admin_note = $request->input('admin_note');
            $row->reviewed_by = auth()->id();
            $row->reviewed_at = Carbon::now();
            $row->save();

            return [
                'success' => true,
                'msg' => $row->status === 'approved'
                    ? __('team_schedule.request_approved')
                    : __('team_schedule.request_rejected'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());

            return ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }
    }

    public function cancel(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        if (! $this->canRequest($business_id)) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $row = TimeOffRequest::where('business_id', $business_id)->findOrFail($id);
            if ((int) $row->user_id !== (int) auth()->id() && ! $this->canManage($business_id)) {
                abort(403, 'Unauthorized action.');
            }
            if ($row->status !== 'pending') {
                return ['success' => false, 'msg' => __('messages.something_went_wrong')];
            }

            $row->status = 'cancelled';
            $row->save();

            return ['success' => true, 'msg' => __('team_schedule.request_cancelled')];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());

            return ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }
    }

    protected function canRequest($business_id): bool
    {
        return auth()->user()->can('time_off.request')
            || auth()->user()->can('attendance.clock')
            || auth()->user()->can('time_off.manage')
            || $this->businessUtil->is_admin(auth()->user(), $business_id);
    }

    protected function canManage($business_id): bool
    {
        return auth()->user()->can('time_off.manage')
            || $this->businessUtil->is_admin(auth()->user(), $business_id);
    }

    protected function userDisplayName($user): string
    {
        if (! $user) {
            return '—';
        }

        return $user->user_full_name
            ?: trim(($user->first_name ?? '').' '.($user->last_name ?? ''));
    }

    protected function businessTimezone(): string
    {
        return session('business.time_zone', config('app.timezone'));
    }

    protected function formatDateTime($datetime): string
    {
        $tz = $this->businessTimezone();
        $dateFormat = session('business.date_format', 'm/d/Y');
        $timeFormat = session('business.time_format', 'H:i');
        if ($timeFormat === '12') {
            $timeFormat = 'h:i A';
        }
        if (strpos($timeFormat, 'i') === false) {
            $timeFormat .= ':i';
        }

        return Carbon::parse($datetime)->setTimezone($tz)->format($dateFormat.' '.$timeFormat);
    }

    protected function parseBusinessDateTime(string $value): Carbon
    {
        $tz = $this->businessTimezone();
        $dateFormat = session('business.date_format', 'm/d/Y');
        $timeFormat = session('business.time_format', 'H:i');
        if ($timeFormat === '12') {
            $timeFormat = 'h:i A';
        }

        $formats = [
            $dateFormat.' '.$timeFormat,
            $dateFormat.' H:i',
            $dateFormat.' h:i A',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d\TH:i',
            'Y-m-d\TH:i:s',
        ];

        foreach ($formats as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, trim($value), $tz)
                    ->setTimezone(config('app.timezone'));
            } catch (\Exception $e) {
                // try next
            }
        }

        return Carbon::parse($value, $tz)->setTimezone(config('app.timezone'));
    }
}
