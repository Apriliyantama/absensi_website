<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Classes;

class LessonSchedule extends Model
{
    protected $fillable = [
        'class_id',
        'subject_id',
        'teacher_id',
        'day_of_week',
        'start_time',
        'end_time',
        'tolerance_minutes',
    ];

    //guru
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    //jadwal milik kelas
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    //mapel
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    //absensi pada jadwal ini
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
