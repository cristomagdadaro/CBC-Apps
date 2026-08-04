<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\RbacService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOptionsManageAccess
{
    public function __construct(private readonly RbacService $rbacService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($this->canManageOptions($user)) {
            return $next($request);
        }

        abort(Response::HTTP_FORBIDDEN, 'You do not have permission to manage system options.');
    }

    private function canManageOptions(?User $user): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        if ($user->is_admin || $user->hasRole('admin')) {
            return true;
        }

        return $this->rbacService->hasPermission($user, 'users.manage')
            || $this->rbacService->hasPermission($user, 'event.forms.manage');
    }
}
