<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'name',
        'nip',
        'email',
        'user_id',
    ];

    public function lessonSchedules()
    {
        return $this->hasMany(LessonSchedule::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
