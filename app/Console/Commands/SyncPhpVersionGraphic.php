<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SyncPhpVersionGraphic extends Command
{
    protected $signature = 'sync:php-version-graphic';

    protected $description = 'Fetch the most recent Version Support graphic from https://www.php.net/images/supported-versions.php';

    public function handle()
    {
        $svgResponse = Http::get('https://www.php.net/images/supported-versions.php');

        // Check if we get an HTML response back, which means the image wasn't found
        if (str_contains($svgResponse, '<!DOCTYPE html>')) {
            Log::warning('Failed fetching the svg');

            return 1;
        }

        // Check if there are any changes to the SVG since we last synced it
        if (
            Storage::disk('public')->exists('supported-versions.svg')
            && Storage::disk('public')->get('supported-versions.svg') === $svgResponse->body()
        ) {
            return 0;
        }

        Storage::disk('public')->put('supported-versions.svg', $svgResponse);

        return 0;
    }
}
