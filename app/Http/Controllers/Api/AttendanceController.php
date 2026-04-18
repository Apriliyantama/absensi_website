<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceSetting;
use App\Services\FaceService;
use App\Services\AttendanceService;
use App\Services\LessonScheduleService;

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
        AttendanceService $attendanceService
    ) {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'embedding' => 'required|array|size:128',
        ]);

        $user = $request->user();

        // ================= 1. CEK LOKASI =================
        $setting = AttendanceSetting::first();

        if (!$setting) {
            return response()->json([
                'status' => 'no_setting',
                'message' => 'Lokasi absensi belum diatur admin'
            ], 422);
        }

        $distance = $this->haversine(
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

        // ================= 2. VERIFY WAJAH =================
        $bestScore = $faceService->verifyEmbedding(
            $user->id,
            $validated['embedding']
        );

        $threshold = $setting->face_threshold ?? 0.8;

        if ($bestScore < $threshold) {
            return response()->json([
                'status' => 'face_not_match',
                'message' => 'Wajah tidak cocok'
            ], 403);
        }

        // ================= 3. ABSENSI (ATOMIC ENGINE) =================
        $result = $attendanceService->attend($user);

        // ================= 4. TAMBAHAN DATA =================
        if ($result['success']) {
            $attendance = $result['data'];

            // update data tambahan (GPS + face score)
            $attendance->update([
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'distance' => $distance,
                'confidence_score' => $bestScore,
                'method' => 'face',
            ]);

            return response()->json([
                'success' => true,
                'status' => $result['status'],
                'message' => $result['message'],
                'distance' => round($distance),
                'confidence' => $bestScore,
                'data' => $attendance,
            ]);
        }

        // gagal (no_schedule / already_attended)
        return response()->json([
            'success' => false,
            'status' => $result['status'],
            'message' => $result['message'],
        ], 422);
    }

    //jadwal aktif saat ini
    public function current(LessonScheduleService $service)
    {
        $result = $service->getCurrentLessonWithStatus();

        if (!isset($result['data'])) {
            return response()->json($result);
        }

        $schedule = $result['data'];

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

    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a =
            sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) *
            cos(deg2rad($lat2)) *
            sin($dLon / 2) *
            sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
