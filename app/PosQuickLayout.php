<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PosQuickLayout extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'keys' => 'array',
        'keep_folder_open' => 'boolean',
        'is_current' => 'boolean',
    ];

    public static function forUser(int $businessId, int $userId)
    {
        return static::where('business_id', $businessId)
            ->orderByDesc('is_current')
            ->orderBy('name')
            ->get();
    }

    public static function currentForUser(int $businessId, int $userId)
    {
        $current = static::where('business_id', $businessId)
            ->where('is_current', 1)
            ->first();
        if ($current) {
            return $current;
        }

        return static::where('business_id', $businessId)->orderBy('name')->first();
    }

    public function markCurrent(): void
    {
        static::where('business_id', $this->business_id)
            ->update(['is_current' => 0]);

        $this->is_current = 1;
        $this->save();
    }
}
