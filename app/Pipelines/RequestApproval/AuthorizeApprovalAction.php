<?php

namespace App\Pipelines\RequestApproval;

use App\Services\RbacService;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

class AuthorizeApprovalAction
{
    public function __construct(private readonly RbacService $rbacService)
    {
    }

    /**
     * @throws AuthorizationException
     */
    public function handle(array $context, Closure $next): mixed
    {
        $user = Auth::user();

        if (!$this->rbacService->hasPermission($user, 'fes.request.approve')) {
            throw new AuthorizationException('You do not have permission to approve FES requests.');
        }

        return $next($context);
    }
}
