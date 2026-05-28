<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceSession;
use Illuminate\Support\Facades\Auth;
use App\Models\LessonSchedule;
use App\Models\Attendance;

class AttendanceSessionController extends Controller
{
    public function start(Request $request)
    {
        $user = Auth::user();

        $schedule = LessonSchedule::findOrFail(
            $request->lesson_schedule_id
        );

        // cek apakah masih ada session OPEN
        // pada kelas yang sama
        $exists = AttendanceSession::whereDate('date', now())
            ->where('status', 'open')
            ->whereHas('schedule', function ($q) use ($schedule) {

                $q->where('class_id', $schedule->class_id);
            })
            ->exists();

        if ($exists) {

            return response()->json([
                'success' => false,
                'message' => 'Masih ada absensi aktif di kelas ini'
            ], 400);
        }

        // anti duplicate session
        $session = AttendanceSession::firstOrCreate(

            [
                'lesson_schedule_id' => $schedule->id,
                'date' => now()->toDateString(),
            ],

            [
                'teacher_id' => $user->id,
                'start_time' => now(),
                'gps_enabled' => $request->gps_enabled ?? false,
                'status' => 'open',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Absensi dimulai',
            'data' => $session
        ]);
    }

    public function end($id)
    {
        $session = AttendanceSession::where('id', $id)
            ->where('teacher_id', Auth::id())
            ->where('status', 'open')
            ->firstOrFail();

        $session->update([
            'status' => 'closed',
            'end_time' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi selesai'
        ]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'attendance_id' => 'nullable|exists:attendances,id',
            'user_id' => 'required|exists:users,id',
            'lesson_schedule_id' => 'required|exists:lesson_schedules,id',
            'status' => 'required|in:hadir,terlambat,izin,sakit,alfa',
        ]);

        $teacher = Auth::user();

        $session = AttendanceSession::where('teacher_id', $teacher->id)
            ->where('lesson_schedule_id', $request->lesson_schedule_id)
            ->whereDate('date', now())
            ->latest()
            ->first();

        // hanya boleh edit jika session OPEN
        if (!$session || $session->status !== 'open') {

            return response()->json([
                'success' => false,
                'message' => 'Session sudah ditutup'
            ], 403);
        }

        $attendance = Attendance::firstOrNew([
            'attendance_session_id' => $session->id,
            'lesson_schedule_id' => $request->lesson_schedule_id,
            'user_id' => $request->user_id,
        ]);

        $attendance->date = now()->toDateString();
        $attendance->check_in_time = now()->format('H:i:s');
        $attendance->status = $request->status;
        $attendance->method = 'manual';

        $attendance->save();

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diperbarui'
        ]);
    }
}
