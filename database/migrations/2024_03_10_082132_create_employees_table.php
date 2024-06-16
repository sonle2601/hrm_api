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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained();
            $table->foreignId('store_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('employee_name');
            $table->string('employee_phone');
            $table->string('employee_email');
            $table->string('salary_type');
            $table->decimal('salaries', 10, 2);
            $table->timestamp('start_time');
            $table->boolean('employee_status')->default(true);
            $table->string('invitation_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
