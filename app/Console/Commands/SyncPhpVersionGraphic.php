<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SyncPhpVersionGraphic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:php-version-graphic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch the most recent Version Support graphic from https://www.php.net/images/supported-versions.php';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $svgResponse = Http::get('https://www.php.net/images/supported-versions.php');

        // checks if we get an html response back, means the image wasn't found
        if (str_contains($svgResponse, '<!DOCTYPE html>')) {
            Log::warning('Failed fetching the svg');

            return 1;
        }

        //checks if there are any changes to the svg
        if (
            Storage::disk('public')->exists('supported-versions.svg')
            && Storage::disk('public')->get('supported-versions.svg') === $svgResponse->body()
        ) {
            return 1;
        }

        Storage::disk('public')->put('supported-versions.svg', $svgResponse);

        Artisan::call('storage:link');

        //@todo update view with {!! file_get_contents('storage/supported-versions.svg') !!}

        return 0;
    }
}
