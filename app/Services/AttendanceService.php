<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\User;
// use App\Models\LessonSchedule;
// use App\Models\LessonSchedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;



class AttendanceService
{
    protected $scheduleService;

    public function __construct(LessonScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function attend(User $user): array
    {
        return DB::transaction(function () use ($user) {

            //current schedule
            $schedule = $this->scheduleService->getCurrentLesson();

            if (!$schedule) {
                return [
                    'success' => false,
                    'status' => 'no_schedule',
                    'message' => 'Tidak ada jadwal aktif',
                ];
            }

            $now = now();
            //determine status
            $status = $this->determineStatus($schedule, $now);

            try {
                //insert
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'lesson_schedule_id' => $schedule->id,
                    'date' => $now->toDateString(),
                    'check_in_time' => $now->format('H:i:s'),
                    'status' => $status,
                ]);
            } catch (QueryException $e) {
                // HANDLE DUPLICATE (UNIQUE CONSTRAINT)
                if ($e->getCode() == 23000) {

                    Log::warning('DOUBLE ATTEND ATTEMPT', [
                        'user_id' => $user->id,
                        'schedule_id' => $schedule->id,
                        'date' => $now->toDateString(),
                    ]);

                    return [
                        'success' => false,
                        'status' => 'already_attended',
                        'message' => 'Anda sudah absen pada jadwal ini',
                    ];
                }

                // error lain
                throw $e;
            }

            Log::info('ATTENDANCE SUCCESS', [
                'user_id' => $user->id,
                'schedule_id' => $schedule->id,
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

    // public function canAttend(User $user, LessonSchedule $schedule): bool
    // {
    //     return ! Attendance::where('user_id', $user->id)
    //         ->where('lesson_schedule_id', $schedule->id)
    //         ->whereDate('created_at', today())
    //         ->exists();
    // }

    private function determineStatus($schedule, $now): string
    {
        $start = Carbon::today()->setTimeFromTimeString($schedule->start_time);

        $toleranceLimit = $start->copy()
            ->addMinutes($schedule->tolerance_minutes)
            ->addSeconds(10); // buffer anti delay network

        return $now->lte($toleranceLimit) ? 'hadir' : 'terlambat';
    }

    public function getTodayAttendance($userId, $scheduleId)
    {
        return Attendance::where('user_id', $userId)
            ->where('lesson_schedule_id', $scheduleId)
            ->where('date', today()->toDateString())
            ->first();
    }
}
