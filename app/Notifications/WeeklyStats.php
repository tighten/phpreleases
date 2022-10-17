<?php

namespace App\Notifications;

use App\Models\Hit;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NathanHeffley\LaravelSlackBlocks\Messages\SlackMessage;

class WeeklyStats extends Notification
{
    use Queueable;

    public function via()
    {
        return ['slack'];
    }

    public function toSlack()
    {
        $hits = Hit::forTimePeriod(
            'week',
            CarbonImmutable::today(config('app.timezone'))
                ->setTime(7, 0)
        );

        return (new SlackMessage())
            ->block(function ($block) use ($hits) {
                $block->type('header')
                    ->text([
                        'type' => 'plain_text',
                        'text' => 'PHP Releases Weekly Hits!',
                    ]);
            })
            ->block(function ($block) use ($hits) {
                $block->type('section')
                    ->fields([
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Current Period:*\n{$hits['current']}",
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Previous Period:*\n{$hits['previous']}",
                        ],
                    ]);
            })
            ->block(function ($block) use ($hits) {
                $emoji = $hits['changePercent'] > 0 ? ':arrow_up:' : ':arrow_down:';

                $block->type('section')
                    ->fields([
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Change:*\n{$emoji} {$hits['changePercent']}%",
                        ],
                    ]);
            })
            ->block(function ($block) use ($hits) {
                $block->type('section')
                    ->text([
                        'type' => 'mrkdwn',
                        'text' => 'See more detail at :link:phpreleases.com/stats',
                    ]);
            });
    }
}
