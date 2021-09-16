<?php

namespace App\Console\Commands;

use App\Models\Version;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncPhpVersions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:php-versions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull PHP versions from GitHub into our application.';

    private array $defaultFilters = [
        'first' => '100',
        'refPrefix' => '"refs/tags/"',
        'orderBy' => '{field: TAG_COMMIT_DATE, direction: DESC}',
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info('Syncing PHP Versions');

        $versions = $this->fetchVersionsFromGitHub();
        // Map into arrays containing major, minor, and release numbers

        $versions  = $versions->reject(function ($item) {
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
            })
            ->map(function ($item) {
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
                    ->firstWhere('release', 0);

                $version = Version::firstWhere([
                    'major' => $item['major'],
                    'minor' => $item['minor'],
                    'release' => $item['release'],
                ]);

                if (! $version) {
                    // Create it if it doesn't exist
                    $created = Version::create([
                        'major' => $item['major'],
                        'minor' => $item['minor'],
                        'release' => $item['release'],
                        'tagged_at' => $item['tagged_at'],
                        'active_support_until' => $initialRelease['active_support_until'],
                        'security_support_until' => $initialRelease['security_support_until'],
                    ]);

                    $this->info('Created PHP version ' . $created);
                    return;
                }

                return $version;
            });

        $this->info('Finished saving PHP versions.');
    }

    private function fetchVersionsFromGitHub()
    {
        return cache()->remember('github::php-versions', HOUR_IN_SECONDS, function () {
            $tags = collect();

            do {
                // Format the filters at runtime to include pagination
                $filters = collect($this->defaultFilters)
                    ->map(function ($value, $key) {
                        return "{$key}: $value";
                    })
                    ->implode(', ');

                $query = <<<QUERY
                    {
                      repository(owner: "php", name: "php-src") {
                        refs({$filters}) {
                          nodes {
                            name
                            target {
                              oid
                              ... on Tag {
                                commitUrl
                                tagger {
                                  date
                                }
                              }
                            }
                          }
                          pageInfo {
                            endCursor
                            hasNextPage
                          }
                        }
                      }
                      rateLimit {
                        cost
                        remaining
                      }
                    }
                QUERY;

                $response = Http::withToken(config('services.github.token'))
                    ->post('https://api.github.com/graphql', ['query' => $query]);

                $responseJson = $response->json();

                if (! $response->ok()) {
                    abort($response->getStatusCode(), 'Error connecting to GitHub: ' . $responseJson['message']);
                }

                $tags->push(collect(data_get($responseJson, 'data.repository.refs.nodes')));

                $nextPage = data_get($responseJson, 'data.repository.refs.pageInfo')['endCursor'];

                if ($nextPage) {
                    $this->defaultFilters['after'] = '"' . $nextPage . '"';
                }
            } while ($nextPage);

            return $tags->flatten(1);
        });
    }
}
