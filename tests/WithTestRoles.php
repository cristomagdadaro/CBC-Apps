<?php

namespace Tests;

use App\Enums\Role as RoleEnum;
use App\Models\Role;
use App\Models\User;

trait WithTestRoles
{
    /**
     * Create a user with specific permission.
     * Admin gets all permissions by default.
     */
    protected function createUserWithPermission(string $permission): User
    {
        $user = User::factory()->create(['is_admin' => true]);
        return $user;
    }

    /**
     * Create a user with specific role.
     */
    protected function createUserWithRole(string $roleName): User
    {
        $role = Role::query()->firstOrCreate(
            ['name' => $roleName],
            [
                'label' => str($roleName)->replace('_', ' ')->title()->toString(),
                'description' => 'Test role',
            ]
        );

        $user = User::factory()->create(['is_admin' => false]);
        $user->roles()->attach($role->id);

        return $user;
    }

    /**
     * Create an admin user.
     */
    protected function createAdminUser(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }
}
