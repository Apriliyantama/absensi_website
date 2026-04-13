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
        Schema::create('lesson_schedules', function (Blueprint $table) {
            $table->id();

            // Relasi utama jadwal
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();

            // Hari & waktu pelajaran
            $table->tinyInteger('day_of_week'); // 1=Senin ... 7=Minggu
            $table->time('start_time');
            $table->time('end_time');

            // Toleransi keterlambatan
            $table->integer('tolerance_minutes')->default(15);

            // Status jadwal (bisa disable tanpa delete)
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_schedules');
    }
};
