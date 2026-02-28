<?php

use App\Console\Commands\SyncPhpReleaseGraphic;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(Tests\TestCase::class);

it('fetches the svg', function () {
    Storage::fake('public');

    Http::fake([
        'https://www.php.net/images/supported-versions.php' => Http::response(file_get_contents('tests/responses/fake-supported-versions.svg')),
    ]);

    $this->artisan(SyncPhpReleaseGraphic::class);

    Storage::disk('public')->assertExists('supported-versions.svg');
});

it('does not store html response from php net', function () {
    Storage::fake('public');

    Log::shouldReceive('warning')
        ->with('Failed fetching the svg');

    Http::fake([
        'https://www.php.net/images/supported-versions.php' => Http::response(file_get_contents('tests/responses/fake-supported-versions-not-found.svg')),
    ]);

    $this->artisan(SyncPhpReleaseGraphic::class);

    Storage::disk('public')->assertMissing('supported-versions.svg');
});

it('does not store if there are no changes', function () {
    Storage::fake('public');

    Http::fake([
        'https://www.php.net/images/supported-versions.php' => Http::response(file_get_contents('tests/responses/fake-supported-versions.svg')),
    ]);

    // Create the initial svg
    $this->artisan(SyncPhpReleaseGraphic::class);

    Storage::disk('public')->assertExists('supported-versions.svg');
    $lastModified = Storage::disk('public')->lastModified('supported-versions.svg');

    sleep(2);
    $this->artisan(SyncPhpReleaseGraphic::class);

    expect(Storage::disk('public')->lastModified('supported-versions.svg'))->toBe($lastModified);
});
