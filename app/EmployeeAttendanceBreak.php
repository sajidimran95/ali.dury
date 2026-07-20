<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeAttendanceBreak extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'break_start_at' => 'datetime',
        'break_end_at' => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(EmployeeAttendance::class, 'attendance_id');
    }
}
