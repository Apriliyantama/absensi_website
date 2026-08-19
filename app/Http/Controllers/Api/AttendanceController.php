<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceSetting;
use App\Models\Attendance;
use App\Services\FaceService;
use App\Services\AttendanceService;
use App\Models\LessonSchedule;
use App\Services\LocationService;
use App\Models\AttendanceSession;

class AttendanceController extends Controller
{
    public function settings()
    {
        $setting = \App\Models\AttendanceSetting::first();

        if (!$setting) {
            return response()->json([
                'message' => 'Setting lokasi belum diatur'
            ], 404);
        }

        return response()->json($setting);
    }

    public function attend(
        Request $request,
        FaceService $faceService,
        AttendanceService $attendanceService,
        LocationService $locationService
    ) {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'embedding' => 'required|array|size:512',
            'raw_captures' => 'nullable|array',
        ]);

        $user = $request->user();

        // SETTING
        $setting = AttendanceSetting::first();

        if (!$setting) {
            return response()->json([
                'status' => 'no_setting',
                'message' => 'Lokasi absensi belum diatur admin'
            ], 422);
        }

        // LOKASI
        $distance = $locationService->calculateDistance(
            $validated['latitude'],
            $validated['longitude'],
            $setting->latitude,
            $setting->longitude
        );

        $classId = $user->student->class_id;
        $session = AttendanceSession::where('date', now()->toDateString())
            ->where('status', 'open')
            ->whereHas('schedule', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            })
            ->first();

        if ($session && $session->gps_enabled) {
            if ($distance > $setting->radius_meter) {
                return response()->json([
                    'status' => 'outside_area',
                    'message' => 'Anda di luar area',
                    'distance' => round($distance, 2)
                ], 403);
            }
        }

        // FACE VERIFY
        $faceResult = $faceService->verifyWithThreshold(
            $user->id,
            $validated['embedding'],
            $setting->face_threshold ?? 0.78,
            $setting->face_required_pass ?? 3,
            $request->input('raw_captures')
        );

        if ($faceResult['reason'] ?? null === 'no_face_data') {
            return response()->json([
                'status' => 'no_face_data',
                'message' => 'Data wajah belum tersedia',
            ], 422);
        }

        if (!$faceResult['match']) {
            return response()->json([
                'status' => 'face_not_match',
                'message' => 'Wajah tidak cocok',
                'passed' => $faceResult['pass_count'],
                'best_score' => $faceResult['best_score'],
            ], 403);
        }

        // ATTEND
        $result = $attendanceService->attend($user);

        if ($result['success']) {
            $attendance = $result['data'];

            $attendance->update([
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'distance' => $distance,
                'confidence_score' => $faceResult['best_score'],
                'method' => 'face',
            ]);

            return response()->json([
                'success' => true,
                'status' => $result['status'],
                'message' => $result['message'],
                'distance' => round($distance),
                'confidence' => $faceResult['best_score'],
                'data' => $attendance,
            ]);
        }

        return response()->json([
            'success' => false,
            'status' => $result['status'],
            'message' => $result['message'],
        ], 422);
    }

    //jadwal aktif saat ini 
    public function getCurrentLessonWithStatus()
    {
        $now = now();

        $today = $now->dayOfWeekIso;

        $currentTime = $now->format('H:i:s');

        $user = request()->user();

        // CARI JADWAL BERDASARKAN JAM
        $schedule = LessonSchedule::with([
            'subject',
            'teacher',
            'class'
        ])
            ->where('class_id', $user->student->class_id)
            ->where('day_of_week', $today)
            ->whereTime('start_time', '<=', $currentTime)
            ->whereTime('end_time', '>=', $currentTime)
            ->first();

        if (!$schedule) {

            return response()->json([
                'status' => 'empty',
                'message' => 'Tidak ada jadwal saat ini',
                'data' => null
            ]);
        }

        // CEK SESSION
        $session = AttendanceSession::where(
            'lesson_schedule_id',
            $schedule->id
        )
            ->whereDate('date', now()->toDateString())
            ->latest()
            ->first();

        // SESSION CLOSED MANUAL
        $sessionClosed =
            $session &&
            $session->status === 'closed' &&
            $session->start_time != null;

        // SESSION OPEN
        $sessionOpen =
            $session &&
            $session->status === 'open';

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal ditemukan',

            'data' => [
                'id' => $schedule->id,
                'subject' => $schedule->subject->name,
                'teacher' => $schedule->teacher->name,
                'class' => $schedule->class->name,
                'grade' => $schedule->class->grade,

                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,

                'session_open' => $sessionOpen,
                'session_closed' => $sessionClosed,

                'gps_enabled' => $session?->gps_enabled,
            ]
        ]);
    }

    // jadwal berikutnya
    public function next(Request $request)
    {
        $now = now();

        // sesuaikan dengan DB
        $today = $now->dayOfWeekIso;

        $currentTime = $now->format('H:i:s');

        $user = $request->user();

        $next = \App\Models\LessonSchedule::where('day_of_week', $today)
            ->where('class_id', $user->student->class_id)
            ->where('start_time', '>', $currentTime)
            ->with(['subject'])
            ->orderBy('start_time')
            ->first();

        if (!$next) {
            return response()->json([
                'finished' => true,
                'message' => 'Semua mata pelajaran telah selesai di hari ini'
            ]);
        }

        return response()->json([
            'finished' => false,
            'data' => [
                'subject' => $next->subject->name,
                'start_time' => $next->start_time,
                'end_time' => $next->end_time,
            ]
        ]);
    }

    public function todayStatus(Request $request)
    {
        $user = $request->user();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', today()->toDateString())
            ->latest()
            ->first();

        return response()->json([
            'attendance' => $attendance ? [
                'status' => $attendance->status,
                'check_in_time' => $attendance->check_in_time,
            ] : null
        ]);
    }
    // JADWAL KESELURUHAN HARI INI UNTUK SCREEN 2
    public function getTodaySchedules(Request $request)
    {
        // 1. Ambil tanggal dari parameter, jika tidak ada gunakan hari ini (real-time)
        $requestedDate = $request->input('date');
        $dateTarget = $requestedDate ? \Carbon\Carbon::parse($requestedDate) : now();
        
        $dayOfWeek = $dateTarget->dayOfWeekIso; 
        $user = $request->user();
        $classId = $user->student->class_id;

        // 2. Ambil jadwal
        $schedules = LessonSchedule::with(['subject', 'teacher', 'class'])
            ->where('class_id', $classId)
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('start_time', 'asc')
            ->get();

        // 3. Format response
        $data = $schedules->map(function ($schedule) use ($user, $dateTarget) {
            
            $session = AttendanceSession::where('lesson_schedule_id', $schedule->id)
                ->whereDate('date', $dateTarget->toDateString())
                ->latest()
                ->first();

            $isAttended = false;
            if ($session) {
                $isAttended = Attendance::where('user_id', $user->id)
                    ->where('attendance_session_id', $session->id)
                    ->exists();
            }
            $isToday = $dateTarget->isToday();
            $currentTime = now()->format('H:i:s');
            // is_active akan bernilai FALSE jika user melihat jadwal besok/kemarin
            $isActive = $isToday && ($currentTime >= $schedule->start_time && $currentTime <= $schedule->end_time);

            return [
                'id' => $schedule->id,
                'subject' => $schedule->subject->name,
                'teacher' => $schedule->teacher->name,
                'class' => $schedule->class->grade . ' ' . $schedule->class->name,
                'start_time' => date('H:i', strtotime($schedule->start_time)),
                'end_time' => date('H:i', strtotime($schedule->end_time)),
                'is_active' => $isActive, 
                'session_open' => $session && $session->status === 'open',
                'is_attended' => $isAttended,
                'gps_enabled' => $session ? $session->gps_enabled : false,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar jadwal pelajaran',
            'data' => $data
        ]);
    }
}
