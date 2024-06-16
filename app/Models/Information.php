<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Information extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'anh_ca_nhan',
        'email',
        'ho_ten',
        'so_cmnd',
        'so_dien_thoai',
        'nam_sinh',
        'gioi_tinh',
        'dia_chi',
        'ngan_hang',
        'so_tai_khoan',
        'anh_mat_truoc',
        'anh_mat_sau',
    ];
}
