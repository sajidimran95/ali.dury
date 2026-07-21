<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Model;

class EcomCoupon extends Model
{
    protected $table = 'ecom_coupons';

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'value' => 'float',
        'min_order_amount' => 'float',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function usages()
    {
        return $this->hasMany(EcomCouponUsage::class, 'coupon_id');
    }
}
