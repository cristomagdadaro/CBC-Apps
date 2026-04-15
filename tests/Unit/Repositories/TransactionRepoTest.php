<?php

namespace Tests\Unit\Repositories;

use App\Enums\Inventory as InventoryEnum;
use App\Models\Category;
use App\Models\Item;
use App\Models\LaboratoryEquipmentLog;
use App\Models\Personnel;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\LaboratoryEquipmentLogRepo;
use App\Repositories\TransactionRepo;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionRepoTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_persists_incoming_transaction_components(): void
    {
        ['item' => $item, 'personnel' => $personnel, 'user' => $user] = $this->createInventoryContext();

        $transaction = app(TransactionRepo::class)->create([
            'item_id' => $item->id,
            'barcode' => 'CBC-01-900001',
            'transac_type' => InventoryEnum::INCOMING->value,
            'quantity' => 10,
            'unit_price' => 5,
            'unit' => 'pc',
            'total_cost' => 50,
            'personnel_id' => $personnel->id,
            'user_id' => $user->id,
            'expiration' => now()->addMonth()->toDateString(),
            'remarks' => 'Initial stock',
            'project_code' => 'PC-REPO',
            'components' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 4,
                    'unit' => 'pc',
                    'barcode_prri' => '1234567890',
                    'prri_component_no' => '12',
                    'remarks' => 'Component A',
                ],
                [
                    'item_id' => $item->id,
                    'quantity' => 6,
                    'unit' => 'pc',
                    'barcode_prri' => '1234567891',
                    'prri_component_no' => '7',
                    'remarks' => 'Component B',
                ],
            ],
        ]);

        $transaction->refresh();

        $this->assertCount(2, $transaction->components);
        $this->assertSame('00012', $transaction->components[0]->prri_component_no);
        $this->assertSame('00007', $transaction->components[1]->prri_component_no);
    }

    public function test_update_replaces_existing_incoming_components(): void
    {
        ['item' => $item, 'personnel' => $personnel, 'user' => $user] = $this->createInventoryContext();
        $repo = app(TransactionRepo::class);

        $transaction = $repo->create([
            'item_id' => $item->id,
            'barcode' => 'CBC-01-900002',
            'transac_type' => InventoryEnum::INCOMING->value,
            'quantity' => 8,
            'unit_price' => 5,
            'unit' => 'pc',
            'total_cost' => 40,
            'personnel_id' => $personnel->id,
            'user_id' => $user->id,
            'expiration' => now()->addMonth()->toDateString(),
            'remarks' => 'Seed stock',
            'project_code' => 'PC-REPO-2',
            'components' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 8,
                    'prri_component_no' => '1',
                ],
            ],
        ]);

        $updated = $repo->update($transaction->id, [
            'remarks' => 'Updated stock',
            'components' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 3,
                    'prri_component_no' => '15',
                    'remarks' => 'Replacement component',
                ],
            ],
        ]);

        $updated->refresh();

        $this->assertCount(1, $updated->components);
        $this->assertSame(3.0, (float) $updated->components->first()->quantity);
        $this->assertSame('00015', $updated->components->first()->prri_component_no);
        $this->assertSame('Updated stock', $updated->remarks);
    }

    public function test_search_without_filter_matches_transaction_related_item_and_personnel_names(): void
    {
        ['item' => $item, 'personnel' => $personnel, 'user' => $user] = $this->createInventoryContext([
            'item' => [
                'name' => 'Atomic Spectrometer',
                'brand' => 'LabWorks',
            ],
            'personnel' => [
                'fname' => 'Alice',
                'mname' => null,
                'lname' => 'Cruz',
                'suffix' => null,
            ],
        ]);

        $transaction = app(TransactionRepo::class)->create([
            'item_id' => $item->id,
            'barcode' => 'CBC-01-900003',
            'transac_type' => InventoryEnum::INCOMING->value,
            'quantity' => 5,
            'unit_price' => 5,
            'unit' => 'pc',
            'total_cost' => 25,
            'personnel_id' => $personnel->id,
            'user_id' => $user->id,
            'remarks' => 'Search seed',
        ]);

        $itemResults = app(TransactionRepo::class)->search(new Collection([
            'search' => 'Atomic Spectrometer',
        ]), false);

        $personnelResults = app(TransactionRepo::class)->search(new Collection([
            'search' => 'Alice Cruz',
        ]), false);

        $this->assertCount(1, $itemResults);
        $this->assertSame($transaction->id, $itemResults->first()->id);
        $this->assertCount(1, $personnelResults);
        $this->assertSame($transaction->id, $personnelResults->first()->id);
    }

    public function test_search_with_relation_filters_matches_transaction_relations(): void
    {
        ['item' => $item, 'personnel' => $personnel, 'user' => $user] = $this->createInventoryContext([
            'item' => [
                'name' => 'Benchtop Centrifuge',
                'brand' => 'SpinTech',
            ],
            'personnel' => [
                'fname' => 'Maria',
                'mname' => null,
                'lname' => 'Santos',
                'suffix' => null,
            ],
        ]);

        $transaction = app(TransactionRepo::class)->create([
            'item_id' => $item->id,
            'barcode' => 'CBC-01-900004',
            'transac_type' => InventoryEnum::OUTGOING->value,
            'quantity' => 1,
            'unit_price' => 10,
            'unit' => 'pc',
            'total_cost' => 10,
            'personnel_id' => $personnel->id,
            'user_id' => $user->id,
            'remarks' => 'Filtered search seed',
        ]);

        $itemResults = app(TransactionRepo::class)->search(new Collection([
            'search' => 'Benchtop Centrifuge',
            'filter' => 'item',
        ]), false);

        $personnelResults = app(TransactionRepo::class)->search(new Collection([
            'search' => 'Maria Santos',
            'filter' => 'personnel_id',
        ]), false);

        $this->assertCount(1, $itemResults);
        $this->assertSame($transaction->id, $itemResults->first()->id);
        $this->assertCount(1, $personnelResults);
        $this->assertSame($transaction->id, $personnelResults->first()->id);
    }

    public function test_get_remaining_stocks_returns_one_row_per_barcode_even_when_transaction_metadata_differs(): void
    {
        ['item' => $item, 'personnel' => $personnel, 'user' => $user] = $this->createInventoryContext();
        $repo = app(TransactionRepo::class);

        $repo->create([
            'item_id' => $item->id,
            'barcode' => 'CBC-01-900005',
            'barcode_prri' => 'PRRI-900005',
            'transac_type' => InventoryEnum::INCOMING->value,
            'quantity' => 10,
            'unit_price' => 5,
            'unit' => 'roll',
            'total_cost' => 50,
            'personnel_id' => $personnel->id,
            'user_id' => $user->id,
            'remarks' => 'Incoming stock',
            'project_code' => 'PC-STOCK',
            'expiration' => now()->addDays(10)->toDateString(),
        ]);

        $repo->create([
            'item_id' => $item->id,
            'barcode' => 'CBC-01-900005',
            'barcode_prri' => null,
            'transac_type' => InventoryEnum::OUTGOING->value,
            'quantity' => 2,
            'unit_price' => 5,
            'unit' => 'box',
            'total_cost' => 10,
            'personnel_id' => $personnel->id,
            'user_id' => $user->id,
            'remarks' => 'Outgoing stock',
            'project_code' => null,
        ]);

        $repo->create([
            'item_id' => $item->id,
            'barcode' => 'CBC-01-900005',
            'barcode_prri' => 'PRRI-900005',
            'transac_type' => InventoryEnum::OUTGOING->value,
            'quantity' => 1,
            'unit_price' => 5,
            'unit' => 'roll',
            'total_cost' => 5,
            'personnel_id' => $personnel->id,
            'user_id' => $user->id,
            'remarks' => 'Outgoing stock 2',
            'project_code' => null,
        ]);

        $stocks = $repo->getRemainingStocks(new Collection([
            'include_all_categories' => true,
            'paginate' => false,
            'per_page' => '*',
        ]));

        $rows = collect($stocks->get('data'))->where('barcode', 'CBC-01-900005')->values();

        $this->assertCount(1, $rows);
        $this->assertSame('roll', $rows->first()->unit);
        $this->assertSame('PRRI-900005', $rows->first()->barcode_prri);
        $this->assertSame('PC-STOCK', $rows->first()->project_code);
        $this->assertSame(10.0, (float) $rows->first()->total_ingoing);
        $this->assertSame(3.0, (float) $rows->first()->total_outgoing);
        $this->assertSame(7.0, (float) $rows->first()->remaining_quantity);
    }

    public function test_transaction_actor_display_name_prefers_personnel_then_falls_back_to_user(): void
    {
        ['item' => $item, 'personnel' => $personnel, 'user' => $user] = $this->createInventoryContext([
            'personnel' => [
                'fname' => 'Jamie',
                'mname' => 'Lee',
                'lname' => 'Torres',
                'suffix' => null,
            ],
        ]);

        $withPersonnel = Transaction::factory()->create([
            'item_id' => $item->id,
            'barcode' => 'CBC-01-900006',
            'transac_type' => InventoryEnum::OUTGOING->value,
            'quantity' => 1,
            'unit' => 'pc',
            'personnel_id' => $personnel->id,
            'user_id' => $user->id,
        ])->load(['personnel', 'user']);

        $userOnly = Transaction::factory()->create([
            'item_id' => $item->id,
            'barcode' => 'CBC-01-900007',
            'transac_type' => InventoryEnum::OUTGOING->value,
            'quantity' => 1,
            'unit' => 'pc',
            'personnel_id' => null,
            'user_id' => $user->id,
        ])->load(['personnel', 'user']);

        $this->assertSame('Jamie L. Torres', $withPersonnel->actor_display_name);
        $this->assertSame($user->name, $userOnly->actor_display_name);
    }

    public function test_laboratory_log_search_matches_related_equipment_and_personnel_names(): void
    {
        ['item' => $item, 'personnel' => $personnel, 'user' => $user] = $this->createInventoryContext([
            'item' => [
                'name' => 'Microplate Reader',
                'brand' => 'ReaderCo',
            ],
            'personnel' => [
                'fname' => 'Nina',
                'mname' => null,
                'lname' => 'Reyes',
                'suffix' => null,
            ],
        ]);

        $log = LaboratoryEquipmentLog::factory()->create([
            'equipment_id' => $item->id,
            'personnel_id' => $personnel->id,
            'checked_in_by' => $user->id,
            'status' => 'active',
            'actual_end_at' => null,
        ]);

        $equipmentResults = app(LaboratoryEquipmentLogRepo::class)->search(new Collection([
            'search' => 'Microplate Reader',
            'filter' => 'equipment_id',
        ]), false);

        $personnelResults = app(LaboratoryEquipmentLogRepo::class)->search(new Collection([
            'search' => 'Nina Reyes',
            'filter' => 'personnel.name',
        ]), false);

        $this->assertCount(1, $equipmentResults);
        $this->assertSame($log->id, $equipmentResults->first()->id);
        $this->assertCount(1, $personnelResults);
        $this->assertSame($log->id, $personnelResults->first()->id);
    }

    private function createInventoryContext(array $overrides = []): array
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        $item = Item::factory()->create(array_merge([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ], $overrides['item'] ?? []));
        $personnel = Personnel::factory()->create(array_merge([
            'employee_id' => 'EMP-REPO',
        ], $overrides['personnel'] ?? []));
        $user = User::factory()->create();

        return compact('item', 'personnel', 'user');
    }
}
