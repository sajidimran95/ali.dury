<?php

namespace Modules\Crm\Entities;

use App\Contact;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallLog extends Model
{
    use SoftDeletes;

    protected $table = 'crm_call_logs';

    protected $guarded = ['id'];

    protected $casts = [
        'called_on' => 'datetime',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
