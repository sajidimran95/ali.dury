<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Model;

class EcomCustomerLicense extends Model
{
    protected $table = 'ecom_customer_licenses';

    protected $guarded = ['id'];

    protected $casts = [
        'expires_at' => 'date',
    ];

    public function contact()
    {
        return $this->belongsTo(\App\Contact::class, 'contact_id');
    }
}
