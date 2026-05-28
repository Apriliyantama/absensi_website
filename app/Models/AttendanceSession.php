<?php

namespace App\Models;

use App\Models\LessonSchedule;
use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    protected $fillable = [
        'teacher_id',
        'lesson_schedule_id',
        'date',
        'start_time',
        'end_time',
        'gps_enabled',
        'status',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'attendance_session_id');
    }

    public function schedule()
    {
        return $this->belongsTo(LessonSchedule::class, 'lesson_schedule_id');
    }

}
