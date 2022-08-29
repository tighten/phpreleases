<?php

namespace App\Http\Controllers;

use App\Models\Hit;
use Illuminate\Support\Facades\DB;

class HitController
{
    public function __invoke()
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

//            'top' => Hit::all()->countBy(function ($item) {
//                return $item->endpoint;
//            })->sortBy(function ($key, $value) {
//                return $value;
//            })->take(5),
        ]);
    }
}
