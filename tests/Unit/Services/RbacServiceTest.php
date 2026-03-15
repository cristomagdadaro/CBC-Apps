<?php

namespace Tests\Unit\Services;

use App\Enums\Role as RoleEnum;
use App\Models\Role;
use App\Models\User;
use App\Services\RbacService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_receives_wildcard_permission(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $permissions = app(RbacService::class)->permissionsFor($user);

        $this->assertContains('*', $permissions);
        $this->assertTrue($user->can('inventory.manage'));
    }

    public function test_ict_manager_permissions_are_applied_from_role_map(): void
    {
        $role = Role::query()->create([
            'name' => RoleEnum::ICT_MANAGER->value,
            'label' => 'ICT Manager',
            'description' => 'Inventory, reports, and event forms',
        ]);

        $user = User::factory()->create(['is_admin' => false]);
        $user->roles()->attach($role->id);

        $rbac = app(RbacService::class);

        $this->assertTrue($rbac->hasPermission($user, 'inventory.manage'));
        $this->assertTrue($rbac->hasPermission($user, 'event.forms.manage'));
        $this->assertFalse($rbac->hasPermission($user, 'fes.request.approve'));
    }

    public function test_direct_user_permissions_are_applied(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'permissions' => ['users.manage', 'rental.vehicle.manage'],
        ]);

        $rbac = app(RbacService::class);

        $this->assertTrue($rbac->hasPermission($user, 'users.manage'));
        $this->assertTrue($rbac->hasPermission($user, 'rental.vehicle.manage'));
        $this->assertFalse($rbac->hasPermission($user, 'event.forms.manage'));
    }

    public function test_administrative_assistant_has_certificate_permission_only(): void
    {
        $role = Role::query()->create([
            'name' => RoleEnum::ADMINISTRATIVE_ASSISTANT->value,
            'label' => 'Administrative Assistant',
            'description' => 'Certificate and rental services access',
        ]);

        $user = User::factory()->create(['is_admin' => false]);
        $user->roles()->attach($role->id);

        $rbac = app(RbacService::class);

        $this->assertTrue($rbac->hasPermission($user, 'event.certificates.manage'));
        $this->assertFalse($rbac->hasPermission($user, 'event.forms.manage'));
    }
}
