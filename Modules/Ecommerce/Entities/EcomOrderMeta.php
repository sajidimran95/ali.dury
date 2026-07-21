<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Model;

class EcomOrderMeta extends Model
{
    protected $table = 'ecom_order_meta';

    protected $guarded = ['id'];

    protected $casts = [
        'timeline' => 'array',
        'shipping_address' => 'array',
        'billing_address' => 'array',
    ];

    public function transaction()
    {
        return $this->belongsTo(\App\Transaction::class, 'transaction_id');
    }
}
