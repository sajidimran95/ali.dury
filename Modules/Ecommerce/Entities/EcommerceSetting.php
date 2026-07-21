<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Model;

class EcommerceSetting extends Model
{
    protected $table = 'ecommerce_settings';

    protected $guarded = ['id'];

    protected $casts = [
        'is_enabled' => 'boolean',
        'enable_cod' => 'boolean',
        'enable_stripe' => 'boolean',
        'enable_paypal' => 'boolean',
        'extra' => 'array',
    ];

    public static function forBusiness(?int $business_id): ?self
    {
        if (empty($business_id)) {
            return null;
        }

        return static::firstOrCreate(
            ['business_id' => $business_id],
            [
                'is_enabled' => false,
                'store_name' => 'Online Store',
                'enable_cod' => true,
                'enable_stripe' => false,
                'enable_paypal' => false,
                'default_country' => 'United States',
            ]
        );
    }

    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    public function location()
    {
        return $this->belongsTo(\App\BusinessLocation::class, 'location_id');
    }
}
