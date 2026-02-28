<?php

namespace Tests\Feature\SuppEquipReports;

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
use Tests\WithTestRoles;

class SuppEquipReportsApiTest extends TestCase
{
    use RefreshDatabase, WithTestRoles;

    protected $seeder = \Database\Seeders\DatabaseSeeder::class;

    public function test_supp_equip_report_endpoints(): void
    {
        $user = $this->createAdminUser();
        Sanctum::actingAs($user);

        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        $item = Item::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
        $personnel = Personnel::factory()->create([
            'employee_id' => 'EMP-REPORTS',
        ]);

        $transaction = Transaction::factory()->create([
            'item_id' => $item->id,
            'barcode' => 'CBC-01-000002',
            'transac_type' => Inventory::INCOMING->value,
            'quantity' => 4,
            'unit_price' => 120,
            'unit' => 'pc',
            'total_cost' => 480,
            'user_id' => $user->id,
            'personnel_id' => $personnel->id,
            'project_code' => 'PC-4000',
        ]);

        $payload = [
            'transaction_id' => $transaction->id,
            'report_type' => 'incident_report',
            'report_data' => [
                'incident_date' => now()->toDateString(),
                'location' => 'Lab Annex',
                'reported_by' => 'Quality Admin',
                'impact_summary' => 'Damaged container',
                'immediate_action' => 'Isolated item',
                'status' => 'needs_repair',
            ],
            'notes' => 'Requires inspection',
            'reported_at' => now()->toDateString(),
        ];

        $storeResponse = $this->postJson(route('api.inventory.supp_equip_reports.store'), $payload);
        $storeResponse->assertStatus(201);
        $reportId = $storeResponse->json('data.id');

        $this->getJson(route('api.inventory.supp_equip_reports.index', ['per_page' => 'all']))
            ->assertOk()
            ->assertJsonStructure(['data']);

        $this->getJson(route('api.inventory.supp_equip_reports.templates'))
            ->assertOk()
            ->assertJsonStructure(['data']);

        $updatePayload = $payload;
        $updatePayload['notes'] = 'Updated notes';

        $this->putJson(route('api.inventory.supp_equip_reports.update', ['id' => $reportId]), $updatePayload)
            ->assertOk();

        $this->deleteJson(route('api.inventory.supp_equip_reports.destroy', ['id' => $reportId]))
            ->assertOk();

        $this->assertSoftDeleted('supp_equip_reports', ['id' => $reportId]);
    }
}
