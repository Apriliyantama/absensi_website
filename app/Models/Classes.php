<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    protected $fillable = [
        'name',
        'grade',
    ];

    // satu kelas memiliki banyak siswa
    public function students()
    {
        return $this->hasMany(User::class);
    }

    public function schedules()
    {
        return $this->hasMany(LessonSchedule::class);
    }
}
