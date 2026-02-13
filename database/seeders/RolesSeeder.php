<?php

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => RoleEnum::ADMIN->value,
                'label' => 'Admin',
                'description' => 'Full System Access',
            ],
            [
                'name' => RoleEnum::LABORATORY_MANAGER->value,
                'label' => 'Laboratory Manager',
                'description' => 'FES approval, laboratory logger, inventory, and equipment reports',
            ],
            [
                'name' => RoleEnum::ICT_MANAGER->value,
                'label' => 'ICT Manager',
                'description' => 'Inventory, equipment reports, and event forms',
            ],
            [
                'name' => RoleEnum::ADMINISTRATIVE_ASSISTANT->value,
                'label' => 'Administrative Assistant',
                'description' => 'Vehicle, venue, and hostel rentals',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::query()->updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }

        $adminRole = Role::query()->where('name', RoleEnum::ADMIN->value)->first();

        if (!$adminRole) {
            return;
        }

        User::query()
            ->where('is_admin', true)
            ->get()
            ->each(fn (User $user) => $user->roles()->syncWithoutDetaching([$adminRole->id]));
    }
}
