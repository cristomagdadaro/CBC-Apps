<?php

namespace App\Http\Middleware;

use App\Services\DeploymentAccessService;
use Closure;
use Illuminate\Http\Request;

class EnsureDeploymentAccess
{
    public function __construct(private readonly DeploymentAccessService $deploymentAccess)
    {
    }

    public function handle(Request $request, Closure $next, string $moduleKey)
    {
        $evaluation = $this->deploymentAccess->evaluate($request, $moduleKey);

        if ($evaluation['allowed'] === true) {
            return $next($request);
        }

        $message = $evaluation['message'] ?? 'This module is not available.';

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $message,
                'channel' => $evaluation['channel'] ?? $this->deploymentAccess->currentChannel($request),
                'required_access' => $evaluation['access'] ?? $this->deploymentAccess->accessFor($moduleKey),
                'mode' => $evaluation['mode'] ?? $this->deploymentAccess->modeFor($moduleKey),
                'reason' => $evaluation['reason'] ?? 'deployment_access',
                'module_key' => $moduleKey,
            ], $evaluation['status'] ?? 403);
        }

        abort($evaluation['status'] ?? 403, $message);
    }
}