<?php

namespace Tests\Feature\Authorization;

use App\Enums\Role as RoleEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiRoleAccessMatrixTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<int, array{route: string, permission: string}>
     */
    private function protectedApiRoutes(): array
    {
        return [
            ['route' => 'api.form.index', 'permission' => 'event.forms.manage'],
            ['route' => 'api.laboratory.dashboard', 'permission' => 'laboratory.logger.manage'],
            ['route' => 'api.inventory.transactions.index', 'permission' => 'inventory.manage'],
            ['route' => 'api.inventory.supp_equip_reports.index', 'permission' => 'equipment.report.manage'],
            ['route' => 'api.users.index', 'permission' => 'users.manage'],
            ['route' => 'api.rental.vehicles.index', 'permission' => 'rental.vehicle.manage'],
            ['route' => 'api.rental.venues.index', 'permission' => 'rental.venue.manage'],
            ['route' => 'api.requestFormPivot.index', 'permission' => 'fes.request.approve'],
        ];
    }

    public function test_protected_api_routes_require_authentication(): void
    {
        foreach ($this->protectedApiRoutes() as $target) {
            $this->getJson(route($target['route']))->assertStatus(401);
        }
    }

    public function test_admin_can_access_all_protected_api_routes(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        foreach ($this->protectedApiRoutes() as $target) {
            $this->getJson(route($target['route']))->assertStatus(200);
        }
    }

    public function test_laboratory_manager_access_matrix_for_protected_api_routes(): void
    {
        $user = $this->createUserWithRole(RoleEnum::LABORATORY_MANAGER->value);

        $this->assertAccess($user, [
            'api.form.index' => false,
            'api.laboratory.dashboard' => true,
            'api.inventory.transactions.index' => true,
            'api.inventory.supp_equip_reports.index' => true,
            'api.users.index' => false,
            'api.rental.vehicles.index' => false,
            'api.rental.venues.index' => false,
            // Currently admin-only in routes/api.php via role.any:admin
            'api.requestFormPivot.index' => false,
        ]);
    }

    public function test_ict_manager_access_matrix_for_protected_api_routes(): void
    {
        $user = $this->createUserWithRole(RoleEnum::ICT_MANAGER->value);

        $this->assertAccess($user, [
            'api.form.index' => true,
            'api.laboratory.dashboard' => false,
            'api.inventory.transactions.index' => true,
            'api.inventory.supp_equip_reports.index' => true,
            'api.users.index' => false,
            'api.rental.vehicles.index' => false,
            'api.rental.venues.index' => false,
            'api.requestFormPivot.index' => false,
        ]);
    }

    public function test_administrative_assistant_access_matrix_for_protected_api_routes(): void
    {
        $user = $this->createUserWithRole(RoleEnum::ADMINISTRATIVE_ASSISTANT->value);

        $this->assertAccess($user, [
            'api.form.index' => false,
            'api.laboratory.dashboard' => false,
            'api.inventory.transactions.index' => false,
            'api.inventory.supp_equip_reports.index' => false,
            'api.users.index' => false,
            'api.rental.vehicles.index' => true,
            'api.rental.venues.index' => true,
            'api.requestFormPivot.index' => false,
        ]);
    }

    /**
     * @param array<string, bool> $expectations
     */
    private function assertAccess(User $user, array $expectations): void
    {
        $this->actingAs($user);

        foreach ($expectations as $routeName => $allowed) {
            $response = $this->getJson(route($routeName));

            if ($allowed) {
                $response->assertStatus(200);
                continue;
            }

            $response->assertStatus(403);
        }
    }

    private function createUserWithRole(string $roleName): User
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
}
