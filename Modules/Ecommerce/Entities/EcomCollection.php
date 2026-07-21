<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Model;

class EcomCollection extends Model
{
    protected $table = 'ecom_collections';

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products()
    {
        return $this->belongsToMany(\App\Product::class, 'ecom_collection_products', 'collection_id', 'product_id')
            ->withPivot('sort_order')
            ->withTimestamps();
    }
}
