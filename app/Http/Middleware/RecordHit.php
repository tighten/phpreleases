<?php

namespace App\Http\Middleware;

use App\Models\Hit;
use Closure;
use Illuminate\Http\Request;

class RecordHit
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request): void
    {
        $uri = $request->getRequestUri();
        $userAgent = $request->header('user-agent');

        if (
            ($uri === '/'
            || explode('/', $uri)[1] === 'api')
            && ! str_contains(strtolower($userAgent), 'bot')
            && ! str_contains(strtolower($userAgent), 'spider')
        ) {
            Hit::create([
                'endpoint' => $uri,
                'user_agent' => $userAgent,
                'referer' => $request->header('referer'),
                'ip' => $request->ip(),
            ]);
        }
    }
}
