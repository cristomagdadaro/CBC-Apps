<?php

namespace Tests\Feature\Inventory;

use App\Enums\Inventory;
use App\Models\Category;
use App\Models\Item;
use App\Models\Personnel;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionComponent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\WithTestRoles;

class TransactionsTest extends TestCase
{
    use RefreshDatabase, WithTestRoles;

    protected $seeder = \Database\Seeders\DatabaseSeeder::class;

    public function test_transactions_crud_flow(): void
    {
        $user = $this->createAdminUser();
        Sanctum::actingAs($user);

        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        $item = Item::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
        $componentItem = Item::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
        $personnel = Personnel::factory()->create([
            'employee_id' => 'EMP-TRANSACTION',
        ]);

        $barcodeResponse = $this->getJson(route('api.inventory.transactions.genbarcode', ['room' => '01']));
        $barcodeResponse->assertOk();
        $barcode = $barcodeResponse->json('data.barcode');

        $incomingPayload = [
            'item_id' => $item->id,
            'barcode' => $barcode,
            'transac_type' => Inventory::INCOMING->value,
            'quantity' => 10,
            'unit_price' => 100,
            'unit' => 'pc',
            'total_cost' => 1000,
            'user_id' => $user->id,
            'expiration' => now()->addDays(30)->toDateString(),
            'remarks' => 'Incoming test',
            'project_code' => 'PC-1000',
            'personnel_id' => $personnel->id,
            'components' => [
                [
                    'item_id' => $componentItem->id,
                    'quantity' => 2,
                    'unit' => 'pc',
                    'prri_component_no' => '12',
                    'expiration' => now()->addDays(15)->toDateString(),
                    'remarks' => 'Attached monitor',
                ],
            ],
        ];

        $incomingResponse = $this->postJson(route('api.inventory.transactions.store'), $incomingPayload);
        $incomingResponse->assertStatus(201);
        $incomingId = $incomingResponse->json('id');

        $this->assertNotEmpty($incomingId);
        $this->assertDatabaseHas('transactions', [
            'id' => $incomingId,
            'transac_type' => Inventory::INCOMING->value,
        ]);
        $this->assertDatabaseHas('transaction_components', [
            'transaction_id' => $incomingId,
            'item_id' => $componentItem->id,
            'prri_component_no' => '00012',
        ]);

        $outgoingPayload = [
            'item_id' => $item->id,
            'barcode' => $barcode,
            'transac_type' => Inventory::OUTGOING->value,
            'quantity' => 2,
            'unit_price' => 100,
            'unit' => 'pc',
            'total_cost' => 200,
            'user_id' => $user->id,
            'expiration' => now()->addDays(30)->toDateString(),
            'remarks' => 'Outgoing test',
            'project_code' => 'PC-1000',
            'personnel_id' => $personnel->id,
        ];

        $outgoingResponse = $this->postJson(route('api.inventory.transactions.store'), $outgoingPayload);
        $outgoingResponse->assertStatus(201);
        $outgoingId = $outgoingResponse->json('id');

        $this->assertNotEmpty($outgoingId);
        $this->assertDatabaseHas('transactions', [
            'id' => $outgoingId,
            'transac_type' => Inventory::OUTGOING->value,
        ]);

        $indexResponse = $this->getJson(route('api.inventory.transactions.index', ['per_page' => 'all']));

        $indexResponse->assertOk()
            ->assertJsonStructure(['data']);

        $updatePayload = $incomingPayload;
        $updatePayload['quantity'] = 12;
        $updatePayload['components'] = [
            [
                'item_id' => $componentItem->id,
                'quantity' => 3,
                'unit' => 'pc',
                'prri_component_no' => '7',
                'expiration' => now()->addDays(20)->toDateString(),
                'remarks' => 'Updated attachment',
            ],
        ];

        $this->putJson(route('api.inventory.transactions.update', ['id' => $incomingId]), $updatePayload)
            ->assertOk();

        $this->assertDatabaseHas('transactions', [
            'id' => $incomingId,
            'quantity' => 12,
        ]);
        $this->assertDatabaseHas('transaction_components', [
            'transaction_id' => $incomingId,
            'item_id' => $componentItem->id,
            'quantity' => 3,
            'prri_component_no' => '00007',
        ]);

        $this->deleteJson(route('api.inventory.transactions.destroy', ['id' => $incomingId]))
            ->assertOk();

        $this->assertSoftDeleted('transactions', ['id' => $incomingId]);
        $this->assertSoftDeleted('transaction_components', ['transaction_id' => $incomingId]);
    }

    public function test_transaction_can_be_permanently_deleted_after_barcode_confirmation(): void
    {
        $user = $this->createAdminUser();
        Sanctum::actingAs($user);

        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        $item = Item::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
        $componentItem = Item::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
        $personnel = Personnel::factory()->create([
            'employee_id' => 'EMP-TRANSACTION-FORCE',
        ]);

        $transaction = Transaction::factory()->create([
            'item_id' => $item->id,
            'barcode' => 'CBC-23-000216',
            'transac_type' => Inventory::INCOMING->value,
            'quantity' => 5,
            'unit' => 'pc',
            'unit_price' => 25,
            'total_cost' => 125,
            'personnel_id' => $personnel->id,
            'user_id' => $user->id,
        ]);

        TransactionComponent::query()->create([
            'transaction_id' => $transaction->id,
            'item_id' => $componentItem->id,
            'quantity' => 1,
            'unit' => 'pc',
            'prri_component_no' => '00001',
        ]);

        $this->deleteJson(route('api.inventory.transactions.destroy', ['id' => $transaction->id]), [
            'force' => true,
            'confirmation_barcode' => 'WRONG-CODE',
        ])->assertStatus(422);

        $this->assertDatabaseHas('transactions', ['id' => $transaction->id]);

        $this->deleteJson(route('api.inventory.transactions.destroy', ['id' => $transaction->id]), [
            'force' => true,
            'confirmation_barcode' => 'CBC-23-000216',
        ])->assertOk();

        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
        $this->assertDatabaseMissing('transaction_components', ['transaction_id' => $transaction->id]);
    }
}
