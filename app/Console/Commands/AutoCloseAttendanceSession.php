<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AttendanceSession;

class AutoCloseAttendanceSession extends Command
{
    protected $signature = 'attendance:auto-close';

    protected $description = 'Auto close attendance session';

    public function handle()
    {
        $now = now();

        $sessions = AttendanceSession::with('schedule')
            ->where('status', 'open')
            ->whereDate('date', today())
            ->get();

        $this->info('TOTAL SESSION: ' . $sessions->count());

        foreach ($sessions as $session) {
            $this->info("CHECK SESSION {$session->id}");
            if (!$session->schedule) {
                continue;
            }

            $endTime = \Carbon\Carbon::parse(
                today()->toDateString() . ' ' .
                    $session->schedule->end_time
            );

            if ($now->greaterThanOrEqualTo($endTime)) {
                $session->update([
                    'status' => 'closed',
                    'end_time' => now(),
                ]);
                $this->info(
                    "Session {$session->id} auto closed"
                );
            }
        }
        return Command::SUCCESS;
    }
}
