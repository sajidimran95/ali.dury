<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BulkPriceUpdate extends Model
{
    protected $fillable = [
        'business_id',
        'created_by',
        'update_type',
        'update_value',
        'price_field',
        'filters',
        'products_count',
        'product_ids',
        'notes',
    ];

    protected $casts = [
        'filters' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }
}
