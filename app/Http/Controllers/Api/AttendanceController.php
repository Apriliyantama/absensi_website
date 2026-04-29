<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceSetting;
use App\Models\Attendance;
use App\Services\FaceService;
use App\Services\AttendanceService;
use App\Services\LessonScheduleService;
use App\Services\LocationService;

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
        ]);

        $user = $request->user();

        // ================= SETTING =================
        $setting = AttendanceSetting::first();

        if (!$setting) {
            return response()->json([
                'status' => 'no_setting',
                'message' => 'Lokasi absensi belum diatur admin'
            ], 422);
        }

        // ================= LOKASI =================
        $distance = $locationService->calculateDistance(
            $validated['latitude'],
            $validated['longitude'],
            $setting->latitude,
            $setting->longitude
        );

        if ($distance > $setting->radius_meter) {
            return response()->json([
                'status' => 'outside_area',
                'message' => 'Anda berada di luar area absensi',
                'distance' => round($distance, 2)
            ], 403);
        }

        // ================= FACE VERIFY =================
        $faceResult = $faceService->verifyWithThreshold(
            $user->id,
            $validated['embedding'],
            $setting->face_threshold ?? 0.78,
            $setting->face_required_pass ?? 3
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

        // ================= ATTEND =================
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
    public function current(LessonScheduleService $service, AttendanceService $attendanceService)
    {
        $result = $service->getCurrentLessonWithStatus();

        if (!isset($result['data'])) {
            return response()->json($result);
        }

        $schedule = $result['data'];

        $user = request()->user();

        $attendance = $attendanceService->getTodayAttendance(
            $user->id,
            $schedule->id
        );

        return response()->json([
            'status' => $result['status'],
            'message' => $result['message'],
            'data' => [
                'id' => $schedule->id,
                'subject' => $schedule->subject->name,
                'teacher' => $schedule->teacher->name,
                'class' => $schedule->class->name,
                'grade' => $schedule->class->grade,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,

                'attendance' => $attendance ? [
                    'status' => $attendance->status,
                    'check_in_time' => $attendance->check_in_time,
                ] : null,
            ]
        ]);
    }

    // jadwal berikutnya
    public function next()
    {
        $now = now();

        // sesuaikan dengan DB kamu (1=Senin)
        $today = $now->dayOfWeek == 0 ? 7 : $now->dayOfWeek;

        $currentTime = $now->format('H:i:s');

        $next = \App\Models\LessonSchedule::where('day_of_week', $today)
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
}
