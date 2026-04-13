<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    protected $table = 'attendance_settings';

    protected $fillable = [
        'latitude',
        'longitude',
        'radius_meter',
        'face_threshold',
    ];
}
