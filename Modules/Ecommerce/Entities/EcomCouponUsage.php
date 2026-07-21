<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Model;

class EcomCouponUsage extends Model
{
    protected $table = 'ecom_coupon_usages';

    protected $guarded = ['id'];
}
