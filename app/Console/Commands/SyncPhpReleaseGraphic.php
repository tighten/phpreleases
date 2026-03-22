<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

#[Signature('sync:php-release-graphic')]
#[Description('Fetch the most recent Version Support graphic from https://www.php.net/images/supported-versions.php')]
class SyncPhpReleaseGraphic extends Command
{
    public function handle(): int
    {
        $svgResponse = Http::get('https://www.php.net/images/supported-versions.php');

        // Check if we get an HTML response back, which means the image wasn't found
        if (str_contains($svgResponse, '<!DOCTYPE html>')) {
            Log::warning('Failed fetching the svg');

            return self::FAILURE;
        }

        // Check if there are any changes to the SVG since we last synced it
        if (
            Storage::disk('public')->exists('supported-versions.svg')
            && Storage::disk('public')->get('supported-versions.svg') === $svgResponse->body()
        ) {
            return self::SUCCESS;
        }

        Storage::disk('public')->put('supported-versions.svg', $svgResponse);

        return self::SUCCESS;
    }
}
