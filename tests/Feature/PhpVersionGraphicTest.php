<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
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

        $this->artisan('sync:php-version-graphic');

        Storage::disk('public')->assertExists('supported-versions.svg');
    }

    /** @test */
    public function it_does_not_store_html_response_from_php_net()
    {
        // If for some reason php.net
        Storage::fake('public');

        Http::fake([
            'https://www.php.net/images/supported-versions.php' => Http::response(file_get_contents('tests/responses/fake-supported-versions-not-found.svg')),
        ]);

        $this->artisan('sync:php-version-graphic');

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
        $this->artisan('sync:php-version-graphic');

        Storage::disk('public')->assertExists('supported-versions.svg');

        // Run the command again. Since there is no change, we should get an error code
        $this->artisan('sync:php-version-graphic')
            ->assertExitCode(1);
    }
}
