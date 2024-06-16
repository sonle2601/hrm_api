<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExitRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_schedule_id',
        'reason',
        'status',
        'type',
        'minutes_out',
        'store_id',
    ];

    public function workSchedule()
    {
        return $this->belongsTo(WorkSchedule::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
