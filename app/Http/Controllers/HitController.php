<?php

namespace App\Http\Controllers;

use App\Models\Hit;

class HitController
{
    public function __invoke()
    {
        $stats = Hit::all();

        return view('stats', [
            'hits' => $stats,
            'week' => Hit::forTimePeriod('week'),
            'month' => Hit::forTimePeriod('month'),
            'year' => Hit::forTimePeriod('year'),
            'top' => $stats->countBy(function ($item) {
                return $item->endpoint;
            })->sortBy(function ($key, $value) {
                return $value;
            })->take(5),
        ]);
    }
}
