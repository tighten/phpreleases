<?php

use App\Models\Hit;
use App\Notifications\WeeklyStats;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

uses(Tests\TestCase::class);
uses(DatabaseMigrations::class);

it('stores the hit', function () {
    $this->getJson('api/releases/8.0.0', [
        'User-Agent' => 'My Test Agent',
        'Referer' => 'http://example.com',
        'REMOTE_ADDR' => '127.0.0.1',
    ]);

    $this->assertDatabaseHas('hits', [
        'endpoint' => '/api/releases/8.0.0',
        'user_agent' => 'My Test Agent',
        'referer' => 'http://example.com',
        'ip' => '127.0.0.1',
    ]);
});

it('ignores bot user agents', function () {
    $this->getJson('/', [
        'User-Agent' => 'Mozilla/5.0 (compatible; DuckDuckGo-Favicons-Bot/1.0; +http://duckduckgo.com)',
    ]);

    $this->assertDatabaseMissing('hits', [
        'endpoint' => '/',
        'user_agent' => 'Mozilla/5.0 (compatible; DuckDuckGo-Favicons-Bot/1.0; +http://duckduckgo.com)',
    ]);
});

it('filters out web requests', function () {
    Hit::factory()
        ->count(2)
        ->create([
            'endpoint' => '/',
        ]);

    Hit::factory()
        ->count(2)
        ->create([
            'endpoint' => '/api/releases',
        ]);

    $hits = Hit::forTimePeriod('week');

    $this->assertSame([
        'current' => 2,
        'previous' => 0,
        'changePercent' => 100,
    ], $hits);
});

it('calculates percent increase', function () {
    Hit::factory()
        ->count(2)
        ->create([
            'created_at' => CarbonImmutable::now()->subWeek()->subDay(),
        ]);

    Hit::factory()
        ->count(6)
        ->create([
            'created_at' => CarbonImmutable::now(),
        ]);

    $hits = Hit::forTimePeriod('week');

    $this->assertSame([
        'current' => 6,
        'previous' => 2,
        'changePercent' => 200,
    ], $hits);
});

it('handles a percent decrease', function () {
    Hit::factory()
        ->count(6)
        ->create([
            'created_at' => CarbonImmutable::now()->subMonth()->subDay(),
        ]);

    Hit::factory()
        ->count(2)
        ->create([
            'created_at' => CarbonImmutable::now(),
        ]);

    $hits = Hit::forTimePeriod('month');

    $this->assertSame([
        'current' => 2,
        'previous' => 6,
        'changePercent' => -66,
    ], $hits);
});

it('handles first period values', function () {
    Hit::factory()
        ->count(2)
        ->create([
            'created_at' => CarbonImmutable::now(),
        ]);

    $hits = Hit::forTimePeriod('year');

    $this->assertSame([
        'current' => 2,
        'previous' => 0,
        'changePercent' => 100,
    ], $hits);
});

test('the slack notification is sent', function () {
    Notification::fake();

    $this->artisan('stats:send')
        ->assertExitCode(0);

    Notification::assertSentTo(
        new AnonymousNotifiable,
        WeeklyStats::class,
        function ($notification, $channels, $notifiable) {
            return $notifiable->routes['slack'] == config('services.slack.webhook');
        }
    );
});

test('hit counts match week to week', function () {
    $start = CarbonImmutable::now();

    // create one or more hits per day for past 2 weeks
    while ($start >= CarbonImmutable::now()->subWeeks(2)) {
        Hit::factory()
            ->create([
                'created_at' => $start,
            ]);
        $start = $start->subHours(mt_rand(1, 8));
    }

    // get current week hits
    $currentPeriod = Hit::forTimePeriod();

    // set "now" to one week ago, get previous week hits
    Carbon::setTestNow(CarbonImmutable::now()->subWeek());
    $previousPeriod = Hit::forTimePeriod();

    $this->assertSame(
        $currentPeriod['previous'],
        $previousPeriod['current'],
    );
});

it('handles a passed date', function () {
    $now = CarbonImmutable::now();

    // create a hit 7 days + 6 hrs earlier
    Hit::factory()->create([
        'created_at' => $now->subWeek()->subHours(6),
    ]);

    // default "current" should have no hits
    $default = Hit::forTimePeriod();
    expect($default['current'])->toBe(0);

    // create custom time period starting 7 hours ago
    // custom "current" should have one hit
    $custom = Hit::forTimePeriod('week', $now->subHours(7));
    expect($custom['current'])->toBe(1);
});
