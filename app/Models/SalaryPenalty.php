<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryPenalty extends Model
{
    use HasFactory;
    protected $fillable = [
        'employee_id',
        'reason',
        'amount',
        'store_id'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
