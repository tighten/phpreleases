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

        $currentPeriodHits = Hit::hitsBetween($now->sub($period, 1), $now);
        $previousPeriodHits = Hit::hitsBetween($now->sub($period, 2), $now->sub($period, 1)->subDay());

        return [
            'current' => $currentPeriodHits,
            'previous' => $previousPeriodHits,
            'changePercent' => $previousPeriodHits
                ? intval((($currentPeriodHits - $previousPeriodHits) / $previousPeriodHits) * 100)
                : 100,
        ];
    }

    private static function hitsBetween(CarbonImmutable $start, CarbonImmutable $end)
    {
        return Hit::whereBetween('created_at', [
            $start->toDateTimeString(),
            $end->toDateTimeString(),
        ])->count();
    }
}
