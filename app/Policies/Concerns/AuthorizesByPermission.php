<?php

namespace App\Policies\Concerns;

use App\Models\User;
use App\Services\RbacService;

trait AuthorizesByPermission
{
    protected function allowed(?User $user, string $permission): bool
    {
        return app(RbacService::class)->hasPermission($user, $permission);
    }
}
