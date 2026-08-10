<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SproutAiTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = env('SPROUTAI_INTERNAL_SYNC_TOKEN');

        if (empty($expected)) {
            return response()->json(['error' => 'Sync token not configured on this server.'], 503);
        }

        $provided = $request->bearerToken();

        if (! hash_equals($expected, (string) $provided)) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
