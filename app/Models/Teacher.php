<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'name',
        'nip',
        'email',
    ];

    public function lessonSchedules()
    {
        return $this->hasMany(LessonSchedule::class);
    }
}
