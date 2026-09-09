<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaleZeroSale extends Model
{
    protected $guarded = ['id'];

    public function reason()
    {
        return $this->belongsTo(SaleZeroSaleReason::class, 'reason_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
