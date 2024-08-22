<?php

namespace App\Console;

<<<<<<< HEAD

use App\Jobs\OrderCodeEveryFifteenMinutes;
use App\Jobs\OrderCodeEveryTenMinutes;
use App\Jobs\OrderCodeEveryThirtyMinute;
use App\Jobs\OrderCodeHourly;
use App\Jobs\RecieveMessage;
=======
use App\Jobs\RecieveMessage;
use App\Jobs\OrderCodeHourly;
use App\Console\Commands\OrderHourly;
use App\Jobs\OrderCodeEveryTenMinutes;
>>>>>>> 7236aee05ab43b09269d845dae10fe3a52beeb13
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
<<<<<<< HEAD
        $schedule->job(new RecieveMessage)->everyThirtyMinutes();
=======
        $schedule->job(new RecieveMessage)->everyThirtyMinutes()->withoutOverlapping(5);
>>>>>>> 7236aee05ab43b09269d845dae10fe3a52beeb13
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
