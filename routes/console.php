<?php

use App\Console\Commands\SendStatsToSlack;
use App\Console\Commands\SyncPhpReleaseGraphic;
use App\Console\Commands\SyncPhpReleases;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(SyncPhpReleases::class)->twiceDaily();
Schedule::command(SyncPhpReleaseGraphic::class)->daily();
Schedule::command(SendStatsToSlack::class)->weeklyOn(5, '8:00');
