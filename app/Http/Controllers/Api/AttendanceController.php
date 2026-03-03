<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\AttendanceSetting;

class AttendanceController extends Controller
{
    public function settings()
    {
        return response()->json(
            AttendanceSetting::first()
        );
    }

    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'distance' => 'required|numeric',
            'confidence_score' => 'required|numeric',
        ]);

        $attendance = Attendance::create([
            'user_id' => Auth::id(),
            'date' => now()->toDateString(),
            'check_in_time' => now()->toTimeString(),
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'distance' => $validated['distance'],
            'confidence_score' => $validated['confidence_score'],
            'method' => 'face',
        ]);

        return response()->json([
            'message' => 'Presensi berhasil',
            'data' => $attendance,
        ], 201);
    }
}
