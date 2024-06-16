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
    Schema::create('attendances', function (Blueprint $table) {
        $table->id();
        $table->time('check_in');
        $table->time('check_out')->nullable();
        $table->date('date');
        $table->integer('minutes_out')->default(0); // Đặt giá trị mặc định là 0
        $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
        $table->foreignId('work_schedule')->constrained();
        $table->integer('total_minutes')->nullable();
        $table->timestamps();
    });

    // Cập nhật dữ liệu sau khi tạo bảng
    DB::table('attendances')->update([
        'total_minutes' => DB::raw('TIMESTAMPDIFF(MINUTE, check_in, check_out) - minutes_out')
    ]);
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
