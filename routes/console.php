<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('attendance:auto-close')
    ->everyMinute();

Schedule::command('attendance:auto-complete-absent-teacher')
    ->everyMinute();

Schedule::command('attendance:auto-alpha')
    ->everyMinute();
