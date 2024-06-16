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
       
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->integer('manager_id');
            $table->string('ten');
            $table->string('thanh_pho');
            $table->string('huyen');
            $table->string('xa');
            $table->string('chi_tiet');
            $table->string('so_dien_thoai');
            $table->string('email');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }

    // public function change(): void
    // {
    //     Schema::table('stores', function (Blueprint $table) {
    //         $table->string('email')->after('so_dien_thoai'); // Thêm cột 'new_column' sau cột 'so_dien_thoai'
    //     });
    // }
};
