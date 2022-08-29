<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hit extends Model
{
    use HasFactory;

    protected $fillable = ['endpoint', 'user_agent'];

    public static function forTimePeriod(string $period = 'week')
    {
        $now = CarbonImmutable::now();

        $currentPeriodHits = Hit::hitsBetween($now->sub($period, 1), $now);
        $previousPeriodHits = Hit::hitsBetween($now->sub($period, 2), $now->sub($period, 1)->subDay());

        return [
            'current' => $currentPeriodHits,
            'previous' => $previousPeriodHits,
            'change' => $previousPeriodHits
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
