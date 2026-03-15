<?php

namespace Tests\Unit\Repositories;

use App\Enums\Inventory as InventoryEnum;
use App\Models\Category;
use App\Models\Item;
use App\Models\Personnel;
use App\Models\Supplier;
use App\Models\User;
use App\Repositories\TransactionRepo;
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

    private function createInventoryContext(): array
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        $item = Item::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
        $personnel = Personnel::factory()->create(['employee_id' => 'EMP-REPO']);
        $user = User::factory()->create();

        return compact('item', 'personnel', 'user');
    }
}
