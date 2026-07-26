<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceSetting;

class AttendanceSettingController extends Controller
{
    public function index()
    {
        // ambil 1 data
        $setting = AttendanceSetting::first();

        return view('admin.attendance-setting.index', compact('setting'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'radius_meter' => 'required|numeric',
            'face_threshold' => 'required|numeric',
            'face_required_pass' => 'required|integer|min:1',
        ]);

        AttendanceSetting::updateOrCreate(
            ['id' => 1], // paksa 1 row
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'radius_meter' => $request->radius_meter,
                'face_threshold' => $request->face_threshold,
                'face_required_pass' => $request->face_required_pass,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Setting berhasil disimpan'
        ]);
    }
}