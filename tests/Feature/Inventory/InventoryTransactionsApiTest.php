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

class InventoryTransactionsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_transactions_crud_flow(): void
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
        ];

        $incomingResponse = $this->postJson(route('api.inventory.transactions.store'), $incomingPayload);
        $incomingResponse->assertStatus(201);
        $incomingId = $incomingResponse->json('id');

        $this->assertNotEmpty($incomingId);
        $this->assertDatabaseHas('transactions', [
            'id' => $incomingId,
            'transac_type' => Inventory::INCOMING->value,
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

        $this->putJson(route('api.inventory.transactions.update', ['id' => $incomingId]), $updatePayload)
            ->assertOk();

        $this->assertDatabaseHas('transactions', [
            'id' => $incomingId,
            'quantity' => 12,
        ]);

        $this->deleteJson(route('api.inventory.transactions.destroy', ['id' => $incomingId]))
            ->assertOk();

        $this->assertSoftDeleted('transactions', ['id' => $incomingId]);
    }
}
