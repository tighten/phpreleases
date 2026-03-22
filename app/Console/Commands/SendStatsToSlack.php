<?php

namespace App\Console\Commands;

use App\Notifications\WeeklyStats;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

#[Signature('stats:send')]
#[Description('Sends weekly stats to slack')]
class SendStatsToSlack extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        Notification::route('slack', config('services.slack.webhook'))
            ->notify(new WeeklyStats);

        return self::SUCCESS;
    }
}
