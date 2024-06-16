<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WorkScheduleShift;
use App\Models\Employee;

class WorkScheduleEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_schedule_shift_id',
        'employee_id',
    ];

    public function workScheduleShift()
    {
        return $this->belongsTo(WorkScheduleShift::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
