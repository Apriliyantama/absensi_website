<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\LessonSchedule;

class LessonScheduleService
{
    public function getCurrentLesson()
    {
        $now = Carbon::now();

        $day = $now->dayOfWeekIso; // 1-7
        $time = $now->format('H:i:s');

        return LessonSchedule::with(['teacher', 'class', 'subject'])
            ->where('is_active', true)
            ->where('day_of_week', $day)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time)
            ->first();
    }

    public function getCurrentLessonWithStatus()
    {
        $now = now();
        $day = $now->dayOfWeek == 0 ? 7 : $now->dayOfWeek;
        $time = $now->format('H:i:s');

        // ambil semua jadwal hari ini
        $schedules = LessonSchedule::with(['teacher', 'subject', 'class'])
            ->where('day_of_week', $day)
            ->where('is_active', 1)
            ->orderBy('start_time')
            ->get();

        if ($schedules->isEmpty()) {
            return [
                'status' => 'no_schedule',
                'message' => 'Tidak ada jadwal hari ini'
            ];
        }

        foreach ($schedules as $schedule) {
            if ($time < $schedule->start_time) {
                return [
                    'status' => 'not_started',
                    'message' => 'Belum waktunya',
                    'data' => $schedule
                ];
            }

            if ($time >= $schedule->start_time && $time <= $schedule->end_time) {
                return [
                    'status' => 'active',
                    'message' => 'Sedang berlangsung',
                    'data' => $schedule
                ];
            }
        }

        return [
            'status' => 'finished',
            'message' => 'Semua jadwal hari ini selesai'
        ];
    }

    public function determineAttendanceStatus(?LessonSchedule $schedule): array
    {
        if (!$schedule) {
            return [
                'status' => 'no_schedule',
                'message' => 'Tidak ada jadwal aktif sekarang'
            ];
        }

        $now = now();

        $start = Carbon::parse($schedule->start_time);
        $end   = Carbon::parse($schedule->end_time);

        if ($now->lt($start)) {
            return [
                'status' => 'early',
                'message' => 'Belum waktu absensi'
            ];
        }

        if ($now->gt($end)) {
            return [
                'status' => 'rejected',
                'message' => 'Jadwal sudah berakhir'
            ];
        }

        return [
            'status' => 'active',
            'message' => 'Jadwal aktif'
        ];
    }
}
