<?php

namespace App\Http\Controllers;

use App\Models\Hit;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HitController
{
    public function __invoke(): View
    {
        return view('stats', [
            'hits' => Hit::simplePaginate(),
            'week' => Hit::forTimePeriod('week'),
            'month' => Hit::forTimePeriod('month'),
            'year' => Hit::forTimePeriod('year'),
            'top' => DB::table('hits')
                ->select(DB::raw('count(*) as count, endpoint'))
                ->groupBy('endpoint')
                ->orderByDesc('count')
                ->take(5)
                ->get(),
        ]);
    }
}
