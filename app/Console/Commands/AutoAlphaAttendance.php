<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Student;

class AutoAlphaAttendance extends Command
{
    protected $signature = 'attendance:auto-alpha';
    protected $description =
    'Auto set alfa untuk siswa yang belum absen';
    public function handle()
    {
        $now = now()->format('H:i:s');

        $sessions = AttendanceSession::with('schedule')
            ->where('status', 'closed')
            ->whereDate('date', today())
            ->get();

        foreach ($sessions as $session) {
            $schedule = $session->schedule;
            if (!$schedule) {
                continue;
            }

            // ambil seluruh siswa kelas ini
            $students = Student::where(
                'class_id',
                $schedule->class_id
            )->get();

            foreach ($students as $student) {
                $exists = Attendance::where(
                    'attendance_session_id',
                    $session->id
                )
                    ->where('user_id', $student->user_id)
                    ->exists();

                // command auto alfa jika belum absen 
                if (!$exists) {
                    Attendance::firstOrCreate([
                        'attendance_session_id' => $session->id,
                        'lesson_schedule_id' => $schedule->id,
                        'user_id' => $student->user_id,
                    ], [
                        'date' => now()->toDateString(),
                        'check_in_time' => null,
                        'status' => 'alfa',
                        'method' => 'manual',
                    ]);
                }
            }
        }

        $this->info('Auto alfa selesai.');
    }
}
