<?php

use App\Models\Release;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseMigrations;

uses(DatabaseMigrations::class);

it('can get the releases', function () {
    Release::factory()->count(12)->create();

    $this->getJson('/api/releases')->assertJsonCount(12);
});

it('can get the minimum security supported release', function () {
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
});

it('can get the minimum active supported release', function () {
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
});

it('can parse a php release and return all details', function () {
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
});

it('returns all minor releases when provided major', function () {
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
});

it('returns all releases when provided major and minor', function () {
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
});

it('returns the latest release', function () {
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

    expect($latest->major)->toBe(8);
    expect($latest->minor)->toBe(1);
    expect($latest->release)->toBe(1);
});

it('returns correct values for needs patch', function () {
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

    expect($needsPatch->refresh()->needs_patch)->toBeTrue();
    expect($noPatch->refresh()->needs_patch)->toBeFalse();
});

it('can get the latest release', function () {
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
});

it('returns the highest release number as latest release', function () {
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
});

it('returns the expected value for changelog url', function () {
    $release = Release::factory()->create();

    $this->assertSame(
        "https://www.php.net/ChangeLog-{$release->major}.php#{$release->__toString()}",
        $release->changelog_url
    );
});

it('sorts correctly', function () {
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

    // the first should be 8.0
    expect($response[0]['minor'])->toEqual(4);
    // the final should be 8.4
    expect($response[4]['minor'])->toEqual(0);
});

it('validates the support type', function () {
    $this->getJson('api/releases/minimum-supported/' . 'foo')
        ->assertJsonValidationErrors('supportType');
});

it('validates the version param', function () {
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
});
