<?php

namespace Tests\Feature;

use App\Console\Commands\SyncPhpReleaseGraphic;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhpVersionGraphicTest extends TestCase
{
    /** @test */
    public function it_fetches_the_svg()
    {
        Storage::fake('public');

        Http::fake([
            'https://www.php.net/images/supported-versions.php' => Http::response(file_get_contents('tests/responses/fake-supported-versions.svg')),
        ]);

        $this->artisan(SyncPhpReleaseGraphic::class);

        Storage::disk('public')->assertExists('supported-versions.svg');
    }

    /** @test */
    public function it_does_not_store_html_response_from_php_net()
    {
        Storage::fake('public');

        Log::shouldReceive('warning')
            ->with('Failed fetching the svg');

        Http::fake([
            'https://www.php.net/images/supported-versions.php' => Http::response(file_get_contents('tests/responses/fake-supported-versions-not-found.svg')),
        ]);

        $this->artisan(SyncPhpReleaseGraphic::class);

        Storage::disk('public')->assertMissing('supported-versions.svg');
    }

    /** @test */
    public function it_does_not_store_if_there_are_no_changes()
    {
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

        $this->assertSame($lastModified, Storage::disk('public')->lastModified('supported-versions.svg'));
    }
}
