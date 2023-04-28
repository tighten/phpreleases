<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Hit extends Model
{
    use HasFactory;

    protected $fillable = ['endpoint', 'user_agent'];

    protected static function boot()
    {
        parent::boot();
    
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('created_at', 'desc');
        });
    }

    /**
     * @param  string  $period    Available Options: millenium, century, decade,
     *                          year, quarter, month, week, day, weekday, hour,
     *                          minute, second, microsecond
     */
    public static function forTimePeriod(string $period = 'week', CarbonImmutable $end = null): array
    {
        $currentPeriodEnd = $end ?? CarbonImmutable::now();
        $currentPeriodStart = $currentPeriodEnd->sub($period, 1)->addSecond();
        $previousPeriodEnd = $currentPeriodStart->subSecond();
        $previousPeriodStart = $previousPeriodEnd->sub($period, 1)->addSecond();

        $currentPeriodHits = Hit::hitsBetween(
            $currentPeriodStart,
            $currentPeriodEnd
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
