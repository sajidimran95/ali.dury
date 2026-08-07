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

    /**
     * Stable distinct color per team member for calendar blocks.
     */
    public static function colorForUserId(int $userId): string
    {
        // High-contrast palette for side-by-side time blocks
        $palette = [
            '#2563EB', // blue
            '#059669', // emerald
            '#D97706', // amber
            '#DC2626', // red
            '#7C3AED', // violet
            '#DB2777', // pink
            '#0891B2', // cyan
            '#EA580C', // orange
            '#4F46E5', // indigo
            '#65A30D', // lime
            '#0D9488', // teal
            '#C026D3', // fuchsia
            '#0284C7', // sky
            '#9333EA', // purple
            '#CA8A04', // yellow
            '#BE123C', // rose
        ];

        return $palette[abs($userId) % count($palette)];
    }

    /**
     * Slightly lighter shade for break segments of the same employee.
     */
    public static function breakColorForUserId(int $userId): string
    {
        $hex = ltrim(self::colorForUserId($userId), '#');
        if (strlen($hex) !== 6) {
            return '#94A3B8';
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Mix with white ~40%
        $r = (int) round($r + (255 - $r) * 0.4);
        $g = (int) round($g + (255 - $g) * 0.4);
        $b = (int) round($b + (255 - $b) * 0.4);

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }
}
