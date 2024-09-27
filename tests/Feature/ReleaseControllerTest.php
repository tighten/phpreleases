<?php

namespace Tests\Feature;

use App\Models\Release;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ReleaseControllerTest extends TestCase
{
    use DatabaseMigrations;

    #[Test]
    public function it_can_get_the_releases(): void
    {
        Release::factory()->count(12)->create();

        $this->getJson('/api/releases')->assertJsonCount(12);
    }

    #[Test]
    public function it_can_get_the_minimum_security_supported_release(): void
    {
        $now = CarbonImmutable::now();

        // release that should be returned
        Release::factory()->create([
            'major' => 7,
            'minor' => 3,
            'release' => 2,
            'security_support_until' => $now->addYear(),
        ]);

        // older release
        Release::factory()->create([
            'major' => 7,
            'minor' => 3,
            'release' => 1,
            'security_support_until' => $now->addYear(),
        ]);

        // newer major release
        Release::factory()->create([
            'major' => 8,
            'minor' => 0,
            'release' => 0,
            'security_support_until' => $now->addYears(2),
        ]);

        // unsupported release
        Release::factory()->create([
            'major' => 5,
            'minor' => 4,
            'release' => 0,
            'security_support_until' => $now->subYear(),
        ]);

        $this->getJson('api/releases/minimum-supported/security')
            ->assertJsonFragment([
                'major' => 7,
                'minor' => 3,
                'release' => 2,
            ]);
    }

    #[Test]
    public function it_can_get_the_minimum_active_supported_release(): void
    {
        $now = CarbonImmutable::now();

        // release that should be returned
        Release::factory()->create([
            'major' => 7,
            'minor' => 4,
            'release' => 2,
            'active_support_until' => $now->addYear(),
        ]);

        // older release
        Release::factory()->create([
            'major' => 7,
            'minor' => 4,
            'release' => 1,
            'active_support_until' => $now->addYear(),
        ]);

        // newer major release
        Release::factory()->create([
            'major' => 8,
            'minor' => 0,
            'release' => 0,
            'active_support_until' => $now->addYears(2),
        ]);

        // unsupported release
        Release::factory()->create([
            'major' => 7,
            'minor' => 3,
            'release' => 22,
            'active_support_until' => $now->subYear(),
        ]);

        $this->getJson('api/releases/minimum-supported/active')
            ->assertJsonFragment([
                'major' => 7,
                'minor' => 4,
                'release' => 2,
            ]);
    }

    #[Test]
    public function it_can_parse_a_php_release_and_return_all_details(): void
    {
        $currentRelease = Release::factory()->create([
            'major' => PHP_MAJOR_VERSION,
            'minor' => PHP_MINOR_VERSION,
            'release' => PHP_RELEASE_VERSION,
        ]);

        $phpVersion = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION;

        $this->getJson('api/releases/' . $phpVersion)
            ->assertJsonStructure([
                'provided' => [
                    'major',
                    'minor',
                    'release',
                    'tagged_at',
                    'active_support_until',
                    'security_support_until',
                    'needs_patch',
                    'needs_upgrade',
                    'changelog_url',
                ],
                'latest_release',
            ])
            ->assertJsonFragment([
                'major' => $currentRelease->major,
                'minor' => $currentRelease->minor,
                'release' => $currentRelease->release,
                'tagged_at' => $currentRelease->tagged_at,
                'active_support_until' => $currentRelease->active_support_until,
                'security_support_until' => $currentRelease->security_support_until,
            ]);
    }

    #[Test]
    public function it_returns_all_minor_releases_when_provided_major(): void
    {
        Release::factory()
            ->count(5)
            ->sequence(fn ($sequence) => [
                'major' => 8,
                'minor' => $sequence->index,
            ])
            ->create();

        Release::factory()
            ->count(3)
            ->sequence(fn ($sequence) => [
                'major' => 7,
                'minor' => $sequence->index,
            ])
            ->create();

        $this->getJson('api/releases/8')
            ->assertJsonStructure([
                '*' => [
                    'major',
                    'minor',
                    'release',
                    'tagged_at',
                    'active_support_until',
                    'security_support_until',
                    'needs_patch',
                    'needs_upgrade',
                    'changelog_url',
                ],
            ])
            ->assertJsonCount(5);

        $this->getJson('api/releases/7')
            ->assertJsonCount(3);
    }

    #[Test]
    public function it_returns_all_releases_when_provided_major_and_minor(): void
    {
        Release::factory()
            ->count(2)
            ->sequence(fn ($sequence) => [
                'major' => 7,
                'minor' => 4,
                'release' => $sequence->index,
            ])
            ->create();

        Release::factory()
            ->count(4)
            ->sequence(fn ($sequence) => [
                'major' => 8,
                'minor' => 0,
                'release' => $sequence->index,
            ])
            ->create();

        $this->getJson('api/releases/7.4')
            ->assertJsonCount(2);
        $this->getJson('api/releases/8.0')
            ->assertJsonCount(4);
        $this->getJson('api/releases/6.0')
            ->assertJsonCount(0);
    }

    #[Test]
    public function it_returns_the_latest_release(): void
    {
        Release::factory()
            ->count(3)
            ->sequence(fn ($sequence) => [
                'major' => 8,
                'minor' => 0,
                'release' => $sequence->index,
            ])
            ->create();

        Release::factory()
            ->count(2)
            ->sequence(fn ($sequence) => [
                'major' => 8,
                'minor' => 1,
                'release' => $sequence->index,
            ])
            ->create();

        $latest = Release::latestRelease()->first();

        $this->assertSame(8, $latest->major);
        $this->assertSame(1, $latest->minor);
        $this->assertSame(1, $latest->release);
    }

    #[Test]
    public function it_returns_correct_values_for_needs_patch(): void
    {
        $now = CarbonImmutable::now();

        $noPatch = Release::factory()->create([
            'major' => 7,
            'minor' => 4,
            'release' => 2,
            'active_support_until' => $now->addYear(),
        ]);

        $needsPatch = Release::factory()->create([
            'major' => 7,
            'minor' => 4,
            'release' => 1,
            'active_support_until' => $now->addYear(),
        ]);

        $this->assertTrue($needsPatch->refresh()->needs_patch);
        $this->assertFalse($noPatch->refresh()->needs_patch);
    }

    #[Test]
    public function it_can_get_the_latest_release(): void
    {
        $release = Release::factory()->create([
            'major' => 10,
            'minor' => 10,
            'release' => 0,
        ]);

        Release::factory()->create([
            'major' => 10,
            'minor' => 9,
            'release' => 0,
        ]);

        Release::factory()->create([
            'major' => 9,
            'minor' => 1,
            'release' => 0,
        ]);

        Release::factory()->create([
            'major' => 9,
            'minor' => 1,
            'release' => 0,
        ]);

        $this->get('api/releases/latest')
            ->assertJsonFragment([$release->__toString()]);
    }

    #[Test]
    public function it_returns_the_highest_release_number_as_latest_release(): void
    {
        $now = CarbonImmutable::now();

        Release::factory()->create([
            'major' => 7,
            'minor' => 4,
            'tagged_at' => now()->subDays(2),
        ]);

        $latest = Release::factory()->create([
            'major' => 8,
            'minor' => 0,
            'tagged_at' => now()->subDays(3),
        ]);

        Release::factory()->create([
            'major' => 7,
            'minor' => 3,
            'tagged_at' => now()->subDays(4),
        ]);

        $this->getJson('api/releases/latest')
            ->assertJsonFragment([$latest->__toString()]);
    }

    #[Test]
    public function it_returns_the_expected_value_for_changelog_url(): void
    {
        $release = Release::factory()->create();

        $this->assertSame(
            "https://www.php.net/ChangeLog-{$release->major}.php#{$release->__toString()}",
            $release->changelog_url
        );
    }

    #[Test]
    public function it_sorts_correctly(): void
    {
        Release::factory()
            ->count(5)
            ->sequence(fn ($sequence) => [
                'major' => 8,
                'minor' => $sequence->index,
                'tagged_at' => CarbonImmutable::today()->addDays($sequence->index),
            ])
            ->create();

        $response = $this->get('api/releases/8')
            ->assertJsonCount(5);

        //the first should be 8.0
        $this->assertEquals(4, $response[0]['minor']);
        //the final should be 8.4
        $this->assertEquals(0, $response[4]['minor']);
    }

    #[Test]
    public function it_validates_the_support_type(): void
    {
        $this->getJson('api/releases/minimum-supported/' . 'foo')
            ->assertJsonValidationErrors('supportType');
    }

    #[Test]
    public function it_validates_the_version_param(): void
    {
        Release::factory()->create([
            'major' => 10,
            'minor' => 9,
            'release' => 0,
        ]);

        $this->getJson('api/releases/' . 'string')
            ->assertJsonValidationErrors('major');

        $this->getJson('api/releases/10.' . 'string')
            ->assertJsonValidationErrors('minor');

        $this->getJson('api/releases/10.9.' . 'string')
            ->assertJsonValidationErrors('release');
    }
}
