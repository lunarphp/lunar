<?php

namespace Lunar\Api\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Every API response is JSON, whatever the client's Accept header says, so
 * framework-rendered errors (auth, throttling) never come back as HTML.
 */
class EnforceJson
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
