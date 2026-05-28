<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\User;
// use Illuminate\Database\QueryException;



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

            $session = AttendanceSession::where('date', now()->toDateString())
                ->where('status', 'open')
                ->whereHas('schedule', function ($q) use ($user) {
                    $q->where('class_id', $user->class_id);
                })
                ->first();

            if (!$session) {

                return [
                    'success' => false,
                    'status' => 'no_session',
                    'message' => 'Absensi belum dimulai oleh guru',
                ];
            }

            $schedule = $session->schedule;

            $now = now();

            $status = $this->determineStatus($session, $now);

            $alreadyAttendance = Attendance::where(
                'user_id',
                $user->id
            )
                ->where(
                    'lesson_schedule_id',
                    $schedule->id
                )
                ->whereDate(
                    'date',
                    $now->toDateString()
                )
                ->exists();

            if ($alreadyAttendance) {

                return [
                    'success' => false,
                    'status' => 'already_attended',
                    'message' => 'Anda sudah absen pada sesi ini',
                ];
            }

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'attendance_session_id' => $session->id,
                'lesson_schedule_id' => $schedule->id,
                'date' => $now->toDateString(),
                'check_in_time' => $now->format('H:i:s'),
                'status' => $status,
                'method' => 'face',
            ]);

            return [
                'success' => true,
                'status' => $status,
                'message' => 'Absensi berhasil',
                'data' => $attendance,
            ];
        });
    }

    private function determineStatus($session, $now): string
    {
        $start = Carbon::parse($session->start_time);

        // toleransi 1 jam
        $limit = $start->copy()
            // ->addHour()
            ->addMinutes(5)
            ->addSeconds(10);

        return $now->lte($limit)
            ? 'hadir'
            : 'alfa';
    }

    public function getTodayAttendance($userId, $scheduleId)
    {
        return Attendance::where('user_id', $userId)
            ->where('lesson_schedule_id', $scheduleId)
            ->where('date', today()->toDateString())
            ->first();
    }
}
