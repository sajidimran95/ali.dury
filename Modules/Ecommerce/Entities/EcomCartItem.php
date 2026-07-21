<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Model;

class EcomCartItem extends Model
{
    protected $table = 'ecom_cart_items';

    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'float',
        'unit_price' => 'float',
    ];

    public function cart()
    {
        return $this->belongsTo(EcomCart::class, 'cart_id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Product::class, 'product_id');
    }

    public function variation()
    {
        return $this->belongsTo(\App\Variation::class, 'variation_id');
    }
}
