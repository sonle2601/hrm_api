<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkShift extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'start_time', 'end_time', 'status', 'store_id'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
