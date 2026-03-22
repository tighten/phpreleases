<?php

namespace App\Console\Commands;

use App\Actions\FetchEolDatesFromEndOfLifeDate;
use App\Actions\FetchReleasesFromGitHub;
use App\Models\Release;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

#[Signature('sync:php-releases')]
#[Description('Pull PHP releases from GitHub into our application.')]
class SyncPhpReleases extends Command
{
    public function handle(): int
    {
        Log::info('Syncing PHP Releases');

        $eolDates = $this->fetchEolDates();
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
            ];
        });

        $releases->each(function ($item) use ($eolDates) {
            $cycle = "{$item['major']}.{$item['minor']}";
            $eol = $eolDates->get($cycle);

            if (! $eol) {
                $this->warn("No EOL data found for PHP {$cycle}, skipping {$cycle}.{$item['release']}");

                return;
            }

            $release = Release::updateOrCreate(
                [
                    'major' => $item['major'],
                    'minor' => $item['minor'],
                    'release' => $item['release'],
                ],
                [
                    'tagged_at' => $item['tagged_at'],
                    'active_support_until' => Carbon::parse($eol['support']),
                    'security_support_until' => Carbon::parse($eol['eol']),
                ]
            );

            if ($release->wasRecentlyCreated) {
                $this->info('Created PHP release ' . $release);
            } elseif ($release->wasChanged()) {
                $this->info('Updated PHP release ' . $release);
            }

            return $release;
        });

        $this->info('Finished saving PHP releases.');

        return self::SUCCESS;
    }

    private function fetchEolDates()
    {
        return (new FetchEolDatesFromEndOfLifeDate)();
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
