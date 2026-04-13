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
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();

            //Koordinat Lokasi Sekolah
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();

            //radius GPS
            $table->integer('radius_meter')->default(100);

            //threshold face recognition
            $table->double('face_threshold')->default(0.8);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};
