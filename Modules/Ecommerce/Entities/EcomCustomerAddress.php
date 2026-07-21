<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Model;

class EcomCustomerAddress extends Model
{
    protected $table = 'ecom_customer_addresses';

    protected $guarded = ['id'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function contact()
    {
        return $this->belongsTo(\App\Contact::class, 'contact_id');
    }

    public function formatted(): string
    {
        $parts = array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state.' '.$this->zip_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }
}
