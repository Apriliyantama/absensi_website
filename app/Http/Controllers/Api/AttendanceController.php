<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Services\FaceService;
use App\Services\LocationService;

class AttendanceController extends Controller
{
    public function settings()
    {
        return response()->json(
            AttendanceSetting::first()
        );
    }

    public function checkIn(Request $request, FaceService $faceService)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'embedding' => 'required|array|size:128',
        ]);

        $user = $request->user();
        // 1. cek sudah absen?
        $already = Attendance::where('user_id', $user->id)
            ->whereDate('date', today())
            ->exists();

        if ($already) {
            return response()->json([
                'message' => 'Anda sudah presensi hari ini'
            ], 422);
        }
        // 2. location
        $setting = AttendanceSetting::first();
        if (!$setting){
            return response()->json([
                'message' => 'Lokasi absensi belum diatur admin'
            ], 422);
        }
        // $locationService = app(LocationService::class);
        // $schoolLat = config('app.school_latitude');
        // $schoolLng = config('app.school_longitude');
        // $radius = config('app.school_radius');
        
        $distance = $this->haversine(
            $validated['latitude'],
            $validated['longitude'],
            $setting->latitude,
            $setting->longitude
        );

        if ($distance > $setting->radius_meter) {
            return response()->json([
                'message' => 'Anda berada di luar area absensi',
                'distance' => round($distance, 2)
            ], 403);
        }

        // 3. verify face
        $bestScore = $faceService->verifyEmbedding(
            $user->id,
            $validated['embedding']
        );

        $threshold = 0.80;
        if ($bestScore < $threshold) {
            return response()->json([
                'message' => 'Wajah tidak cocok'
            ], 403);
        }

        // 4. validasi gps
        // $setting = AttendanceSetting::first();

        // $distance = $this->haversine(
        //     $validated['latitude'],
        //     $validated['longitude'],
        //     $setting->latitude,
        //     $setting->longitude
        // );

        // $isValidLocation = $distance <= $setting->radius;

        // if (!$isValidLocation) {
        //     return response()->json([
        //         'message' => 'Diluar area absensi'
        //     ], 403);
        // }

        // 5. simpan absensi
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'check_in_time' => now()->toTimeString(),
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'distance' => $distance,
            'confidence_score' => $bestScore,
            'status' => 'hadir',
            'method' => 'face',
        ]);

        return response()->json([
            'message' => 'Presensi berhasil',
            'data' => $attendance,
        ], 201);
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

        return $earthRadius * $c; //meter
    }
}
