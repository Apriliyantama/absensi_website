<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'check_in_time',
        'latitude',
        'longitude',
        'distance',
        'confidence_score',
        'status',
        'method',
    ];
}
