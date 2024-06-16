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
        Schema::create('time_clock_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_name');
            $table->string('mac_address', 17)->unique(); 
            $table->foreignId('store_id')->constrained();
            $table->boolean('status')->default(true); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_clock_devices');
    }
};
