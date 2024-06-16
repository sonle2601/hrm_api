<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('information', function (Blueprint $table) {
            $table->id();
            $table->string('anh_ca_nhan')->nullable();
            $table->integer('user_id');
            $table->string('email');
            $table->string('ho_ten');
            $table->string('so_cmnd');
            $table->string('so_dien_thoai');
            $table->string('nam_sinh');
            $table->string('gioi_tinh');
            $table->string('dia_chi');
            $table->string('ngan_hang')->nullable();
            $table->string('so_tai_khoan')->nullable();
            $table->string('anh_mat_truoc');
            $table->string('anh_mat_sau');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('information');
    }
};
