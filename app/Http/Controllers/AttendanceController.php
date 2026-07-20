<?php

namespace App\Http\Controllers;

use App\EmployeeAttendance;
use App\EmployeeAttendanceBreak;
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
        if (! auth()->user()->can('attendance.clock')) {
            abort(403, 'Unauthorized action.');
        }

        return response()->json($this->statusPayload(auth()->user()->id));
    }

    /**
     * Clock in.
     */
    public function clockIn(Request $request)
    {
        if (! auth()->user()->can('attendance.clock')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');
            $user_id = auth()->user()->id;

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

            EmployeeAttendance::create([
                'business_id' => $business_id,
                'user_id' => $user_id,
                'clock_in_at' => Carbon::now(),
                'status' => 'clocked_in',
                'notes' => $request->input('notes'),
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
        if (! auth()->user()->can('attendance.clock')) {
            abort(403, 'Unauthorized action.');
        }

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
        if (! auth()->user()->can('attendance.clock')) {
            abort(403, 'Unauthorized action.');
        }

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
        if (! auth()->user()->can('attendance.clock')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');
            $user_id = auth()->user()->id;

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

            $attendance->clock_out_at = Carbon::now();
            $attendance->status = 'clocked_out';
            if ($request->filled('notes')) {
                $attendance->notes = $request->input('notes');
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
        $can_view_all = auth()->user()->can('attendance.view_all')
            || $this->businessUtil->is_admin(auth()->user(), $business_id);

        if ($request->ajax()) {
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
                    return optional($row->user)->user_full_name
                        ?: trim((optional($row->user)->first_name ?? '').' '.(optional($row->user)->last_name ?? ''));
                })
                ->editColumn('clock_in_at', function ($row) {
                    return $row->clock_in_at ? $this->formatDateTime($row->clock_in_at) : '—';
                })
                ->editColumn('clock_out_at', function ($row) {
                    return $row->clock_out_at ? $this->formatDateTime($row->clock_out_at) : '—';
                })
                ->addColumn('break_time', function ($row) {
                    return EmployeeAttendance::formatDuration($row->totalBreakSeconds());
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
                ->rawColumns(['status'])
                ->make(true);
        }

        $users = [];
        if ($can_view_all) {
            $users = User::where('business_id', $business_id)
                ->select('id', 'first_name', 'last_name', 'username')
                ->orderBy('first_name')
                ->get()
                ->pluck('user_full_name', 'id');
        }

        return view('attendance.report', compact('users', 'can_view_all'));
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

    protected function formatDateTime($datetime): string
    {
        $tz = session('business.time_zone', config('app.timezone'));
        $format = session('business.date_format', 'm/d/Y').' '.session('business.time_format', 'H:i');

        return Carbon::parse($datetime)->setTimezone($tz)->format($format);
    }
}
