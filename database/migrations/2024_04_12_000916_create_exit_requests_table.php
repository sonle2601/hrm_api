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
        Schema::create('exit_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_schedule_id');
            $table->text('reason')->nullable();
            $table->foreign('work_schedule_id')->references('id')->on('work_schedules');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->integer('minutes_out')->default(0); 
            $table->foreignId('store_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exit_requests');
    }
};
