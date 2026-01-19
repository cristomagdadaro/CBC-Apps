<?php

namespace Tests\Feature\Inventory;

use App\Enums\Inventory;
use App\Models\Category;
use App\Models\Item;
use App\Models\Personnel;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InventorySuppEquipReportsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_supp_equip_report_crud_flow(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        $item = Item::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
        $personnel = Personnel::factory()->create([
            'employee_id' => 'EMP-SUPP',
        ]);

        $transaction = Transaction::factory()->create([
            'item_id' => $item->id,
            'barcode' => 'CBC-01-000001',
            'transac_type' => Inventory::INCOMING->value,
            'quantity' => 5,
            'unit_price' => 100,
            'unit' => 'pc',
            'total_cost' => 500,
            'user_id' => $user->id,
            'personnel_id' => $personnel->id,
            'project_code' => 'PC-2000',
        ]);

        $payload = [
            'transaction_id' => $transaction->id,
            'report_type' => 'incident_report',
            'report_data' => [
                'incident_date' => now()->toDateString(),
                'location' => 'Main Lab',
                'reported_by' => 'Unit Tester',
                'impact_summary' => 'Broken casing',
                'immediate_action' => 'Isolated item',
                'status' => 'needs_repair',
            ],
            'notes' => 'Needs urgent replacement',
            'reported_at' => now()->toDateString(),
        ];

        $storeResponse = $this->postJson(route('api.inventory.supp_equip_reports.store'), $payload);

        $storeResponse->assertStatus(201);
        $reportId = $storeResponse->json('data.id');

        $this->assertNotEmpty($reportId);
        $this->assertDatabaseHas('supp_equip_reports', [
            'id' => $reportId,
            'transaction_id' => $transaction->id,
            'report_type' => 'incident_report',
        ]);

        $updatePayload = $payload;
        $updatePayload['report_data']['impact_summary'] = 'Updated summary';
        $updatePayload['notes'] = 'Updated notes';

        $this->putJson(route('api.inventory.supp_equip_reports.update', ['id' => $reportId]), $updatePayload)
            ->assertOk();

        $this->assertDatabaseHas('supp_equip_reports', [
            'id' => $reportId,
            'notes' => 'Updated notes',
        ]);

        $this->getJson(route('api.inventory.supp_equip_reports.index', ['per_page' => 'all']))
            ->assertOk()
            ->assertJsonStructure(['data']);

        $this->getJson(route('api.inventory.supp_equip_reports.templates'))
            ->assertOk()
            ->assertJsonStructure(['data']);

        $this->deleteJson(route('api.inventory.supp_equip_reports.destroy', ['id' => $reportId]))
            ->assertOk();

        $this->assertSoftDeleted('supp_equip_reports', ['id' => $reportId]);
    }
}
