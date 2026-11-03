<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Archived snapshot of a deleted sale / sales order / draft / quotation.
 * Payload holds the transaction row, sell lines, payment lines and warranties.
 */
class DeletedSale extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'payload' => 'array',
        'transaction_date' => 'datetime',
    ];

    public function contact()
    {
        return $this->belongsTo(\App\Contact::class, 'contact_id');
    }

    public function deletedBy()
    {
        return $this->belongsTo(\App\User::class, 'deleted_by');
    }

    public function location()
    {
        return $this->belongsTo(\App\BusinessLocation::class, 'location_id');
    }
}
