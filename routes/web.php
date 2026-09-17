<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\StudentApprovalController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\AttendanceSettingController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Http\Controllers\Teacher\AttendanceOverviewController;
use App\Http\Controllers\Teacher\AttendanceSessionController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->role === 'admin'
            ? redirect('/admin/dashboard')
            : redirect('/teacher/dashboard');
    }

    return redirect()->route('login');
});

// DEBUG TEST ONLY
// Route::get('/force-logout', function () {
//     auth()->logout();
//     session()->invalidate();
//     session()->regenerateToken();
//     return redirect('/login');
// });
// DEBUG TEST ONLY
// Route::get('/check-auth', function () {
//     return [
//         'check' => auth()->check(),
//         'user' => auth()->user()
//     ];
// });

// Route::get('/', function () {
//     return view('auth.login');
// });

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/check-mode', function () {
    return config('app.attendance_mode');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Teacher
    Route::prefix('teachers')->name('teachers.')->group(function () {
        Route::get('/', [TeacherController::class, 'index'])->name('index');
        Route::get('/data', [TeacherController::class, 'data'])->name('data');
        Route::post('/store', [TeacherController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [TeacherController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [TeacherController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [TeacherController::class, 'destroy'])->name('destroy');
    });

    // Student Validity
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/approval', [StudentApprovalController::class, 'index'])->name('approval');
        Route::get('/approval/data', [StudentApprovalController::class, 'data'])->name('approval.data');
        Route::post('/approve/{id}', [StudentApprovalController::class, 'approve'])->name('approve');
        Route::post('/reject/{id}', [StudentApprovalController::class, 'reject'])->name('reject');
    });
    // Student Master Data
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('index');
        Route::get('/data', [StudentController::class, 'data'])->name('data');
        Route::get('/show/{id}',[StudentController::class, 'show'])->name('show');
        Route::get('/edit/{id}',[StudentController::class, 'edit'])->name('edit');
        Route::post('/update/{id}',[StudentController::class, 'update'])->name('update');
        Route::delete('/delete/{id}',[StudentController::class, 'destroy'])->name('destroy');
        Route::post('/promote', [StudentController::class, 'promote'])->name('promote');
    });

    // Student Class
    // Class
    Route::prefix('classes')->name('classes.')->group(function () {

        Route::get('/', [ClassController::class, 'index'])->name('index');
        Route::get('/data', [ClassController::class, 'data'])->name('data');
        Route::post('/store', [ClassController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [ClassController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [ClassController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [ClassController::class, 'destroy'])->name('destroy');
    });

    //Subject
    Route::prefix('subjects')->name('subjects.')->group(function () {
        Route::get('/', [SubjectController::class, 'index'])->name('index');
        Route::get('/data', [SubjectController::class, 'data'])->name('data');
        Route::post('/store', [SubjectController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [SubjectController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [SubjectController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [SubjectController::class, 'destroy'])->name('destroy');
    });

    //LessonSchedule
    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/', [ScheduleController::class, 'index'])->name('index');
        Route::get('/data', [ScheduleController::class, 'data'])->name('data');
        Route::post('/store', [ScheduleController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [ScheduleController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [ScheduleController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [ScheduleController::class, 'destroy'])->name('destroy');
    });

    //Attendance-setting
    Route::prefix('attendance-setting')->name('attendance.setting.')->group(function () {
        Route::get('/', [AttendanceSettingController::class, 'index'])
            ->name('index');
        Route::post('/store', [AttendanceSettingController::class, 'store'])
            ->name('store');
    });
});

Route::middleware(['auth', 'teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])
        ->name('dashboard');
    Route::post('/attendance/start', [\App\Http\Controllers\Teacher\AttendanceSessionController::class, 'start'])
        ->name('attendance.start');
    Route::post('/attendance/end/{id}', [\App\Http\Controllers\Teacher\AttendanceSessionController::class, 'end'])
        ->name('attendance.end');
    Route::get('/attendance-overview', [AttendanceOverviewController::class, 'index'])
        ->name('attendance.overview');
    Route::get('/attendance-overview/{id}', [AttendanceOverviewController::class, 'show'])
        ->name('attendance.overview.show');
    Route::post('/attendance/update-status', [AttendanceSessionController::class, 'updateStatus'])
        ->name('attendance.update-status');
    Route::post('/attendance-session/end/{id}', [AttendanceSessionController::class, 'end'])
        ->name('attendance.end');
});

require __DIR__ . '/auth.php';
