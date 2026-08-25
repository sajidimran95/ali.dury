<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TimeOffRequest extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public const TYPES = ['vacation', 'sick', 'personal', 'other'];

    public const STATUSES = ['pending', 'approved', 'rejected', 'cancelled'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
