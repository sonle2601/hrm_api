<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WorkSchedule;
use App\Models\Shift;


class WorkScheduleShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_schedule_id',
        'shift_id',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function workSchedule()
    {
        return $this->belongsTo(WorkSchedule::class);
    }
}
