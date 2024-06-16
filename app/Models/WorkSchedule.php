<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WorkShift;
use App\Models\Employee;


class WorkSchedule extends Model
{
    use HasFactory;
    protected $fillable = [
        'date',
        'work_shift_id',
        'employee_id',
        'store_id',
        'status'
    ];

    public function workShift()
    {
        return $this->belongsTo(WorkShift::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    
}
