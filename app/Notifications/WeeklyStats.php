<?php

namespace App\Notifications;

use App\Models\Hit;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackMessage;

class WeeklyStats extends Notification
{
    use Queueable;

    public function via(): array
    {
        return ['slack'];
    }

    public function toSlack(): SlackMessage
    {
        $hits = Hit::forTimePeriod(
            'week',
            CarbonImmutable::today(config('app.timezone'))
                ->setTime(7, 0)
        );

        return (new SlackMessage)
            ->headerBlock('PHP Releases Weekly API Hits!')
            ->sectionBlock(function (SectionBlock $block) use ($hits) {
                $block->field("*Current Period:*\n{$hits['current']}")->markdown();
                $block->field("*Previous Period:*\n{$hits['previous']}")->markdown();
            })
            ->sectionBlock(function (SectionBlock $block) use ($hits) {
                $emoji = $hits['changePercent'] > 0 ? ':arrow_up:' : ':arrow_down:';
                $block->text("*Change:*\n{$emoji} {$hits['changePercent']}%")->markdown();
            })
            ->dividerBlock()
            ->sectionBlock(function (SectionBlock $block) {
                $block->text("See more detail at :link:phpreleases.com/stats")->markdown();
            });
    }
}
