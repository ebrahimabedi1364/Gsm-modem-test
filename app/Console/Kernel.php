<?php

namespace App\Console;

use App\Jobs\RecieveMessage;
use App\Jobs\OrderCodeHourly;
use App\Console\Commands\OrderHourly;
use App\Jobs\OrderCodeEveryTenMinutes;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\OrderCodeEveryThirtyMinute;
use App\Jobs\OrderCodeEveryFifteenMinutes;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

      
        
        // $schedule->command('auto:recieveMessage')->everyThirtyMinutes();
        $schedule->job(new OrderCodeHourly)->hourly();
        $schedule->job(new OrderCodeEveryThirtyMinute)->everyThirtyMinutes();
        $schedule->job(new OrderCodeEveryFifteenMinutes)->everyFifteenMinutes();
        $schedule->job(new OrderCodeEveryTenMinutes)->everyTenMinutes();
        $schedule->job(new RecieveMessage)->everyThirtyMinutes()->withoutOverlapping(5);
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
