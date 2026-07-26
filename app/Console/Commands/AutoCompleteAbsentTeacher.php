<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\LessonSchedule;
use App\Models\Student;
use App\Models\Teacher;

class AutoCompleteAbsentTeacher extends Command
{
    protected $signature =
    'attendance:auto-complete-absent-teacher';

    protected $description =
    'Auto hadir jika guru tidak memulai absensi';

    public function handle()
    {
        $this->info("AUTO COMPLETE RUNNING");
        $now = now();

        $today = $now->dayOfWeekIso;

        $schedules = LessonSchedule::with('class')
            ->where('day_of_week', $today)
            ->get();
        $this->info("TOTAL SCHEDULE: " . $schedules->count());

        foreach ($schedules as $schedule) {
            $this->info("CHECK SCHEDULE {$schedule->id}");
            $endTime = \Carbon\Carbon::parse(
                today()->toDateString() . ' ' .
                    $schedule->end_time
            );

            // belum selesai
            if ($now->lt($endTime)) {
                continue;
            }

            // cek session hari ini
            $session = AttendanceSession::where(
                'lesson_schedule_id',
                $schedule->id
            )
                ->whereDate('date', today())
                ->first();
            if ($session) {
                continue;
            }

            $teacher = Teacher::find(
                $schedule->teacher_id
            );

            if (!$teacher || !$teacher->user_id) {
                continue;
            }

            // auto create closed session
            $session = AttendanceSession::create([
                'teacher_id' => $teacher->user_id,
                'lesson_schedule_id' => $schedule->id,
                'date' => today(),
                'start_time' => null,
                'end_time' => now(),
                'gps_enabled' => false,
                'status' => 'closed',
            ]);

            // command seluruh siswa auto hadir
            $students = Student::where(
                'class_id',
                $schedule->class_id
            )->get();

            foreach ($students as $student) {

                Attendance::firstOrCreate([
                    'attendance_session_id' => $session->id,
                    'lesson_schedule_id' => $schedule->id,
                    'user_id' => $student->user_id,
                ], [
                    'date' => today(),
                    'check_in_time' => null,
                    'status' => 'hadir',
                    'method' => 'manual',
                ]);
            }

            $this->info(
                "AUTO HADIR schedule {$schedule->id}"
            );
        }

        return Command::SUCCESS;
    }
}
