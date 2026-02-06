<?php

namespace App\Console;

use App\Console\Commands\DispatchSmsJobs;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('sms:dispatch-due')->everyMinute();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
