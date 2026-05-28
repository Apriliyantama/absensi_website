<?php

namespace Database\Seeders;

use App\Models\LessonSchedule;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LessonScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        LessonSchedule::updateOrCreate(
            ['id' => 1],
            [
                'class_id' => 1,
                'subject_id' => 1,
                'teacher_id' => 1,
                'day_of_week' => 2, // Selasa
                'start_time' => '08:00:00',
                'end_time' => '09:30:00',
                'is_active' => true,
            ]
        );
    }
}
