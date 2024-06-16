<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Noitification extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'content',
        'type',
        'user_id',
        'reference_id',
        'is_read'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leaveRequest()
    {
        return $this->hasOne(LeaveRequest::class, 'id', 'reference_id');
    }


    public function exitRequest()
    {
        return $this->hasOne(ExitRequest::class, 'id', 'reference_id');
    }

    public function lateEarlyRequests()
    {
        return $this->hasOne(LateEarlyRequests::class, 'id', 'reference_id');
    }

    public function attendanceRequest()
    {
        return $this->hasOne(AttendanceRequests::class, 'id', 'reference_id');
    }

}
