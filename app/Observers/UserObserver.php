<?php

namespace App\Observers;

use App\Enums\Role as RoleEnum;
use App\Models\Role;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        $this->syncAdminRole($user);
    }

    public function updated(User $user): void
    {
        $this->syncAdminRole($user);
    }

    protected function syncAdminRole(User $user): void
    {
        if (!$user->is_admin) {
            return;
        }

        $adminRole = Role::query()->firstOrCreate(
            ['name' => RoleEnum::ADMIN->value],
            [
                'label' => 'Admin',
                'description' => 'Full system access',
            ]
        );

        if (!$user->roles()->where('roles.id', $adminRole->id)->exists()) {
            $user->roles()->attach($adminRole->id);
        }
    }
}
