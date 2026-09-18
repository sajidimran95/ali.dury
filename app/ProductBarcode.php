<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductBarcode extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(\App\Product::class);
    }

    public function variation()
    {
        return $this->belongsTo(\App\Variation::class);
    }

    public function unit()
    {
        return $this->belongsTo(\App\Unit::class);
    }

    public function scopeForBusiness($query, $business_id)
    {
        return $query->where('business_id', $business_id);
    }
}
