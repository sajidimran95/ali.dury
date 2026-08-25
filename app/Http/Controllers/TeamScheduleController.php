<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\EmployeeAttendance;
use App\TeamMemberColor;
use App\TeamShift;
use App\TimeOffRequest;
use App\User;
use App\Utils\BusinessUtil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TeamScheduleController extends Controller
{
    protected $businessUtil;

    public function __construct(BusinessUtil $businessUtil)
    {
        $this->businessUtil = $businessUtil;
    }

    /**
     * Shared calendar (shifts + approved time off + attendance).
     */
    public function calendar(Request $request)
    {
        $this->authorizeScheduleView();

        $business_id = $request->session()->get('user.business_id');
        $can_view_all = $this->canViewAll($business_id);
        $can_manage = $this->canManageSchedule($business_id);

        $users = [];
        $user_colors = [];
        if ($can_view_all) {
            $userModels = User::where('business_id', $business_id)
                ->select('id', 'first_name', 'last_name', 'username')
                ->orderBy('first_name')
                ->get();
            $users = $userModels->pluck('user_full_name', 'id');
            foreach ($userModels as $u) {
                $user_colors[$u->id] = TeamMemberColor::colorFor((int) $business_id, (int) $u->id);
            }
        } else {
            $uid = auth()->user()->id;
            $user_colors[$uid] = TeamMemberColor::colorFor((int) $business_id, (int) $uid);
        }

        return view('team_schedule.calendar', compact('users', 'can_view_all', 'can_manage', 'user_colors'));
    }

    /**
     * Merged FullCalendar feed.
     */
    public function calendarEvents(Request $request)
    {
        $this->authorizeScheduleView();

        $business_id = $request->session()->get('user.business_id');
        $can_view_all = $this->canViewAll($business_id);
        $tz = $this->businessTimezone();

        $start = $request->filled('start')
            ? Carbon::parse($request->input('start'), $tz)->startOfDay()
            : Carbon::now($tz)->startOfMonth();
        $end = $request->filled('end')
            ? Carbon::parse($request->input('end'), $tz)->endOfDay()
            : Carbon::now($tz)->endOfMonth();

        $startUtc = $start->copy()->setTimezone(config('app.timezone'));
        $endUtc = $end->copy()->setTimezone(config('app.timezone'));

        $userFilter = null;
        if (! $can_view_all) {
            $userFilter = (int) auth()->user()->id;
        } elseif ($request->filled('user_id')) {
            $userFilter = (int) $request->input('user_id');
        }

        $events = [];

        // Attendance (reuse same shape as AttendanceController)
        $attQuery = EmployeeAttendance::with(['user', 'breaks'])
            ->where('business_id', $business_id)
            ->where('clock_in_at', '<=', $endUtc)
            ->where(function ($q) use ($startUtc) {
                $q->whereNull('clock_out_at')
                    ->orWhere('clock_out_at', '>=', $startUtc);
            });
        if ($userFilter) {
            $attQuery->where('user_id', $userFilter);
        }
        $now = Carbon::now();
        foreach ($attQuery->orderBy('clock_in_at')->get() as $row) {
            $name = $this->userDisplayName($row->user);
            $color = TeamMemberColor::colorFor((int) $business_id, (int) $row->user_id);
            $breakColor = TeamMemberColor::softColorFor((int) $business_id, (int) $row->user_id);
            $in = $row->clock_in_at;
            $out = $row->clock_out_at ?: $now;
            if (! $in) {
                continue;
            }
            $events[] = [
                'id' => 'work_'.$row->id,
                'title' => $name,
                'start' => $in->toIso8601String(),
                'end' => $out->toIso8601String(),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => '#ffffff',
                'className' => 'ts-fc-work',
                'extendedProps' => ['kind' => 'work', 'user_id' => $row->user_id],
            ];
            foreach ($row->breaks as $break) {
                if (! $break->break_start_at) {
                    continue;
                }
                $bEnd = $break->break_end_at ?: $now;
                $events[] = [
                    'id' => 'break_'.$break->id,
                    'title' => $name.' · '.__('attendance.break_time'),
                    'start' => $break->break_start_at->toIso8601String(),
                    'end' => $bEnd->toIso8601String(),
                    'backgroundColor' => $breakColor,
                    'borderColor' => $color,
                    'textColor' => '#1e293b',
                    'className' => 'ts-fc-break',
                    'extendedProps' => ['kind' => 'break', 'user_id' => $row->user_id],
                ];
            }
        }

        // Planned shifts
        $shiftQuery = TeamShift::with('user')
            ->where('business_id', $business_id)
            ->where('starts_at', '<=', $endUtc)
            ->where('ends_at', '>=', $startUtc);
        if ($userFilter) {
            $shiftQuery->where('user_id', $userFilter);
        }
        foreach ($shiftQuery->get() as $shift) {
            $name = $this->userDisplayName($shift->user);
            $color = TeamMemberColor::colorFor((int) $business_id, (int) $shift->user_id);
            $title = $shift->title ? $name.' · '.$shift->title : $name.' · '.__('team_schedule.shift');
            $events[] = [
                'id' => 'shift_'.$shift->id,
                'title' => $title,
                'start' => $shift->starts_at->toIso8601String(),
                'end' => $shift->ends_at->toIso8601String(),
                'backgroundColor' => 'transparent',
                'borderColor' => $color,
                'textColor' => $color,
                'className' => 'ts-fc-shift',
                'extendedProps' => [
                    'kind' => 'shift',
                    'shift_id' => $shift->id,
                    'user_id' => $shift->user_id,
                    'notes' => $shift->notes,
                ],
            ];
        }

        // Approved time off
        $offQuery = TimeOffRequest::with('user')
            ->where('business_id', $business_id)
            ->where('status', 'approved')
            ->where('starts_at', '<=', $endUtc)
            ->where('ends_at', '>=', $startUtc);
        if ($userFilter) {
            $offQuery->where('user_id', $userFilter);
        }
        foreach ($offQuery->get() as $off) {
            $name = $this->userDisplayName($off->user);
            $color = TeamMemberColor::softColorFor((int) $business_id, (int) $off->user_id);
            $border = TeamMemberColor::colorFor((int) $business_id, (int) $off->user_id);
            $typeLabel = __('team_schedule.type_'.$off->type);
            $events[] = [
                'id' => 'timeoff_'.$off->id,
                'title' => $name.' · '.$typeLabel,
                'start' => $off->starts_at->toIso8601String(),
                'end' => $off->ends_at->toIso8601String(),
                'backgroundColor' => $color,
                'borderColor' => $border,
                'textColor' => '#1e293b',
                'className' => 'ts-fc-timeoff',
                'extendedProps' => [
                    'kind' => 'time_off',
                    'user_id' => $off->user_id,
                    'type' => $off->type,
                ],
            ];
        }

        return response()->json($events);
    }

    /**
     * Shifts list + DataTables.
     */
    public function shifts(Request $request)
    {
        $this->authorizeScheduleView();

        $business_id = $request->session()->get('user.business_id');
        $can_manage = $this->canManageSchedule($business_id);
        $can_view_all = $this->canViewAll($business_id);

        if ($request->ajax()) {
            $query = TeamShift::with(['user', 'location'])
                ->where('business_id', $business_id);

            if (! $can_view_all) {
                $query->where('user_id', auth()->user()->id);
            } elseif ($request->filled('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $start = Carbon::createFromFormat('Y-m-d', $request->input('start_date'))->startOfDay();
                $end = Carbon::createFromFormat('Y-m-d', $request->input('end_date'))->endOfDay();
                $query->where('starts_at', '<=', $end)->where('ends_at', '>=', $start);
            }

            $query->orderBy('starts_at', 'desc');

            return DataTables::of($query)
                ->addColumn('employee', function ($row) {
                    return e($this->userDisplayName($row->user));
                })
                ->addColumn('color_swatch', function ($row) use ($business_id) {
                    $color = TeamMemberColor::colorFor((int) $business_id, (int) $row->user_id);

                    return '<span class="att-color-dot" style="background:'.$color.'"></span>';
                })
                ->editColumn('starts_at', function ($row) {
                    return $this->formatDateTime($row->starts_at);
                })
                ->editColumn('ends_at', function ($row) {
                    return $this->formatDateTime($row->ends_at);
                })
                ->addColumn('location_name', function ($row) {
                    return e(optional($row->location)->name ?: '—');
                })
                ->addColumn('action', function ($row) use ($can_manage) {
                    if (! $can_manage) {
                        return '';
                    }

                    return '<button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline ts-edit-shift" data-id="'.$row->id.'"><i class="fa fa-edit"></i></button> '
                        .'<button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-error tw-dw-btn-outline ts-delete-shift" data-id="'.$row->id.'"><i class="fa fa-trash"></i></button>';
                })
                ->rawColumns(['color_swatch', 'action', 'employee'])
                ->make(true);
        }

        $users = User::where('business_id', $business_id)
            ->select('id', 'first_name', 'last_name', 'username')
            ->orderBy('first_name')
            ->get()
            ->pluck('user_full_name', 'id');

        $locations = BusinessLocation::forDropdown($business_id, false, false, true, true);

        return view('team_schedule.shifts', compact('users', 'locations', 'can_manage', 'can_view_all'));
    }

    public function storeShift(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        if (! $this->canManageSchedule($business_id)) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'user_id' => 'required|integer',
            'starts_at' => 'required|string',
            'ends_at' => 'required|string',
            'title' => 'nullable|string|max:191',
            'notes' => 'nullable|string|max:2000',
            'location_id' => 'nullable|integer',
        ]);

        try {
            $starts = $this->parseBusinessDateTime($request->input('starts_at'));
            $ends = $this->parseBusinessDateTime($request->input('ends_at'));
            if ($ends->lte($starts)) {
                return ['success' => false, 'msg' => __('team_schedule.ends_before_starts')];
            }

            $userOk = User::where('business_id', $business_id)->where('id', $request->input('user_id'))->exists();
            if (! $userOk) {
                return ['success' => false, 'msg' => __('messages.something_went_wrong')];
            }

            $shift = TeamShift::create([
                'business_id' => $business_id,
                'user_id' => (int) $request->input('user_id'),
                'location_id' => $request->input('location_id') ?: null,
                'starts_at' => $starts,
                'ends_at' => $ends,
                'title' => $request->input('title'),
                'notes' => $request->input('notes'),
                'created_by' => auth()->id(),
            ]);

            return [
                'success' => true,
                'msg' => __('team_schedule.shift_saved'),
                'data' => $this->shiftToArray($shift),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());

            return ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }
    }

    public function showShift(Request $request, $id)
    {
        $this->authorizeScheduleView();
        $business_id = $request->session()->get('user.business_id');
        $shift = TeamShift::where('business_id', $business_id)->findOrFail($id);

        if (! $this->canViewAll($business_id) && (int) $shift->user_id !== (int) auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return ['success' => true, 'data' => $this->shiftToArray($shift)];
    }

    public function updateShift(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        if (! $this->canManageSchedule($business_id)) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'user_id' => 'required|integer',
            'starts_at' => 'required|string',
            'ends_at' => 'required|string',
            'title' => 'nullable|string|max:191',
            'notes' => 'nullable|string|max:2000',
            'location_id' => 'nullable|integer',
        ]);

        try {
            $shift = TeamShift::where('business_id', $business_id)->findOrFail($id);
            $starts = $this->parseBusinessDateTime($request->input('starts_at'));
            $ends = $this->parseBusinessDateTime($request->input('ends_at'));
            if ($ends->lte($starts)) {
                return ['success' => false, 'msg' => __('team_schedule.ends_before_starts')];
            }

            $shift->update([
                'user_id' => (int) $request->input('user_id'),
                'location_id' => $request->input('location_id') ?: null,
                'starts_at' => $starts,
                'ends_at' => $ends,
                'title' => $request->input('title'),
                'notes' => $request->input('notes'),
            ]);

            return [
                'success' => true,
                'msg' => __('team_schedule.shift_updated'),
                'data' => $this->shiftToArray($shift->fresh()),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());

            return ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }
    }

    public function destroyShift(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        if (! $this->canManageSchedule($business_id)) {
            abort(403, 'Unauthorized action.');
        }

        try {
            TeamShift::where('business_id', $business_id)->where('id', $id)->delete();

            return ['success' => true, 'msg' => __('team_schedule.shift_deleted')];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());

            return ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }
    }

    /**
     * Team member color picker.
     */
    public function colors(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        if (! $this->canManageSchedule($business_id) && ! $this->canEditAttendance($business_id)) {
            abort(403, 'Unauthorized action.');
        }

        $users = User::where('business_id', $business_id)
            ->select('id', 'first_name', 'last_name', 'username')
            ->orderBy('first_name')
            ->get();

        $custom = TeamMemberColor::where('business_id', $business_id)
            ->pluck('color', 'user_id');

        $rows = [];
        foreach ($users as $u) {
            $rows[] = [
                'user_id' => $u->id,
                'name' => $u->user_full_name,
                'color' => $custom[$u->id] ?? TeamMemberColor::colorFor((int) $business_id, (int) $u->id),
                'is_custom' => isset($custom[$u->id]),
            ];
        }

        return view('team_schedule.colors', compact('rows'));
    }

    public function saveColors(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        if (! $this->canManageSchedule($business_id) && ! $this->canEditAttendance($business_id)) {
            abort(403, 'Unauthorized action.');
        }

        $colors = $request->input('colors', []);
        if (! is_array($colors)) {
            return ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }

        try {
            DB::beginTransaction();
            $validUserIds = User::where('business_id', $business_id)->pluck('id')->all();

            foreach ($colors as $userId => $hex) {
                $userId = (int) $userId;
                if (! in_array($userId, $validUserIds, true)) {
                    continue;
                }
                $hex = strtoupper(trim((string) $hex));
                if ($hex === '' || $hex === 'RESET') {
                    TeamMemberColor::where('business_id', $business_id)->where('user_id', $userId)->delete();
                    continue;
                }
                if (! preg_match('/^#[0-9A-F]{6}$/', $hex)) {
                    continue;
                }
                TeamMemberColor::updateOrCreate(
                    ['business_id' => $business_id, 'user_id' => $userId],
                    ['color' => $hex]
                );
            }
            DB::commit();

            return ['success' => true, 'msg' => __('team_schedule.colors_saved')];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());

            return ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }
    }

    protected function authorizeScheduleView(): void
    {
        $business_id = request()->session()->get('user.business_id');
        if (
            ! auth()->user()->can('schedule.view')
            && ! auth()->user()->can('schedule.manage')
            && ! auth()->user()->can('attendance.view')
            && ! auth()->user()->can('attendance.view_all')
            && ! $this->businessUtil->is_admin(auth()->user(), $business_id)
        ) {
            abort(403, 'Unauthorized action.');
        }
    }

    protected function canManageSchedule($business_id): bool
    {
        return auth()->user()->can('schedule.manage')
            || $this->businessUtil->is_admin(auth()->user(), $business_id);
    }

    protected function canEditAttendance($business_id): bool
    {
        return auth()->user()->can('attendance.edit')
            || $this->businessUtil->is_admin(auth()->user(), $business_id);
    }

    protected function canViewAll($business_id): bool
    {
        return auth()->user()->can('schedule.manage')
            || auth()->user()->can('attendance.view_all')
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
        // Force minutes
        if (strpos($timeFormat, 'i') === false) {
            $timeFormat .= ':i';
        }

        return Carbon::parse($datetime)->setTimezone($tz)->format($dateFormat.' '.$timeFormat);
    }

    protected function formatForInput($datetime): ?string
    {
        if (! $datetime) {
            return null;
        }
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

    protected function shiftToArray(TeamShift $shift): array
    {
        return [
            'id' => $shift->id,
            'user_id' => $shift->user_id,
            'location_id' => $shift->location_id,
            'starts_at' => $this->formatForInput($shift->starts_at),
            'ends_at' => $this->formatForInput($shift->ends_at),
            'title' => $shift->title,
            'notes' => $shift->notes,
        ];
    }
}
