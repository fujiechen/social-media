<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('video:activate-medias')->daily();
        $schedule->command('video:sync-tag-category-actor-count')->daily();
        $schedule->command('video:sync-media-count')->daily();
        $schedule->command('video:process-media-recommendation')->hourly();
        $schedule->command('video:update-media-readyable')->hourly();
        $schedule->command('video:reset-user-roles')->daily();
        $schedule->command('video:reset-media-permissions')->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
