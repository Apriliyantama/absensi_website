<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\StudentApprovalController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\AttendanceSettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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

require __DIR__ . '/auth.php';
