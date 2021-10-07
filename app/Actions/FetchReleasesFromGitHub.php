<?php

namespace App\Actions;

use Illuminate\Support\Facades\Http;

class FetchReleasesFromGitHub
{
    private array $filters = [
        'first' => '100',
        'refPrefix' => '"refs/tags/"',
        'orderBy' => '{field: TAG_COMMIT_DATE, direction: DESC}',
    ];

    public function __invoke()
    {
        return cache()->remember('github::php-releases', HOUR_IN_SECONDS, function () {
            $tags = collect();

            do {
                $responseJson = $this->getResponseJson();

                $tags->push(collect(data_get($responseJson, 'data.repository.refs.nodes')));

                $nextPage = data_get($responseJson, 'data.repository.refs.pageInfo')['endCursor'];

                if ($nextPage) {
                    $this->filters['after'] = '"' . $nextPage . '"';
                }
            } while ($nextPage);

            return $tags->flatten(1);
        });
    }

    private function getResponseJson()
    {
        // Format the filters at runtime to include pagination
        $filters = collect($this->filters)
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

        return $responseJson;
    }
}
