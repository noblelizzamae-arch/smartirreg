<?php

namespace App\Console;

use App\Services\BlynkService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Pull sensor data from Blynk Cloud every 2 minutes and save to DB.
     * Run: php artisan schedule:work
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            app(BlynkService::class)->pullAndSave();
        })->everyTwoMinutes()->name('blynk-pull')->withoutOverlapping();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
