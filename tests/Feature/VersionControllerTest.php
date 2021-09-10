<?php

namespace Tests\Feature;

use App\Models\Version;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class VersionControllerTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_can_get_the_versions()
    {
        Version::factory()->count(12)->create();

        $this->getJson('/api/versions')->assertJsonCount(12);
    }

    /** @test */
    public function it_can_get_the_minimum_security_supported_version()
    {
        $now = CarbonImmutable::now();

        // version that should be returned
        Version::factory()->create([
            'major' => 7,
            'minor' => 3,
            'release' => 2,
            'security_support_until' => $now->addYear(),
        ]);

        //older release
        Version::factory()->create([
            'major' => 7,
            'minor' => 3,
            'release' => 1,
            'security_support_until' => $now->addYear(),
        ]);

        // newer major version
        Version::factory()->create([
            'major' => 8,
            'minor' => 0,
            'release' => 0,
            'security_support_until' => $now->addYears(2),
        ]);

        // unsupported version
        Version::factory()->create([
            'major' => 5,
            'minor' => 4,
            'release' => 0,
            'security_support_until' => $now->subYear(),
        ]);

        $this->getJson('api/versions/minimum-supported/security')
            ->assertJsonFragment([
                'major' => '7',
                'minor' => '3',
                'release' => '2',
            ]);
    }

    /** @test */
    public function it_can_get_the_minimum_active_supported_version()
    {
        $now = CarbonImmutable::now();

        // version that should be returned
        Version::factory()->create([
            'major' => 7,
            'minor' => 4,
            'release' => 2,
            'active_support_until' => $now->addYear(),
        ]);

        //older release
        Version::factory()->create([
            'major' => 7,
            'minor' => 4,
            'release' => 1,
            'active_support_until' => $now->addYear(),
        ]);

        // newer major version
        Version::factory()->create([
            'major' => 8,
            'minor' => 0,
            'release' => 0,
            'active_support_until' => $now->addYears(2),
        ]);

        // unsupported version
        Version::factory()->create([
            'major' => 7,
            'minor' => 3,
            'release' => 22,
            'active_support_until' => $now->subYear(),
        ]);

        $this->getJson('api/versions/minimum-supported/active')
            ->assertJsonFragment([
                'major' => '7',
                'minor' => '4',
                'release' => '2',
            ]);
    }

    /** @test */
    public function it_can_parse_a_php_version_and_return_all_details()
    {
        $currentVersion = Version::factory()->create([
            'major' => PHP_MAJOR_VERSION,
            'minor' => PHP_MINOR_VERSION,
            'release' => PHP_RELEASE_VERSION,
        ]);

        $this->getJson('api/versions/' . phpversion('tidy'))
            ->assertJsonFragment([
                'major' => (string) $currentVersion->major,
                'minor' => (string) $currentVersion->minor,
                'release' => (string) $currentVersion->release,
                'tagged_at' => $currentVersion->tagged_at,
                'active_support_until' => $currentVersion->active_support_until,
                'security_support_until' => $currentVersion->security_support_until,
            ]);
    }

    /** @test */
    public function it_returns_all_minor_versions_when_provided_major()
    {
        Version::factory()
            ->count(5)
            ->sequence(fn ($sequence) => [
                'major' => 8,
                'minor' => $sequence->index,
            ])
            ->create();

        Version::factory()
            ->count(3)
            ->sequence(fn ($sequence) => [
                'major' => 7,
                'minor' => $sequence->index,
            ])
            ->create();

        $this->getJson('api/versions/8')
            ->assertJsonCount(5);

        $this->getJson('api/versions/7')
            ->assertJsonCount(3);
    }

    /** @test */
    public function it_returns_all_releases_when_provided_major_and_minor()
    {
        Version::factory()
            ->count(2)
            ->sequence(fn ($sequence) => [
                'major' => 7,
                'minor' => 4,
                'release' => $sequence->index,
            ])
            ->create();

        Version::factory()
            ->count(4)
            ->sequence(fn ($sequence) => [
                'major' => 8,
                'minor' => 0,
                'release' => $sequence->index,
            ])
            ->create();

        $this->getJson('api/versions/7.4')
            ->assertJsonCount(2);
        $this->getJson('api/versions/8.0')
            ->assertJsonCount(4);
        $this->getJson('api/versions/6.0')
            ->assertJsonCount(0);
    }

    /** @test */
    public function it_returns_the_latest_release()
    {
        Version::factory()
            ->count(3)
            ->sequence(fn ($sequence) => [
                'major' => 8,
                'minor' => 0,
                'release' => $sequence->index,
            ])
            ->create();

        Version::factory()
            ->count(2)
            ->sequence(fn ($sequence) => [
                'major' => 8,
                'minor' => 1,
                'release' => $sequence->index,
            ])
            ->create();

        $latest = Version::latestRelease()->first();

        $this->assertSame('8', $latest->major);
        $this->assertSame('1', $latest->minor);
        $this->assertSame('1', $latest->release);
    }

    /** @test */
    public function it_returns_correct_values_for_needs_patch()
    {
        $now = CarbonImmutable::now();

        $noPatch = Version::factory()->create([
            'major' => 7,
            'minor' => 4,
            'release' => 2,
            'active_support_until' => $now->addYear(),
        ]);

        $needsPatch = Version::factory()->create([
            'major' => 7,
            'minor' => 4,
            'release' => 1,
            'active_support_until' => $now->addYear(),
        ]);

        $this->assertTrue($needsPatch->refresh()->needs_patch);
        $this->assertFalse($noPatch->refresh()->needs_patch);
    }

    /** @test */
    public function it_can_get_the_latest_version()
    {
          $version = Version::factory()->create([
              'major' => 10,
              'minor' => 10,
              'release' => 0,
          ]);

          Version::factory()->create([
              'major' => 10,
              'minor' => 9,
              'release' => 0,
          ]);

          Version::factory()->create([
              'major' => 9,
              'minor' => 1,
              'release' => 0,
          ]);

        Version::factory()->create([
            'major' => 9,
            'minor' => 1,
            'release' => 0,
        ]);

        $this->get('api/versions/latest')
            ->assertJsonFragment([$version->__toString()]);
    }

    /** @test */
    public function it_returns_the_highest_version_number_as_latest_release()
    {
        $now = CarbonImmutable::now();

        Version::factory()->create([
            'major' => 7,
            'minor' => 4,
            'tagged_at' => now()->subDays(2),
        ]);

        $latest = Version::factory()->create([
            'major' => 8,
            'minor' => 0,
            'tagged_at' => now()->subDays(3),
        ]);

        $this->getJson('api/versions/latest')
            ->assertJsonFragment([$latest->__toString()]);
    }
}
