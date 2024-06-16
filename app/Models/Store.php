<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
            'manager_id',
            'ten',
            'dia_chi',
            'so_dien_thoai',
            'email'
    ];
}
