<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LateEarlyRequests extends Model
{
    use HasFactory;
    protected $fillable = [
        'work_schedule_id',
        'type',
        'time',
        'reason',
        'status',
        'store_id'
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
