<?php

namespace App\Notifications;

use App\Models\Hit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class WeeklyStats extends Notification
{
    use Queueable;

    public function via()
    {
        return ['slack'];
    }

    public function toSlack()
    {
        $hits = Hit::forTimePeriod('week');

        $message = (new SlackMessage())
            ->attachment(function ($attachment) use ($hits) {
                $attachment->title('PHP Releases Weekly Hits!')
                    ->content(
                        "*Current Period*: {$hits['current']} \n *Previous Period*: {$hits['previous']} \n *Change*: {$hits['changePercent']}%"
                    )
                    ->markdown(['text']);
            });

        return $hits['changePercent'] >= 0
            ? $message->success()
            : $message->warning();
    }
}
