<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\LessonSchedule;

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
            ->get();

        return view('teacher.dashboard', compact('schedules'));
    }
}