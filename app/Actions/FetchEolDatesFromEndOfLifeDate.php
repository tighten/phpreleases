<?php

namespace App\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class FetchEolDatesFromEndOfLifeDate
{
    public function __invoke(): Collection
    {
        return cache()->remember('endoflife::php', HOUR_IN_SECONDS, function () {
            $response = Http::get('https://endoflife.date/api/php.json');

            if (! $response->ok()) {
                abort($response->status(), 'Error fetching EOL data from endoflife.date');
            }

            return collect($response->json())->keyBy('cycle');
        });
    }
}
