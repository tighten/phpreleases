<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hit extends Model
{
    use HasFactory;

    protected $fillable = ['endpoint', 'user_agent'];

    /**
     * @param string $period    Available Options: millenium, century, decade,
     *                          year, quarter, month, week, day, weekday, hour,
     *                          minute, second, microsecond
     *
     * @return array
     */
    public static function forTimePeriod(string $period = 'week'): array
    {
        $now = CarbonImmutable::now();
        $currentPeriodStart = $now->sub($period, 1)->addSecond();
        $previousPeriodEnd = $currentPeriodStart->subSecond();
        $previousPeriodStart = $previousPeriodEnd->sub($period, 1)->addSecond();

        $currentPeriodHits = Hit::hitsBetween(
            $currentPeriodStart,
            $now
        )->count();

        $previousPeriodHits = Hit::hitsBetween(
            $previousPeriodStart,
            $previousPeriodEnd
        )->count();

        return [
            'current' => $currentPeriodHits,
            'previous' => $previousPeriodHits,
            'changePercent' => $previousPeriodHits
                ? intval((($currentPeriodHits - $previousPeriodHits) / $previousPeriodHits) * 100)
                : 100,
        ];
    }

    public static function hitsBetween(CarbonImmutable $start, CarbonImmutable $end)
    {
        return Hit::whereBetween('created_at', [
            $start->toDateTimeString(),
            $end->toDateTimeString(),
        ]);
    }
}
