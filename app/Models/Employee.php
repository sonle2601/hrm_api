<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $fillable = [
       'part_id',
        // 'work_shift_id',
        'store_id',
        'user_id',
        'employee_name',
       'employee_phone',
       'employee_email',
        'salaries',
        'start_time',
        'employee_status',
        'invitation_status',
        'email',
        'salary_type'
    ];
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function part()
    {
        return $this->belongsTo(Part::class);
    }
    // Trong mô hình Employee
public function information() {
    return $this->hasOne(Information::class, 'user_id', 'user_id');
}


}
