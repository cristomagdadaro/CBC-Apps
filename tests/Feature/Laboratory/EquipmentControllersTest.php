<?php

namespace Tests\Feature\Laboratory;

use App\Enums\Role as RoleEnum;
use App\Models\Item;
use App\Models\LaboratoryEquipmentLog;
use App\Models\User;
use App\Repositories\LaboratoryEquipmentLogRepo;
use App\Services\Laboratory\LaboratoryLogService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\WithTestRoles;

class EquipmentControllersTest extends TestCase
{
    use RefreshDatabase;
    use WithTestRoles;

    public function test_guest_can_list_ict_equipment_from_public_endpoint(): void
    {
        $service = $this->createMock(LaboratoryLogService::class);
        $service->expects($this->once())
            ->method('listEligibleEquipment')
            ->with('spectrometer', 'ict')
            ->willReturn(new EloquentCollection([
                tap(new Item(), function (Item $item): void {
                    $item->forceFill(['id' => 'ICT-1', 'name' => 'Spectrometer']);
                }),
            ]));

        $this->app->instance(LaboratoryLogService::class, $service);

        $this->getJson(route('api.ict.equipments.index', ['search' => 'spectrometer']))
            ->assertOk()
            ->assertJsonPath('data.0.id', 'ICT-1')
            ->assertJsonPath('data.0.name', 'Spectrometer');
    }

    public function test_guest_ict_show_returns_not_found_when_identifier_cannot_be_resolved(): void
    {
        $service = $this->createMock(LaboratoryLogService::class);
        $service->expects($this->once())
            ->method('resolveEquipmentId')
            ->with('missing-equipment')
            ->willReturn(null);

        $this->app->instance(LaboratoryLogService::class, $service);

        $this->getJson(route('api.ict.equipments.show', ['identifier' => 'missing-equipment']))
            ->assertNotFound()
            ->assertJson([
                'message' => 'Equipment not found.',
            ]);
    }

    public function test_guest_cannot_check_in_ict_equipment_without_authentication(): void
    {
        $this->postJson(route('api.ict.equipments.check-in', ['identifier' => 'ICT-BC-001']), [
            'end_use_at' => now()->addHour()->toIso8601String(),
            'purpose' => 'Diagnostics',
        ])->assertUnauthorized();
    }

    public function test_authenticated_user_can_check_in_ict_equipment_without_posting_employee_id(): void
    {
        $service = $this->createMock(LaboratoryLogService::class);
        $service->expects($this->once())
            ->method('resolveEquipmentId')
            ->with('ICT-BC-001')
            ->willReturn('equipment-1');
        $service->expects($this->once())
            ->method('checkIn')
            ->with(
                'equipment-1',
                $this->callback(function (array $payload): bool {
                    return !array_key_exists('employee_id', $payload)
                        && ($payload['purpose'] ?? null) === 'Diagnostics'
                        && !empty($payload['end_use_at']);
                }),
                'ict'
            )
            ->willReturn(tap(new LaboratoryEquipmentLog(), function (LaboratoryEquipmentLog $log): void {
                $log->forceFill(['id' => 'log-1', 'status' => 'active']);
            }));

        $this->app->instance(LaboratoryLogService::class, $service);

        Sanctum::actingAs(User::factory()->create([
            'employee_id' => 'EMP-ICT-001',
        ]));

        $this->postJson(route('api.ict.equipments.check-in', ['identifier' => 'ICT-BC-001']), [
            'end_use_at' => now()->addHour()->toIso8601String(),
            'purpose' => 'Diagnostics',
        ])
            ->assertCreated()
            ->assertJsonPath('message', 'Equipment checked in successfully.')
            ->assertJsonPath('data.id', 'log-1')
            ->assertJsonPath('data.status', 'active');
    }

    public function test_guest_cannot_view_active_laboratory_equipments_without_authentication(): void
    {
        $this->getJson(route('api.laboratory.equipments.active', ['employee_id' => 'EMP-LAB-100']))
            ->assertUnauthorized();
    }

    public function test_authenticated_non_admin_user_views_only_linked_active_laboratory_equipments(): void
    {
        $service = $this->createMock(LaboratoryLogService::class);
        $service->expects($this->once())
            ->method('getActiveEquipment')
            ->with('EMP-LAB-100')
            ->willReturn(new EloquentCollection([
                tap(new LaboratoryEquipmentLog(), function (LaboratoryEquipmentLog $log): void {
                    $log->forceFill(['id' => 'LAB-1', 'status' => 'active']);
                }),
            ]));

        $repo = $this->createMock(LaboratoryEquipmentLogRepo::class);

        $this->app->instance(LaboratoryLogService::class, $service);
        $this->app->instance(LaboratoryEquipmentLogRepo::class, $repo);

        Sanctum::actingAs(User::factory()->create([
            'employee_id' => 'EMP-LAB-100',
            'is_admin' => false,
        ]));

        $this->getJson(route('api.laboratory.equipments.active', ['employee_id' => 'EMP-LAB-999']))
            ->assertOk()
            ->assertJsonPath('data.0.id', 'LAB-1')
            ->assertJsonPath('data.0.status', 'active');
    }

    public function test_laboratory_manager_can_view_laboratory_dashboard_metrics(): void
    {
        $service = $this->createMock(LaboratoryLogService::class);
        $service->expects($this->once())
            ->method('getDashboardMetrics')
            ->willReturn([
                'active' => 3,
                'overdue' => 1,
            ]);

        $repo = $this->createMock(LaboratoryEquipmentLogRepo::class);

        $this->app->instance(LaboratoryLogService::class, $service);
        $this->app->instance(LaboratoryEquipmentLogRepo::class, $repo);

        $user = $this->createUserWithRole(RoleEnum::LABORATORY_MANAGER->value);
        Sanctum::actingAs($user);

        $this->getJson(route('api.laboratory.dashboard'))
            ->assertOk()
            ->assertJson([
                'data' => [
                    'active' => 3,
                    'overdue' => 1,
                ],
            ]);
    }

    public function test_users_without_laboratory_permission_cannot_view_dashboard(): void
    {
        Sanctum::actingAs($this->createUserWithRole(RoleEnum::ICT_MANAGER->value));

        $this->getJson(route('api.laboratory.dashboard'))
            ->assertForbidden();
    }

    public function test_laboratory_manager_can_view_logs_collection(): void
    {
        $service = $this->createMock(LaboratoryLogService::class);
        $service->expects($this->once())
            ->method('enrichLogsWithLocationDetails')
            ->with($this->isInstanceOf(Collection::class));

        $repo = $this->createMock(LaboratoryEquipmentLogRepo::class);
        $repo->expects($this->once())
            ->method('search')
            ->willReturn([
                'data' => new EloquentCollection([
                    tap(new LaboratoryEquipmentLog(), function (LaboratoryEquipmentLog $log): void {
                        $log->forceFill(['id' => 'log-1', 'status' => 'active']);
                    }),
                ]),
                'meta' => ['total' => 1],
            ]);

        $this->app->instance(LaboratoryLogService::class, $service);
        $this->app->instance(LaboratoryEquipmentLogRepo::class, $repo);

        $user = $this->createUserWithRole(RoleEnum::LABORATORY_MANAGER->value);
        Sanctum::actingAs($user);

        $this->getJson(route('api.laboratory.logs.index', ['search' => 'scope']))
            ->assertOk()
            ->assertJsonPath('data.0.id', 'log-1')
            ->assertJsonPath('data.0.status', 'active')
            ->assertJsonPath('meta.total', 1);
    }
}
