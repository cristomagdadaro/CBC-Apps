<?php

namespace Tests\Feature\Inventory;

use App\Enums\Inventory as InventoryEnum;
use App\Models\Category;
use App\Models\Item;
use App\Models\Personnel;
use App\Models\SuppEquipReport;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SuppEquipReportCrudTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_can_create_a_supply_equipment_report_via_public_endpoint(): void
    {
        ['item' => $item, 'personnel' => $personnel, 'user' => $user] = $this->buildInventoryContext();

        $transaction = $this->createIncomingTransaction($item, $personnel, $user);

        $response = $this->postJson(
            route('api.inventory.supp_equip_reports.store.public'),
            $this->incidentReportPayload($transaction->id)
        );

        $response->assertCreated()
            ->assertJsonPath('data.transaction_id', $transaction->id)
            ->assertJsonPath('data.item_id', $item->id)
            ->assertJsonPath('data.report_type', 'incident_report')
            ->assertJsonPath('data.transaction.id', $transaction->id)
            ->assertJsonPath('data.transaction.item.id', $item->id);

        $this->assertDatabaseHas('supp_equip_reports', [
            'transaction_id' => $transaction->id,
            'item_id' => $item->id,
            'report_type' => 'incident_report',
        ]);
    }

    /** @test */
    public function authenticated_admin_can_update_a_report_and_receive_loaded_relations(): void
    {
        ['item' => $item, 'personnel' => $personnel, 'user' => $user] = $this->buildInventoryContext();

        $transaction = $this->createIncomingTransaction($item, $personnel, $user);
        $admin = User::factory()->create(['is_admin' => true]);

        $report = SuppEquipReport::create([
            'transaction_id' => $transaction->id,
            'item_id' => $item->id,
            'user_id' => $admin->id,
            'report_type' => 'incident_report',
            'report_data' => $this->incidentReportPayload($transaction->id)['report_data'],
            'notes' => 'Initial note',
            'reported_at' => '2026-03-29',
        ]);

        Sanctum::actingAs($admin);

        $payload = $this->incidentReportPayload($transaction->id, [
            'notes' => 'Updated maintenance note',
            'report_data' => [
                'impact_summary' => 'Updated impact summary',
                'immediate_action' => 'Escalated to facilities',
                'status' => 'needs_repair',
            ],
        ]);

        $this->putJson(route('api.inventory.supp_equip_reports.update', $report->id), $payload)
            ->assertOk()
            ->assertJsonPath('data.id', $report->id)
            ->assertJsonPath('data.transaction.id', $transaction->id)
            ->assertJsonPath('data.transaction.item.id', $item->id)
            ->assertJsonPath('data.transaction.personnel.id', $personnel->id)
            ->assertJsonPath('data.report_data.status', 'needs_repair')
            ->assertJsonPath('data.notes', 'Updated maintenance note');

        $this->assertDatabaseHas('supp_equip_reports', [
            'id' => $report->id,
            'transaction_id' => $transaction->id,
            'item_id' => $item->id,
            'notes' => 'Updated maintenance note',
        ]);
    }

    /** @test */
    public function update_page_receives_a_single_loaded_report_object(): void
    {
        ['item' => $item, 'personnel' => $personnel, 'user' => $user] = $this->buildInventoryContext();

        $transaction = $this->createIncomingTransaction($item, $personnel, $user);
        $admin = User::factory()->create(['is_admin' => true]);

        $report = SuppEquipReport::create([
            'transaction_id' => $transaction->id,
            'item_id' => $item->id,
            'user_id' => $admin->id,
            'report_type' => 'incident_report',
            'report_data' => $this->incidentReportPayload($transaction->id)['report_data'],
            'notes' => 'Needs follow-up inspection',
            'reported_at' => '2026-03-30',
        ])->load(['transaction.item', 'transaction.personnel', 'item', 'user']);

        $this->actingAs($admin)
            ->get(route('suppEquipReports.show', $report->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Inventory/SuppEquipReports/SuppEquipReportsFormPage')
                ->where('mode', 'update')
                ->where('data.id', $report->id)
                ->where('data.transaction.id', $transaction->id)
                ->where('data.transaction.item.id', $item->id)
                ->where('data.transaction.personnel.id', $personnel->id)
                ->where('data.notes', 'Needs follow-up inspection')
            );
    }

    private function buildInventoryContext(): array
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        $item = Item::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
        $personnel = Personnel::factory()->create();
        $user = User::factory()->create();

        return compact('item', 'personnel', 'user');
    }

    private function createIncomingTransaction(Item $item, Personnel $personnel, User $user): Transaction
    {
        return Transaction::create([
            'item_id' => $item->id,
            'barcode' => 'CBC-01-000001',
            'barcode_prri' => 'PRRI-000001',
            'transac_type' => InventoryEnum::INCOMING->value,
            'quantity' => 10,
            'unit_price' => 500,
            'unit' => 'pc',
            'total_cost' => 5000,
            'personnel_id' => $personnel->id,
            'user_id' => $user->id,
            'expiration' => now()->addMonth()->toDateString(),
            'remarks' => 'Inventory intake',
            'project_code' => 'PC-TEST',
            'par_no' => 'PAR-001',
            'condition' => 'Serviceable',
        ]);
    }

    private function incidentReportPayload(string $transactionId, array $overrides = []): array
    {
        $payload = [
            'transaction_id' => $transactionId,
            'report_type' => 'incident_report',
            'report_data' => [
                'incident_date' => '2026-03-30',
                'location' => 'Chemistry Laboratory',
                'reported_by' => 'Test Personnel',
                'impact_summary' => 'Equipment casing cracked during transport.',
                'immediate_action' => 'Unit isolated for inspection.',
                'status' => 'operational',
            ],
            'notes' => 'Initial inspection logged.',
            'reported_at' => '2026-03-30',
        ];

        return array_replace_recursive($payload, $overrides);
    }
}