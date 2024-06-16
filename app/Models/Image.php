<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'anh_ca_nhan',
        'anh_cmnd_truoc',
        'anh_cmnd_sau'
    ];
    
}
