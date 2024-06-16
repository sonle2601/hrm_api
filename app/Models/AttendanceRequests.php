<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequests extends Model
{
    use HasFactory;
    protected $fillable = [
        'check_in',
        'check_out',
        'status',
        'work_schedule',
        'date'
     ];

     public function workSchedule()
    {
        return $this->belongsTo(WorkSchedule::class, 'work_schedule');
    }
}
