<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classes;
use App\Models\Attendance;

class DashboardController extends Controller
{
    public function index()
    {
        $studentCount = Student::count();
        $teacherCount = Teacher::count();
        $classCount = Classes::count();

        $attendanceToday = Attendance::whereDate(
            'date',
            today()
        )->count();

        $hadir = Attendance::whereDate('date', today())
            ->where('status', 'hadir')
            ->count();

        $izin = Attendance::whereDate('date', today())
            ->where('status', 'izin')
            ->count();

        $sakit = Attendance::whereDate('date', today())
            ->where('status', 'sakit')
            ->count();

        $alfa = Attendance::whereDate('date', today())
            ->where('status', 'alfa')
            ->count();

        $classLabels = [];
        $classTotals = [];

        foreach (Classes::all() as $class) {
            $classLabels[] =
                $class->grade . ' ' . $class->name;
            $classTotals[] =
                Student::where(
                    'class_id',
                    $class->id
                )->count();
        }

        return view('admin.dashboard', compact(
            'studentCount',
            'teacherCount',
            'classCount',
            'attendanceToday',
            'hadir',
            'izin',
            'sakit',
            'alfa',
            'classLabels',
            'classTotals'
        ));
    }
}
