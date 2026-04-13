<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\FaceController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/face/register', [FaceController::class, 'register']);
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/face/verify', [FaceController::class, 'verify']);
    Route::post('/attendance/attend', [AttendanceController::class, 'attend']);
    Route::get('/attendance/current', [AttendanceController::class, 'current']);

    Route::get('/face/me', function (Request $request) {
        return \App\Models\FaceEmbedding::where('user_id', $request->user()->id)->latest()->first();
    });
});

Route::get('/attendance/settings', [AttendanceController::class, 'settings']);
