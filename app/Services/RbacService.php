<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\User;

class RbacService
{
    public function permissionsFor(?User $user): array
    {
        if (!$user) {
            return [];
        }

        if ($user->is_admin || $user->hasRole(Role::ADMIN->value)) {
            return ['*'];
        }

        $permissionsByRole = config('rbac.role_permissions', []);
        $roles = $user->roles()->pluck('name')->all();

        $permissions = [];
        foreach ($roles as $role) {
            $permissions = array_merge($permissions, $permissionsByRole[$role] ?? []);
        }

        return array_values(array_unique($permissions));
    }

    public function hasPermission(?User $user, string $permission): bool
    {
        if (!$user) {
            return false;
        }

        $permissions = $this->permissionsFor($user);

        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }
}
