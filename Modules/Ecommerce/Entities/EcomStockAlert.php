<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Model;

class EcomStockAlert extends Model
{
    protected $table = 'ecom_stock_alerts';

    protected $guarded = ['id'];
}
