<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TeamMemberColor extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Resolve calendar color: custom row first, then stable palette hash.
     */
    public static function colorFor(int $businessId, int $userId): string
    {
        $custom = static::where('business_id', $businessId)
            ->where('user_id', $userId)
            ->value('color');

        if (! empty($custom) && preg_match('/^#[0-9A-Fa-f]{6}$/', $custom)) {
            return strtoupper($custom);
        }

        return EmployeeAttendance::colorForUserId($userId);
    }

    /**
     * Lighter shade for breaks / time-off of the same member.
     */
    public static function softColorFor(int $businessId, int $userId): string
    {
        $hex = ltrim(static::colorFor($businessId, $userId), '#');
        if (strlen($hex) !== 6) {
            return '#94A3B8';
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = (int) round($r + (255 - $r) * 0.4);
        $g = (int) round($g + (255 - $g) * 0.4);
        $b = (int) round($b + (255 - $b) * 0.4);

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }
}
