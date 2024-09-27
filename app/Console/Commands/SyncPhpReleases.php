<?php

namespace App\Console\Commands;

use App\Actions\FetchReleasesFromGitHub;
use App\Models\Release;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncPhpReleases extends Command
{
    protected $signature = 'sync:php-releases';

    protected $description = 'Pull PHP releases from GitHub into our application.';

    public function handle(): int
    {
        Log::info('Syncing PHP Releases');

        $releases = $this->filterToUsefulReleases($this->fetchReleasesFromGitHub());

        // Map into arrays containing major, minor, and release numbers
        $releases = $releases->map(function ($item) {
            // The release will have a tagger if it's verified,
            // or an author if it's a commit
            $tagDate = Arr::get(
                $item,
                'target.tagger.date',
                Arr::get($item, 'target.author.date')
            );
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

        $releases->each(function ($item) use ($releases) {
            // fetch the initial release of the minor version so we access the support dates
            $initialRelease = $releases
                ->where('major', $item['major'])
                ->where('minor', $item['minor'])
                ->where('release', 0)
                ->firstOrFail();

            $release = Release::firstOrCreate(
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

            if ($release->wasRecentlyCreated) {
                $this->info('Created PHP release ' . $release);
            }

            return $release;
        });

        $this->info('Finished saving PHP releases.');

        return self::SUCCESS;
    }

    private function fetchReleasesFromGitHub()
    {
        return (new FetchReleasesFromGitHub)();
    }

    private function filterToUsefulReleases(Collection $releases): Collection
    {
        return $releases->reject(function ($item) {
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
