<?php

namespace Tests\Unit\Services\Laboratory;

use App\Enums\Inventory as InventoryEnum;
use App\Models\Item;
use App\Models\LaboratoryEquipmentLog;
use App\Models\Personnel;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Laboratory\LaboratoryLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LaboratoryLogServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_equipment_id_from_barcode_returns_item_id(): void
    {
        ['item' => $item] = $this->createLaboratoryInventoryContext();

        Transaction::query()->create([
            'item_id' => $item->id,
            'barcode' => 'CBC-LAB-0001',
            'transac_type' => InventoryEnum::INCOMING->value,
            'quantity' => 1,
            'unit_price' => 100,
            'unit' => 'pc',
            'total_cost' => 100,
            'personnel_id' => Personnel::query()->first()->id,
            'user_id' => User::query()->first()->id,
            'expiration' => now()->addMonth(),
            'remarks' => 'Laboratory stock',
        ]);

        $resolved = app(LaboratoryLogService::class)->resolveEquipmentId('CBC-LAB-0001');

        $this->assertSame($item->id, $resolved);
    }

    public function test_mark_overdue_updates_only_expired_active_logs(): void
    {
        ['item' => $item, 'personnel' => $personnel, 'user' => $user] = $this->createLaboratoryInventoryContext();

        $overdue = LaboratoryEquipmentLog::query()->create([
            'equipment_id' => $item->id,
            'personnel_id' => $personnel->id,
            'status' => 'active',
            'started_at' => now()->subDay(),
            'end_use_at' => now()->subHour(),
            'active_lock' => true,
            'checked_in_by' => $user->id,
        ]);

        $active = LaboratoryEquipmentLog::query()->create([
            'equipment_id' => $item->id,
            'personnel_id' => $personnel->id,
            'status' => 'active',
            'started_at' => now()->subHour(),
            'end_use_at' => now()->addHour(),
            'active_lock' => true,
            'checked_in_by' => $user->id,
        ]);

        $count = app(LaboratoryLogService::class)->markOverdue();

        $this->assertSame(1, $count);
        $this->assertSame('overdue', $overdue->fresh()->status);
        $this->assertSame('active', $active->fresh()->status);
    }

    private function createLaboratoryInventoryContext(): array
    {
        DB::table('categories')->insert([
            'id' => 7,
            'name' => 'Laboratory Equipment',
            'description' => 'Laboratory items',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $supplier = Supplier::factory()->create();
        $item = Item::factory()->create([
            'category_id' => 7,
            'supplier_id' => $supplier->id,
        ]);
        $personnel = Personnel::factory()->create(['employee_id' => 'EMP-LAB-SVC']);
        $user = User::factory()->create();

        return compact('item', 'personnel', 'user');
    }
}
