<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'lesson_schedule_id',
        'date',
        'check_in_time',
        'latitude',
        'longitude',
        'distance',
        'confidence_score',
        'status',
        'method',
    ];

    //relasi jadwal
    public function schedule()
    {
        return $this->belongsTo(LessonSchedule::class, 'lesson_schedule_id');
    }

    //murid
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
