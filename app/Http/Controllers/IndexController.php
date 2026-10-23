<?php

namespace App\Http\Controllers;

use App\Console\Commands\SyncPhpReleaseGraphic;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class IndexController
{
    public function index(): View
    {
        $graphic = Cache::get(SyncPhpReleaseGraphic::CACHE_KEY);

        return view('index', [
            'graphic' => $graphic['svg'] ?? null,
            'graphicUpdatedAt' => isset($graphic['updated_at']) ? Carbon::createFromTimestamp($graphic['updated_at']) : null,
        ]);
    }
}
