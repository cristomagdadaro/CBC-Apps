<?php

namespace App\Pipelines\RequestApproval;

use Closure;

class PrepareApprovalPayload
{
    public function handle(array $context, Closure $next): mixed
    {
        // Keep payload unchanged for compatibility; reserved for normalization rules.
        return $next($context);
    }
}
