<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->decimal('temperature',  5, 2)->comment('Degrees Celsius');
            $table->decimal('humidity',     5, 2)->comment('Percentage 0-100');
            $table->decimal('soil_moisture', 5, 2)->comment('Percentage 0-100');
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
