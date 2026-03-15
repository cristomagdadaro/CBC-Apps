<?php

namespace Tests\Feature\Authorization;

use App\Enums\Role as RoleEnum;
use App\Models\Category;
use App\Models\Form;
use App\Models\Item;
use App\Models\LaboratoryEquipmentLog;
use App\Models\Personnel;
use App\Models\RentalVehicle;
use App\Models\RentalVenue;
use App\Models\RequestFormPivot;
use App\Models\Supplier;
use App\Models\SuppEquipReport;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\WithTestRoles;

class PolicyCoverageTest extends TestCase
{
    use RefreshDatabase;
    use WithTestRoles;

    public function test_form_policy_uses_event_forms_permission(): void
    {
        $manager = $this->createUserWithRole(RoleEnum::ICT_MANAGER->value);
        $user = User::factory()->create(['is_admin' => false]);
        $form = Form::factory()->create();

        $this->assertTrue($manager->can('viewAny', Form::class));
        $this->assertTrue($manager->can('create', Form::class));
        $this->assertTrue($manager->can('update', $form));
        $this->assertTrue($manager->can('delete', $form));

        $this->assertFalse($user->can('viewAny', Form::class));
        $this->assertFalse($user->can('create', Form::class));
        $this->assertFalse($user->can('update', $form));
        $this->assertFalse($user->can('delete', $form));
    }

    public function test_request_form_pivot_policy_uses_fes_request_approval_permission(): void
    {
        $manager = $this->createUserWithRole(RoleEnum::LABORATORY_MANAGER->value);
        $user = User::factory()->create(['is_admin' => false]);
        $pivot = RequestFormPivot::factory()->create();

        $this->assertTrue($manager->can('viewAny', RequestFormPivot::class));
        $this->assertTrue($manager->can('update', $pivot));

        $this->assertFalse($user->can('viewAny', RequestFormPivot::class));
        $this->assertFalse($user->can('update', $pivot));
    }

    public function test_transaction_policy_uses_inventory_manage_permission(): void
    {
        $manager = $this->createUserWithRole(RoleEnum::ICT_MANAGER->value);
        $user = User::factory()->create(['is_admin' => false]);
        $transaction = $this->createTransaction();

        $this->assertTrue($manager->can('viewAny', Transaction::class));
        $this->assertTrue($manager->can('create', Transaction::class));
        $this->assertTrue($manager->can('update', $transaction));
        $this->assertTrue($manager->can('delete', $transaction));

        $this->assertFalse($user->can('viewAny', Transaction::class));
        $this->assertFalse($user->can('create', Transaction::class));
        $this->assertFalse($user->can('update', $transaction));
        $this->assertFalse($user->can('delete', $transaction));
    }

    public function test_supp_equip_report_policy_uses_equipment_report_permission(): void
    {
        $manager = $this->createUserWithRole(RoleEnum::ICT_MANAGER->value);
        $user = User::factory()->create(['is_admin' => false]);
        $transaction = $this->createTransaction();
        $report = SuppEquipReport::factory()->create([
            'transaction_id' => $transaction->id,
            'item_id' => $transaction->item_id,
            'user_id' => $transaction->user_id,
        ]);

        $this->assertTrue($manager->can('viewAny', SuppEquipReport::class));
        $this->assertTrue($manager->can('create', SuppEquipReport::class));
        $this->assertTrue($manager->can('update', $report));
        $this->assertTrue($manager->can('delete', $report));

        $this->assertFalse($user->can('viewAny', SuppEquipReport::class));
        $this->assertFalse($user->can('create', SuppEquipReport::class));
        $this->assertFalse($user->can('update', $report));
        $this->assertFalse($user->can('delete', $report));
    }

    public function test_laboratory_equipment_log_policy_uses_laboratory_logger_permission(): void
    {
        $manager = $this->createUserWithRole(RoleEnum::LABORATORY_MANAGER->value);
        $user = User::factory()->create(['is_admin' => false]);
        $log = $this->createLaboratoryLog();

        $this->assertTrue($manager->can('viewAny', LaboratoryEquipmentLog::class));
        $this->assertTrue($manager->can('create', LaboratoryEquipmentLog::class));
        $this->assertTrue($manager->can('update', $log));

        $this->assertFalse($user->can('viewAny', LaboratoryEquipmentLog::class));
        $this->assertFalse($user->can('create', LaboratoryEquipmentLog::class));
        $this->assertFalse($user->can('update', $log));
    }

    public function test_rental_vehicle_policy_uses_vehicle_manage_permission(): void
    {
        $assistant = $this->createUserWithRole(RoleEnum::ADMINISTRATIVE_ASSISTANT->value);
        $user = User::factory()->create(['is_admin' => false]);
        Personnel::factory()->create(['employee_id' => 'EMP-RV-POL']);
        $vehicle = RentalVehicle::factory()->create();

        $this->assertTrue($assistant->can('viewAny', RentalVehicle::class));
        $this->assertTrue($assistant->can('create', RentalVehicle::class));
        $this->assertTrue($assistant->can('update', $vehicle));
        $this->assertTrue($assistant->can('delete', $vehicle));

        $this->assertFalse($user->can('viewAny', RentalVehicle::class));
        $this->assertFalse($user->can('create', RentalVehicle::class));
        $this->assertFalse($user->can('update', $vehicle));
        $this->assertFalse($user->can('delete', $vehicle));
    }

    public function test_rental_venue_policy_uses_venue_manage_permission(): void
    {
        $assistant = $this->createUserWithRole(RoleEnum::ADMINISTRATIVE_ASSISTANT->value);
        $user = User::factory()->create(['is_admin' => false]);
        Personnel::factory()->create(['employee_id' => 'EMP-RVEN-POL']);
        $venue = RentalVenue::factory()->create();

        $this->assertTrue($assistant->can('viewAny', RentalVenue::class));
        $this->assertTrue($assistant->can('create', RentalVenue::class));
        $this->assertTrue($assistant->can('update', $venue));
        $this->assertTrue($assistant->can('delete', $venue));

        $this->assertFalse($user->can('viewAny', RentalVenue::class));
        $this->assertFalse($user->can('create', RentalVenue::class));
        $this->assertFalse($user->can('update', $venue));
        $this->assertFalse($user->can('delete', $venue));
    }

    private function createTransaction(): Transaction
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        $item = Item::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
        $personnel = Personnel::factory()->create(['employee_id' => 'EMP-POLICY']);
        $user = User::factory()->create();

        return Transaction::factory()->create([
            'item_id' => $item->id,
            'personnel_id' => $personnel->id,
            'user_id' => $user->id,
            'transac_type' => 'incoming',
            'quantity' => 10,
            'barcode' => 'CBC-01-000001',
            'unit' => 'pc',
        ]);
    }

    private function createLaboratoryLog(): LaboratoryEquipmentLog
    {
        $category = Category::factory()->create(['name' => 'Laboratory Equipment']);
        $supplier = Supplier::factory()->create();
        $item = Item::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
        $personnel = Personnel::factory()->create(['employee_id' => 'EMP-LAB-POL']);
        $user = User::factory()->create();

        return LaboratoryEquipmentLog::query()->create([
            'equipment_id' => $item->id,
            'personnel_id' => $personnel->id,
            'status' => 'active',
            'started_at' => now()->subHour(),
            'end_use_at' => now()->addHour(),
            'active_lock' => true,
            'checked_in_by' => $user->id,
        ]);
    }
}
