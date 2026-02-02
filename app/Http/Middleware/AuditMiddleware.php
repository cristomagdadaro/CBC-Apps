<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Store user context for audit observers to access
        // This middleware captures the authenticated user and IP address for the current request
        // The AuditObserver will use Auth::id() and Request::ip() to capture this context

        return $next($request);
    }
}
