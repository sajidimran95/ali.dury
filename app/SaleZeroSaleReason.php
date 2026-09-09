<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaleZeroSaleReason extends Model
{
    protected $guarded = ['id'];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1)->orderBy('sort_order')->orderBy('name');
    }
}
