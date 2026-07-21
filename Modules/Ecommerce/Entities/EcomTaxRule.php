<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Model;

class EcomTaxRule extends Model
{
    protected $table = 'ecom_tax_rules';

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'rate' => 'float',
    ];
}
