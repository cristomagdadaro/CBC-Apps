<?php

namespace App\Pipelines\RequestApproval;

use App\Services\LabRequest\RequestLifecycleService;
use Closure;

class PrepareApprovalPayload
{
    public function __construct(private readonly RequestLifecycleService $lifecycleService)
    {
    }

    public function handle(array $context, Closure $next): mixed
    {
        $transition = $this->lifecycleService->prepareTransition(
            $context['model'],
            $context['validated'],
            auth()->user()?->name,
        );

        $context['validated'] = $transition['payload'];
        $context['previous_status'] = $transition['previous_status'];
        $context['requested_status'] = $transition['requested_status'];

        return $next($context);
    }
}
