<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceSession;

class AttendanceOverviewController extends Controller
{
    public function index()
    {
        $sessions = AttendanceSession::with([
            'schedule.subject',
            'schedule.class',
            'attendances'
        ])
            ->where('teacher_id', Auth::id())
            ->latest()
            ->get();

        return view(
            'teacher.overviewpage.index',
            compact('sessions')
        );
    }

    public function show($id)
    {
        $session = AttendanceSession::with([
            'schedule.subject',
            'schedule.class',
            'attendances.user'
        ])->findOrFail($id);

        return view(
            'teacher.overviewpage.show',
            compact('session')
        );
    }
}
