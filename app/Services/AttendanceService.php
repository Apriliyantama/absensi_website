<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\User;
use App\Models\LessonSchedule;

class AttendanceService
{
    public function canAttend(User $user, LessonSchedule $schedule): bool
    {
        return ! Attendance::where('user_id', $user->id)
            ->where('lesson_schedule_id', $schedule->id)
            ->whereDate('created_at', today())
            ->exists();
    }

    public function attend(User $user): array
    {
        return DB::transaction(function () use ($user) {

            // 1. Ambil jadwal aktif
            $scheduleService = app(LessonScheduleService::class);
            $schedule = $scheduleService->getCurrentLesson();

            if (!$schedule) {
                return [
                    'success' => false,
                    'status' => 'no_schedule',
                    'message' => 'Tidak ada jadwal aktif',
                ];
            }

            // 2. Cek boleh absen
            if (!$this->canAttend($user, $schedule)) {
                return [
                    'success' => false,
                    'status' => 'already_attended',
                    'message' => 'Anda sudah absen pada jadwal ini',
                ];
            }

            // 3. Tentukan status hadir / terlambat
            $status = $this->determineStatus($schedule);

            // 4. Simpan absensi
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'lesson_schedule_id' => $schedule->id,
                'date' => now()->toDateString(),
                'check_in_time' => now()->format('H:i:s'),
                'status' => $status,
            ]);

            return [
                'success' => true,
                'status' => $status,
                'message' => 'Absensi berhasil',
                'data' => $attendance,
            ];
        });
    }

    private function determineStatus(LessonSchedule $schedule): string
    {
        $now = Carbon::now();

        $start = Carbon::today()->setTimeFromTimeString($schedule->start_time);

        $toleranceLimit = $start->copy()
            ->addMinutes($schedule->tolerance_minutes);

        if ($now->lte($toleranceLimit)) {
            return 'hadir';
        }

        return 'terlambat';
    }
}
