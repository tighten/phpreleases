<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

#[Signature('sync:php-release-graphic')]
#[Description('Fetch the most recent Version Support graphic from https://www.php.net/images/supported-versions.php')]
class SyncPhpReleaseGraphic extends Command
{
    public const CACHE_KEY = 'supported-versions-graphic';

    public function handle(): int
    {
        $svgResponse = Http::get('https://www.php.net/images/supported-versions.php');

        // Check if we get an HTML response back, which means the image wasn't found
        if (str_contains($svgResponse, '<!DOCTYPE html>')) {
            Log::warning('Failed fetching the svg');

            return self::FAILURE;
        }

        // Check if there are any changes to the SVG since we last synced it
        if ((Cache::get(self::CACHE_KEY)['svg'] ?? null) === $svgResponse->body()) {
            return self::SUCCESS;
        }

        Cache::forever(self::CACHE_KEY, [
            'svg' => $svgResponse->body(),
            'updated_at' => now()->timestamp,
        ]);

        return self::SUCCESS;
    }
}
