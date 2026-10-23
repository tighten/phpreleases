<?php

use App\Console\Commands\SyncPhpReleaseGraphic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

it('fetches the svg', function () {
    Http::fake([
        'https://www.php.net/images/supported-versions.php' => Http::response(file_get_contents('tests/responses/fake-supported-versions.svg')),
    ]);

    $this->artisan(SyncPhpReleaseGraphic::class);

    expect(Cache::get(SyncPhpReleaseGraphic::CACHE_KEY)['svg'])
        ->toBe(file_get_contents('tests/responses/fake-supported-versions.svg'));
});

it('does not store html response from php net', function () {
    Log::shouldReceive('warning')
        ->with('Failed fetching the svg');

    Http::fake([
        'https://www.php.net/images/supported-versions.php' => Http::response(file_get_contents('tests/responses/fake-supported-versions-not-found.svg')),
    ]);

    $this->artisan(SyncPhpReleaseGraphic::class);

    expect(Cache::has(SyncPhpReleaseGraphic::CACHE_KEY))->toBeFalse();
});

it('does not store if there are no changes', function () {
    Http::fake([
        'https://www.php.net/images/supported-versions.php' => Http::response(file_get_contents('tests/responses/fake-supported-versions.svg')),
    ]);

    // Create the initial svg
    $this->artisan(SyncPhpReleaseGraphic::class);

    $updatedAt = Cache::get(SyncPhpReleaseGraphic::CACHE_KEY)['updated_at'];

    $this->travel(2)->seconds();
    $this->artisan(SyncPhpReleaseGraphic::class);

    expect(Cache::get(SyncPhpReleaseGraphic::CACHE_KEY)['updated_at'])->toBe($updatedAt);
});

it('renders the homepage without a synced graphic', function () {
    $this->withoutVite()->get('/')->assertOk();
});

it('renders the synced graphic on the homepage', function () {
    Cache::forever(SyncPhpReleaseGraphic::CACHE_KEY, [
        'svg' => '<svg id="supported-versions"></svg>',
        'updated_at' => now()->subDays(2)->timestamp,
    ]);

    $this->withoutVite()->get('/')
        ->assertOk()
        ->assertSee('<svg id="supported-versions"></svg>', false)
        ->assertSee('Graphic updated');
});
