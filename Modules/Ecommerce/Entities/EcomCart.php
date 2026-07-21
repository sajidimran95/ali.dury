<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Model;

class EcomCart extends Model
{
    protected $table = 'ecom_carts';

    protected $guarded = ['id'];

    public function items()
    {
        return $this->hasMany(EcomCartItem::class, 'cart_id');
    }

    public function contact()
    {
        return $this->belongsTo(\App\Contact::class, 'contact_id');
    }
}
