<?php

namespace App\Console\Commands;

use App\Notifications\WeeklyStats;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendStatsToSlack extends Command
{
    protected $signature = 'stats:send';

    protected $description = 'Sends weekly stats to slack';

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
