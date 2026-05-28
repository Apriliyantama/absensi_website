<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\LessonSchedule;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Student;

class TeacherDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // relasi teacher
        if (!$user->teacher) {
            abort(403, 'Akun ini tidak terhubung dengan data guru');
        }

        $teacher = $user->teacher;

        // hari ini (1 = senin)
        $today = now()->dayOfWeekIso;

        $schedules = LessonSchedule::with(['class', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->where('day_of_week', $today)
            ->orderBy('start_time')
            ->get()
            ->map(function ($schedule) use ($user) {

                $now = now();

                $schedule->active_session = null;
                $schedule->closed_session = false;
                $schedule->can_edit = false;
                $schedule->is_started = false;
                $schedule->is_ended = false;

                $startTime = \Carbon\Carbon::parse(
                    today()->toDateString() . ' ' .
                        $schedule->start_time
                );

                $endTime = \Carbon\Carbon::parse(
                    today()->toDateString() . ' ' .
                        $schedule->end_time
                );

                // STATUS WAKTU
                $schedule->is_started =
                    $now->greaterThanOrEqualTo($startTime);

                $schedule->is_ended =
                    $now->greaterThan($endTime);

                // SESSION ACTIVE
                $activeSession = AttendanceSession::where('teacher_id', $user->id)
                    ->where('lesson_schedule_id', $schedule->id)
                    ->whereDate('date', today())
                    ->where('status', 'open')
                    ->first();

                // SESSION CLOSED
                $closedSession = AttendanceSession::where('teacher_id', $user->id)
                    ->where('lesson_schedule_id', $schedule->id)
                    ->whereDate('date', today())
                    ->where('status', 'closed')
                    ->exists();

                $schedule->active_session = $activeSession;
                $schedule->closed_session = $closedSession;

                // SESSION UNTUK ATTENDANCE
                $sessionForAttendance = AttendanceSession::where(
                    'lesson_schedule_id',
                    $schedule->id
                )
                    ->whereDate('date', today())
                    ->latest()
                    ->first();

                // AMBIL SISWA KELAS
                $students = Student::with('user')
                    ->where('class_id', $schedule->class_id)
                    ->get();

                // TEMPEL ATTENDANCE SISWA
                $students->map(function ($student) use (
                    $sessionForAttendance,
                    $schedule
                ) {

                    $attendance = null;

                    if ($sessionForAttendance) {

                        $attendance = Attendance::where(
                            'attendance_session_id',
                            $sessionForAttendance->id
                        )
                            ->where('lesson_schedule_id', $schedule->id)
                            ->where('user_id', $student->user_id)
                            ->first();
                    }

                    $student->attendance = $attendance;

                    return $student;
                });

                $schedule->students = $students;

                // IZIN EDIT RADIO
                $schedule->can_edit =
                    $schedule->active_session &&
                    !$schedule->closed_session;

                return $schedule;
            });

        $activeSchedule =
            $schedules->first(function ($item) {
                return $item->active_session;
            });

        if (!$activeSchedule) {

            // sedang berlangsung meski guru belum start
            $activeSchedule =
                $schedules->first(function ($item) {
                    return $item->is_started && !$item->is_ended;
                });
        }

        if (!$activeSchedule) {

            // jadwal berikutnya
            $activeSchedule =
                $schedules->first(function ($item) {
                    return !$item->is_started;
                });
        }

        return view('teacher.dashboard', compact(
            'schedules',
            'activeSchedule'
        ));
    }
}
