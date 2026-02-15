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
        $allowedPermissions = config('rbac.permissions', []);
        $roles = $user->roles()->pluck('name')->all();

        $permissions = [];
        foreach ($roles as $role) {
            $permissions = array_merge($permissions, $permissionsByRole[$role] ?? []);
        }

        $directPermissions = collect($user->permissions ?? [])
            ->filter(static fn ($permission) => is_string($permission) && in_array($permission, $allowedPermissions, true))
            ->values()
            ->all();

        $permissions = array_merge($permissions, $directPermissions);

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
