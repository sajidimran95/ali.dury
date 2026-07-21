<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Model;

class EcomShippingRule extends Model
{
    protected $table = 'ecom_shipping_rules';

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'amount' => 'float',
        'min_order_amount' => 'float',
        'max_weight' => 'float',
        'states' => 'array',
    ];
}
