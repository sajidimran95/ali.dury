<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\EmployeeAttendance;
use App\EmployeeAttendanceBreak;
use App\TeamMemberColor;
use App\User;
use App\Utils\BusinessUtil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AttendanceController extends Controller
{
    protected $businessUtil;

    public function __construct(BusinessUtil $businessUtil)
    {
        $this->businessUtil = $businessUtil;
    }

    /**
     * Current attendance status for the logged-in user (dashboard widget).
     */
    public function status()
    {
        $this->assertCanClock();

        return response()->json($this->statusPayload(auth()->user()->id));
    }

    /**
     * Clock in.
     */
    public function clockIn(Request $request)
    {
        $this->assertCanClock();

        try {
            $business_id = $request->session()->get('user.business_id');
            $user_id = auth()->user()->id;

            $geo = $this->assertClockGeofence($request, $business_id);
            if ($geo !== null) {
                $geo['status'] = $this->statusPayload($user_id);

                return $geo;
            }

            $open = EmployeeAttendance::where('business_id', $business_id)
                ->where('user_id', $user_id)
                ->whereIn('status', ['clocked_in', 'on_break'])
                ->first();

            if ($open) {
                return [
                    'success' => false,
                    'msg' => __('attendance.already_clocked_in'),
                    'status' => $this->statusPayload($user_id),
                ];
            }

            $coords = $this->extractClockCoords($request);

            EmployeeAttendance::create([
                'business_id' => $business_id,
                'user_id' => $user_id,
                'clock_in_at' => Carbon::now(),
                'status' => 'clocked_in',
                'notes' => $request->input('notes'),
                'clock_latitude' => $coords['latitude'],
                'clock_longitude' => $coords['longitude'],
                'clock_source' => $coords['source'],
            ]);

            return [
                'success' => true,
                'msg' => __('attendance.clocked_in_success'),
                'status' => $this->statusPayload($user_id),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());

            return [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
    }

    /**
     * Start break.
     */
    public function startBreak(Request $request)
    {
        $this->assertCanClock();

        try {
            $business_id = $request->session()->get('user.business_id');
            $user_id = auth()->user()->id;

            $attendance = EmployeeAttendance::where('business_id', $business_id)
                ->where('user_id', $user_id)
                ->where('status', 'clocked_in')
                ->first();

            if (! $attendance) {
                return [
                    'success' => false,
                    'msg' => __('attendance.must_clock_in_first'),
                    'status' => $this->statusPayload($user_id),
                ];
            }

            DB::beginTransaction();

            EmployeeAttendanceBreak::create([
                'attendance_id' => $attendance->id,
                'break_start_at' => Carbon::now(),
                'reason' => $request->input('reason'),
            ]);

            $attendance->status = 'on_break';
            $attendance->save();

            DB::commit();

            return [
                'success' => true,
                'msg' => __('attendance.break_started'),
                'status' => $this->statusPayload($user_id),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());

            return [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
    }

    /**
     * End break.
     */
    public function endBreak(Request $request)
    {
        $this->assertCanClock();

        try {
            $business_id = $request->session()->get('user.business_id');
            $user_id = auth()->user()->id;

            $attendance = EmployeeAttendance::where('business_id', $business_id)
                ->where('user_id', $user_id)
                ->where('status', 'on_break')
                ->first();

            if (! $attendance) {
                return [
                    'success' => false,
                    'msg' => __('attendance.not_on_break'),
                    'status' => $this->statusPayload($user_id),
                ];
            }

            DB::beginTransaction();

            $openBreak = EmployeeAttendanceBreak::where('attendance_id', $attendance->id)
                ->whereNull('break_end_at')
                ->latest('break_start_at')
                ->first();

            if ($openBreak) {
                $openBreak->break_end_at = Carbon::now();
                $openBreak->save();
            }

            $attendance->status = 'clocked_in';
            $attendance->save();

            DB::commit();

            return [
                'success' => true,
                'msg' => __('attendance.break_ended'),
                'status' => $this->statusPayload($user_id),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());

            return [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
    }

    /**
     * Clock out.
     */
    public function clockOut(Request $request)
    {
        $this->assertCanClock();

        try {
            $business_id = $request->session()->get('user.business_id');
            $user_id = auth()->user()->id;

            $geo = $this->assertClockGeofence($request, $business_id);
            if ($geo !== null) {
                $geo['status'] = $this->statusPayload($user_id);

                return $geo;
            }

            $attendance = EmployeeAttendance::where('business_id', $business_id)
                ->where('user_id', $user_id)
                ->whereIn('status', ['clocked_in', 'on_break'])
                ->first();

            if (! $attendance) {
                return [
                    'success' => false,
                    'msg' => __('attendance.not_clocked_in'),
                    'status' => $this->statusPayload($user_id),
                ];
            }

            DB::beginTransaction();

            $openBreak = EmployeeAttendanceBreak::where('attendance_id', $attendance->id)
                ->whereNull('break_end_at')
                ->latest('break_start_at')
                ->first();

            if ($openBreak) {
                $openBreak->break_end_at = Carbon::now();
                $openBreak->save();
            }

            $coords = $this->extractClockCoords($request);

            $attendance->clock_out_at = Carbon::now();
            $attendance->status = 'clocked_out';
            if ($request->filled('notes')) {
                $attendance->notes = $request->input('notes');
            }
            if ($coords['latitude'] !== null) {
                $attendance->clock_latitude = $coords['latitude'];
                $attendance->clock_longitude = $coords['longitude'];
                $attendance->clock_source = $coords['source'];
            }
            $attendance->save();

            DB::commit();

            return [
                'success' => true,
                'msg' => __('attendance.clocked_out_success'),
                'status' => $this->statusPayload($user_id),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());

            return [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
    }

    /**
     * Attendance report page + DataTables JSON.
     */
    public function report(Request $request)
    {
        if (! auth()->user()->can('attendance.view') && ! auth()->user()->can('attendance.view_all')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $can_view_all = $this->canViewAll($business_id);
        $can_edit = $this->canEdit($business_id);

        if ($request->ajax() && $request->input('format') !== 'calendar') {
            $query = EmployeeAttendance::with(['user', 'breaks'])
                ->where('business_id', $business_id);

            if (! $can_view_all) {
                $query->where('user_id', auth()->user()->id);
            } elseif ($request->filled('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $start = Carbon::createFromFormat('Y-m-d', $request->input('start_date'))->startOfDay();
                $end = Carbon::createFromFormat('Y-m-d', $request->input('end_date'))->endOfDay();
                $query->whereBetween('clock_in_at', [$start, $end]);
            }

            $query->orderBy('clock_in_at', 'desc');

            return DataTables::of($query)
                ->addColumn('employee', function ($row) {
                    return $this->employeeName($row);
                })
                ->addColumn('color_swatch', function ($row) use ($business_id) {
                    $color = TeamMemberColor::colorFor((int) $business_id, (int) $row->user_id);

                    return '<span class="att-color-dot" style="background:'.$color.'" title="'.e($this->employeeName($row)).'"></span>';
                })
                ->editColumn('clock_in_at', function ($row) {
                    return $row->clock_in_at ? $this->formatDateTime($row->clock_in_at) : '—';
                })
                ->editColumn('clock_out_at', function ($row) {
                    return $row->clock_out_at ? $this->formatDateTime($row->clock_out_at) : '—';
                })
                ->addColumn('break_time', function ($row) {
                    $duration = EmployeeAttendance::formatDuration($row->totalBreakSeconds());
                    $details = [];
                    foreach ($row->breaks as $b) {
                        if (! $b->break_start_at) {
                            continue;
                        }
                        $start = $this->formatTimeOnly($b->break_start_at);
                        $end = $b->break_end_at ? $this->formatTimeOnly($b->break_end_at) : '…';
                        $details[] = e($start).' – '.e($end);
                    }
                    if (empty($details)) {
                        return e($duration);
                    }

                    return '<div>'.e($duration).'</div><div class="text-muted" style="font-size:11px;line-height:1.4;">'
                        .implode('<br>', $details).'</div>';
                })
                ->addColumn('worked_time', function ($row) {
                    return EmployeeAttendance::formatDuration($row->workedSeconds());
                })
                ->editColumn('status', function ($row) {
                    $map = [
                        'clocked_in' => ['class' => 'pl-status--ok', 'label' => __('attendance.status_clocked_in')],
                        'on_break' => ['class' => 'pl-status--low', 'label' => __('attendance.status_on_break')],
                        'clocked_out' => ['class' => 'pl-status--inactive', 'label' => __('attendance.status_clocked_out')],
                    ];
                    $meta = $map[$row->status] ?? $map['clocked_out'];

                    return '<span class="pl-status '.$meta['class'].'"><span class="pl-status-dot"></span>'.$meta['label'].'</span>';
                })
                ->addColumn('action', function ($row) use ($can_edit) {
                    if (! $can_edit) {
                        return '';
                    }

                    return '<button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline att-edit-btn" data-id="'.$row->id.'"><i class="fa fa-edit"></i> '.__('messages.edit').'</button>';
                })
                ->rawColumns(['status', 'color_swatch', 'action', 'break_time'])
                ->make(true);
        }

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

        return view('attendance.report', compact('users', 'can_view_all', 'can_edit', 'user_colors'));
    }

    /**
     * FullCalendar event feed (time clocks colored per employee).
     */
    public function calendarEvents(Request $request)
    {
        if (! auth()->user()->can('attendance.view') && ! auth()->user()->can('attendance.view_all')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $can_view_all = $this->canViewAll($business_id);
        $tz = $this->businessTimezone();

        $start = $request->filled('start')
            ? Carbon::parse($request->input('start'), $tz)->startOfDay()
            : Carbon::now($tz)->startOfMonth();
        $end = $request->filled('end')
            ? Carbon::parse($request->input('end'), $tz)->endOfDay()
            : Carbon::now($tz)->endOfMonth();

        // Convert range to app/store timestamps for DB comparison
        $startUtc = $start->copy()->setTimezone(config('app.timezone'));
        $endUtc = $end->copy()->setTimezone(config('app.timezone'));

        $query = EmployeeAttendance::with(['user', 'breaks'])
            ->where('business_id', $business_id)
            ->where('clock_in_at', '<=', $endUtc)
            ->where(function ($q) use ($startUtc) {
                $q->whereNull('clock_out_at')
                    ->orWhere('clock_out_at', '>=', $startUtc);
            });

        if (! $can_view_all) {
            $query->where('user_id', auth()->user()->id);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $rows = $query->orderBy('clock_in_at')->get();
        $events = [];
        $now = Carbon::now();

        foreach ($rows as $row) {
            $name = $this->employeeName($row);
            $color = TeamMemberColor::colorFor((int) $business_id, (int) $row->user_id);
            $breakColor = TeamMemberColor::softColorFor((int) $business_id, (int) $row->user_id);
            $in = $row->clock_in_at;
            $out = $row->clock_out_at ?: $now;

            if (! $in) {
                continue;
            }

            $events[] = [
                'id' => 'work_'.$row->id,
                'attendance_id' => $row->id,
                'user_id' => $row->user_id,
                'type' => 'work',
                'title' => $name,
                'start' => $in->toIso8601String(),
                'end' => $out->toIso8601String(),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => '#ffffff',
                'className' => 'att-fc-work',
                'editable' => false,
                'extendedProps' => [
                    'kind' => 'work',
                    'status' => $row->status,
                    'worked' => EmployeeAttendance::formatDuration($row->workedSeconds($out)),
                    'notes' => $row->notes,
                ],
            ];

            foreach ($row->breaks as $break) {
                if (! $break->break_start_at) {
                    continue;
                }
                $bEnd = $break->break_end_at ?: $now;
                $events[] = [
                    'id' => 'break_'.$break->id,
                    'attendance_id' => $row->id,
                    'user_id' => $row->user_id,
                    'type' => 'break',
                    'title' => $name.' · '.__('attendance.break_time'),
                    'start' => $break->break_start_at->toIso8601String(),
                    'end' => $bEnd->toIso8601String(),
                    'backgroundColor' => $breakColor,
                    'borderColor' => $color,
                    'textColor' => '#1e293b',
                    'className' => 'att-fc-break',
                    'editable' => false,
                    'extendedProps' => [
                        'kind' => 'break',
                        'status' => 'break',
                    ],
                ];
            }
        }

        return response()->json($events);
    }

    /**
     * Single attendance record for edit modal.
     */
    public function show(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        if (! $this->canEdit($business_id)) {
            abort(403, 'Unauthorized action.');
        }

        $attendance = EmployeeAttendance::with(['user', 'breaks'])
            ->where('business_id', $business_id)
            ->findOrFail($id);

        if (! $this->canAccessRecord($attendance, $business_id)) {
            abort(403, 'Unauthorized action.');
        }

        return response()->json([
            'success' => true,
            'data' => $this->attendanceToFormArray($attendance),
        ]);
    }

    /**
     * Create a time clock entry (manager).
     */
    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        if (! $this->canEdit($business_id)) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'user_id' => 'required|integer',
            'clock_in_at' => 'required|string',
            'clock_out_at' => 'nullable|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $userId = (int) $request->input('user_id');
            if (! $this->canViewAll($business_id) && $userId !== (int) auth()->user()->id) {
                abort(403, 'Unauthorized action.');
            }

            $userOk = User::where('business_id', $business_id)->where('id', $userId)->exists();
            if (! $userOk) {
                return ['success' => false, 'msg' => __('messages.something_went_wrong')];
            }

            $clockIn = $this->parseBusinessDateTime($request->input('clock_in_at'));
            $clockOut = $request->filled('clock_out_at')
                ? $this->parseBusinessDateTime($request->input('clock_out_at'))
                : null;

            if ($clockOut && $clockOut->lt($clockIn)) {
                return ['success' => false, 'msg' => __('attendance.clock_out_before_in')];
            }

            DB::beginTransaction();

            $attendance = EmployeeAttendance::create([
                'business_id' => $business_id,
                'user_id' => $userId,
                'clock_in_at' => $clockIn,
                'clock_out_at' => $clockOut,
                'status' => $clockOut ? 'clocked_out' : 'clocked_in',
                'notes' => $request->input('notes'),
            ]);

            $this->syncBreaksFromRequest($attendance, $request->input('breaks', []), $clockIn, $clockOut);

            if (! $clockOut && $attendance->breaks()->whereNull('break_end_at')->exists()) {
                $attendance->status = 'on_break';
                $attendance->save();
            }

            DB::commit();

            return [
                'success' => true,
                'msg' => __('attendance.entry_saved'),
                'data' => $this->attendanceToFormArray($attendance->fresh(['user', 'breaks'])),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());

            return [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
    }

    /**
     * Update a time clock entry (manager).
     */
    public function update(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        if (! $this->canEdit($business_id)) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'clock_in_at' => 'required|string',
            'clock_out_at' => 'nullable|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $attendance = EmployeeAttendance::with('breaks')
                ->where('business_id', $business_id)
                ->findOrFail($id);

            if (! $this->canAccessRecord($attendance, $business_id)) {
                abort(403, 'Unauthorized action.');
            }

            $clockIn = $this->parseBusinessDateTime($request->input('clock_in_at'));
            $clockOut = $request->filled('clock_out_at')
                ? $this->parseBusinessDateTime($request->input('clock_out_at'))
                : null;

            if ($clockOut && $clockOut->lt($clockIn)) {
                return ['success' => false, 'msg' => __('attendance.clock_out_before_in')];
            }

            DB::beginTransaction();

            $attendance->clock_in_at = $clockIn;
            $attendance->clock_out_at = $clockOut;
            $attendance->notes = $request->input('notes');
            $attendance->status = $clockOut ? 'clocked_out' : 'clocked_in';
            $attendance->save();

            $this->syncBreaksFromRequest($attendance, $request->input('breaks', []), $clockIn, $clockOut);

            if (! $clockOut && $attendance->breaks()->whereNull('break_end_at')->exists()) {
                $attendance->status = 'on_break';
                $attendance->save();
            }

            DB::commit();

            return [
                'success' => true,
                'msg' => __('attendance.entry_updated'),
                'data' => $this->attendanceToFormArray($attendance->fresh(['user', 'breaks'])),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());

            return [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
    }

    /**
     * Delete a time clock entry.
     */
    public function destroy(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        if (! $this->canEdit($business_id)) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $attendance = EmployeeAttendance::where('business_id', $business_id)->findOrFail($id);

            if (! $this->canAccessRecord($attendance, $business_id)) {
                abort(403, 'Unauthorized action.');
            }

            $attendance->breaks()->delete();
            $attendance->delete();

            return [
                'success' => true,
                'msg' => __('attendance.entry_deleted'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());

            return [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
    }

    protected function canViewAll($business_id): bool
    {
        return auth()->user()->can('attendance.view_all')
            || $this->businessUtil->is_admin(auth()->user(), $business_id);
    }

    protected function canEdit($business_id): bool
    {
        return auth()->user()->can('attendance.edit')
            || $this->businessUtil->is_admin(auth()->user(), $business_id);
    }

    protected function canAccessRecord(EmployeeAttendance $attendance, $business_id): bool
    {
        if ($this->canViewAll($business_id) || $this->businessUtil->is_admin(auth()->user(), $business_id)) {
            return true;
        }

        return (int) $attendance->user_id === (int) auth()->user()->id;
    }

    protected function employeeName(EmployeeAttendance $row): string
    {
        return optional($row->user)->user_full_name
            ?: trim((optional($row->user)->first_name ?? '').' '.(optional($row->user)->last_name ?? ''));
    }

    protected function businessTimezone(): string
    {
        return session('business.time_zone', config('app.timezone'));
    }

    protected function statusPayload(int $user_id): array
    {
        $business_id = request()->session()->get('user.business_id');
        $attendance = EmployeeAttendance::with('breaks')
            ->where('business_id', $business_id)
            ->where('user_id', $user_id)
            ->whereIn('status', ['clocked_in', 'on_break'])
            ->latest('clock_in_at')
            ->first();

        if (! $attendance) {
            return [
                'state' => 'clocked_out',
                'clock_in_at' => null,
                'break_start_at' => null,
                'worked_seconds' => 0,
                'break_seconds' => 0,
                'worked_label' => '00:00:00',
                'break_label' => '00:00:00',
            ];
        }

        $openBreak = $attendance->breaks->firstWhere('break_end_at', null);
        $worked = $attendance->workedSeconds();
        $breakSecs = $attendance->totalBreakSeconds();

        return [
            'state' => $attendance->status,
            'clock_in_at' => optional($attendance->clock_in_at)->toIso8601String(),
            'break_start_at' => optional(optional($openBreak)->break_start_at)->toIso8601String(),
            'worked_seconds' => $worked,
            'break_seconds' => $breakSecs,
            'worked_label' => EmployeeAttendance::formatDuration($worked),
            'break_label' => EmployeeAttendance::formatDuration($breakSecs),
        ];
    }

    protected function phpTimeFormat(): string
    {
        $setting = session('business.time_format', 24);
        if ((string) $setting === '12') {
            return 'h:i A';
        }

        return 'H:i';
    }

    protected function formatDateTime($datetime): string
    {
        $tz = $this->businessTimezone();
        $dateFormat = session('business.date_format', 'm/d/Y');

        return Carbon::parse($datetime)->setTimezone($tz)->format($dateFormat.' '.$this->phpTimeFormat());
    }

    /**
     * Format for datetimepicker inputs (business tz).
     */
    protected function formatForInput($datetime): ?string
    {
        if (! $datetime) {
            return null;
        }
        $tz = $this->businessTimezone();
        $dateFormat = session('business.date_format', 'm/d/Y');

        return Carbon::parse($datetime)->setTimezone($tz)->format($dateFormat.' '.$this->phpTimeFormat());
    }

    protected function parseBusinessDateTime(string $value): Carbon
    {
        $tz = $this->businessTimezone();
        $dateFormat = session('business.date_format', 'm/d/Y');
        $timeFormat = $this->phpTimeFormat();

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

    protected function attendanceToFormArray(EmployeeAttendance $attendance): array
    {
        $breaks = [];
        foreach ($attendance->breaks as $b) {
            $breaks[] = [
                'id' => $b->id,
                'break_start_at' => $this->formatForInput($b->break_start_at),
                'break_end_at' => $this->formatForInput($b->break_end_at),
                'reason' => $b->reason,
            ];
        }

        return [
            'id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'employee' => $this->employeeName($attendance),
            'color' => TeamMemberColor::colorFor((int) $attendance->business_id, (int) $attendance->user_id),
            'clock_in_at' => $this->formatForInput($attendance->clock_in_at),
            'clock_out_at' => $this->formatForInput($attendance->clock_out_at),
            'status' => $attendance->status,
            'notes' => $attendance->notes,
            'breaks' => $breaks,
            'worked_time' => EmployeeAttendance::formatDuration($attendance->workedSeconds()),
            'break_time' => EmployeeAttendance::formatDuration($attendance->totalBreakSeconds()),
        ];
    }

    /**
     * Enforce on-site geofence when the selected location has coordinates.
     * Users with attendance.clock_offsite (or admins) may bypass.
     *
     * @return array|null Error payload, or null if allowed
     */
    protected function assertClockGeofence(Request $request, $business_id): ?array
    {
        if (
            auth()->user()->can('attendance.clock_offsite')
            || $this->businessUtil->is_admin(auth()->user(), $business_id)
        ) {
            return null;
        }

        $location = $this->resolveClockLocation($request, $business_id);
        if (
            ! $location
            || $location->latitude === null
            || $location->longitude === null
            || $location->latitude === ''
            || $location->longitude === ''
        ) {
            return null;
        }

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');
        if ($lat === null || $lng === null || $lat === '' || $lng === '') {
            return [
                'success' => false,
                'msg' => __('attendance.location_required'),
            ];
        }

        $radius = (int) ($location->clock_radius_meters ?: 200);
        $distance = $this->haversineMeters(
            (float) $location->latitude,
            (float) $location->longitude,
            (float) $lat,
            (float) $lng
        );

        if ($distance > $radius) {
            return [
                'success' => false,
                'msg' => __('attendance.outside_geofence'),
            ];
        }

        return null;
    }

    protected function resolveClockLocation(Request $request, $business_id): ?BusinessLocation
    {
        $locationId = $request->input('location_id')
            ?: $request->session()->get('user.default_location_id');

        if ($locationId) {
            $loc = BusinessLocation::where('business_id', $business_id)->find($locationId);
            if ($loc) {
                return $loc;
            }
        }

        return BusinessLocation::where('business_id', $business_id)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('id')
            ->first();
    }

    protected function extractClockCoords(Request $request): array
    {
        $lat = $request->input('latitude');
        $lng = $request->input('longitude');
        $has = $lat !== null && $lat !== '' && $lng !== null && $lng !== '';

        $business_id = $request->session()->get('user.business_id');
        $offsite = auth()->user()->can('attendance.clock_offsite')
            || $this->businessUtil->is_admin(auth()->user(), $business_id);

        return [
            'latitude' => $has ? (float) $lat : null,
            'longitude' => $has ? (float) $lng : null,
            'source' => $has ? ($offsite ? 'offsite' : 'web') : null,
        ];
    }

    protected function haversineMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return 2 * $earth * asin(min(1, sqrt($a)));
    }

    protected function formatTimeOnly($datetime): string
    {
        $tz = $this->businessTimezone();

        return Carbon::parse($datetime)->setTimezone($tz)->format($this->phpTimeFormat());
    }

    protected function syncBreaksFromRequest(EmployeeAttendance $attendance, $breaks, Carbon $clockIn, ?Carbon $clockOut): void
    {
        $attendance->breaks()->delete();

        if (! is_array($breaks)) {
            return;
        }

        foreach ($breaks as $row) {
            if (empty($row['break_start_at'])) {
                continue;
            }
            try {
                $bStart = $this->parseBusinessDateTime($row['break_start_at']);
            } catch (\Exception $e) {
                continue;
            }
            $bEnd = null;
            if (! empty($row['break_end_at'])) {
                try {
                    $bEnd = $this->parseBusinessDateTime($row['break_end_at']);
                } catch (\Exception $e) {
                    $bEnd = null;
                }
            }

            // Keep break within shift bounds when possible
            if ($bStart->lt($clockIn)) {
                $bStart = $clockIn->copy();
            }
            if ($clockOut && $bStart->gt($clockOut)) {
                continue;
            }
            if ($bEnd && $clockOut && $bEnd->gt($clockOut)) {
                $bEnd = $clockOut->copy();
            }
            if ($bEnd && $bEnd->lt($bStart)) {
                $bEnd = $bStart->copy();
            }

            EmployeeAttendanceBreak::create([
                'attendance_id' => $attendance->id,
                'break_start_at' => $bStart,
                'break_end_at' => $bEnd,
                'reason' => $row['reason'] ?? null,
            ]);
        }
    }

    /**
     * POS Time Clock permission, or Sale App (same EmployeeAttendance records).
     */
    protected function assertCanClock(): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(403, 'Unauthorized action.');
        }
        if ($user->can('attendance.clock') || auth()->getDefaultDriver() === 'sale') {
            return;
        }

        abort(403, 'Unauthorized action.');
    }
}
