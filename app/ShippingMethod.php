<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingMethod extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Dropdown for POS / sell forms: [name => name] (store name on transaction for history).
     */
    public static function forDropdown(int $business_id, bool $show_none = false, bool $only_active = true): array
    {
        $query = self::where('business_id', $business_id)
            ->orderBy('name');

        if ($only_active) {
            $query->where('is_active', 1);
        }

        $methods = $query->pluck('name', 'name')->toArray();

        if ($show_none) {
            return ['' => __('messages.please_select')] + $methods;
        }

        return $methods;
    }
}
