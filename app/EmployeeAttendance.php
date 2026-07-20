<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeAttendance extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'clock_in_at' => 'datetime',
        'clock_out_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function breaks()
    {
        return $this->hasMany(EmployeeAttendanceBreak::class, 'attendance_id');
    }

    /**
     * Total break seconds for this attendance (completed + open break).
     */
    public function totalBreakSeconds(?\Carbon\Carbon $until = null): int
    {
        $until = $until ?: \Carbon\Carbon::now();
        $total = 0;

        foreach ($this->breaks as $break) {
            $start = $break->break_start_at;
            $end = $break->break_end_at ?: $until;
            if ($start && $end) {
                $total += max(0, $end->getTimestamp() - $start->getTimestamp());
            }
        }

        return $total;
    }

    /**
     * Worked seconds excluding breaks.
     */
    public function workedSeconds(?\Carbon\Carbon $until = null): int
    {
        $until = $until ?: ($this->clock_out_at ?: \Carbon\Carbon::now());
        if (! $this->clock_in_at) {
            return 0;
        }

        $elapsed = max(0, $until->getTimestamp() - $this->clock_in_at->getTimestamp());

        return max(0, $elapsed - $this->totalBreakSeconds($until));
    }

    public static function formatDuration(int $seconds): string
    {
        $seconds = max(0, $seconds);
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
}
