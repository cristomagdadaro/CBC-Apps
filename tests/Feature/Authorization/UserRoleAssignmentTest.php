<?php

namespace Tests\Feature\Authorization;

use App\Enums\Role as RoleEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_research_roles_are_created_and_assigned_during_user_update(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);

        Role::query()->whereIn('name', [
            RoleEnum::RESEARCHER->value,
            RoleEnum::RESEARCH_SUPERVISOR->value,
        ])->delete();

        $this->actingAs($admin, 'sanctum')
            ->putJson(route('api.users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'is_admin' => false,
                'roles' => [RoleEnum::RESEARCHER->value],
                'permissions' => [],
            ])
            ->assertOk();

        $this->assertDatabaseHas('roles', [
            'name' => RoleEnum::RESEARCHER->value,
            'label' => RoleEnum::RESEARCHER->label(),
        ]);

        $this->assertTrue(
            $user->fresh()->roles()->where('name', RoleEnum::RESEARCHER->value)->exists()
        );
    }
}
