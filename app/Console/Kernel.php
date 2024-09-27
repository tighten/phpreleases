<?php

namespace App\Console;

use App\Console\Commands\SendStatsToSlack;
use App\Console\Commands\SyncPhpReleaseGraphic;
use App\Console\Commands\SyncPhpReleases;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(SyncPhpReleases::class)->twiceDaily();
        $schedule->command(SyncPhpReleaseGraphic::class)->daily();
        $schedule->command(SendStatsToSlack::class)->weeklyOn(5, '8:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
