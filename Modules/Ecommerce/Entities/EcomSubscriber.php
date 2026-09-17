<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Model;

class EcomSubscriber extends Model
{
    protected $table = 'ecom_subscribers';

    protected $guarded = ['id'];
}
