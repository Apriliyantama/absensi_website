<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AttendanceSession;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'lesson_schedule_id',
        'attendance_session_id',
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

    //sesi aktifkan absen
    public function session()
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    //status kehadiran
    public const STATUS_HADIR = 'hadir';
    public const STATUS_TERLAMBAT = 'terlambat';
    public const STATUS_IZIN = 'izin';
    public const STATUS_SAKIT = 'sakit';
    public const STATUS_ALFA = 'alfa';

    public function getBadgeClassAttribute()
    {
        return match ($this->status) {

            self::STATUS_HADIR => 'success',

            self::STATUS_TERLAMBAT => 'warning',

            self::STATUS_IZIN => 'primary',

            self::STATUS_SAKIT => 'info',

            self::STATUS_ALFA => 'danger',

            default => 'secondary',
        };
    }
}
