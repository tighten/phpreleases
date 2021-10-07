<?php

namespace App\Console\Commands;

use App\Actions\FetchVersionsFromGitHub;
use App\Models\Version;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncPhpVersions extends Command
{
    protected $signature = 'sync:php-versions';

    protected $description = 'Pull PHP versions from GitHub into our application.';

    public function handle()
    {
        Log::info('Syncing PHP Versions');

        $versions = $this->filterToUsefulVersions($this->fetchVersionsFromGitHub());

        // Map into arrays containing major, minor, and release numbers
        $versions = $versions->map(function ($item) {
            $tagDate = $item['target']['tagger']['date'];
            $pieces = explode('.', ltrim($item['name'], 'php-'));

            return [
                'major' => $pieces[0],
                'minor' => $pieces[1],
                'release' => $pieces[2],
                'tagged_at' => Carbon::parse($tagDate),
                'released_at' => Carbon::parse($tagDate)->addDays(2),
                'active_support_until' => Carbon::parse($tagDate)->addDays(2)->addYears(2),
                'security_support_until' => Carbon::parse($tagDate)->addDays(2)->addYears(3),
            ];
        });

        $versions->each(function ($item) use ($versions) {
            // fetch the initial release of the minor version so we access the support dates
            $initialRelease = $versions
                ->where('major', $item['major'])
                ->where('minor', $item['minor'])
                ->where('release', 0)
                ->firstOrFail();

            $version = Version::firstOrCreate(
                [
                    'major' => $item['major'],
                    'minor' => $item['minor'],
                    'release' => $item['release'],
                ],
                [
                    'tagged_at' => $item['tagged_at'],
                    'active_support_until' => $initialRelease['active_support_until'],
                    'security_support_until' => $initialRelease['security_support_until'],
                ]
            );

            if ($version->wasRecentlyCreated) {
                $this->info('Created PHP version ' . $version);
            }

            return $version;
        });

        $this->info('Finished saving PHP versions.');
    }

    private function fetchVersionsFromGitHub()
    {
        return (new FetchVersionsFromGitHub())();
    }

    private function filterToUsefulVersions(Collection $versions): Collection
    {
        return $versions->reject(function ($item) {
            // reject alphas, betas, RCs and some other non-conventional tags
            return Str::contains($item['name'], ['RC', 'beta', 'alpha', 'rc', 'php_ibase_before_split', 'php4', 'php5_5_0']);
        })
            ->filter(function ($item) {
                // include only tags with `php`
                return Str::contains($item['name'], 'php');
            })
            ->filter(function ($item) {
                // include only version 5.6 and after
                return (bool) preg_match('/5\.6\.[0-9]|([6-9]\.[0-9]\.[0-9])|(1[0-9]\.[0-9]\.[0-9])/', $item['name']);
            });
    }
}
