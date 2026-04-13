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
use Tests\TestCase;
use Tests\WithTestRoles;

class GuestOutgoingTest extends TestCase
{
    use RefreshDatabase, WithTestRoles;

    protected $seeder = \Database\Seeders\DatabaseSeeder::class;

    public function test_guest_outgoing_and_remaining_stocks(): void
    {
        $user = $this->createAdminUser();

        // Consumable category names that are included in remaining-stocks query (IDs 1,2,3,5,6)
        $consumableCategoryNames = ['Office Supplies', 'IEC Materials', 'Tokens', 'ICT Supplies', 'Laboratory Consumables'];
        $category = Category::query()->whereIn('name', $consumableCategoryNames)->first()
            ?? Category::factory()->create(['name' => 'Office Supplies']);
        $supplier = Supplier::query()->first() ?? Supplier::factory()->create();
        $item = Item::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
        $personnel = Personnel::query()->first() ?? Personnel::factory()->create([
            'employee_id' => 'EMP-GUEST',
        ]);

        $incoming = Transaction::factory()->create([
            'item_id' => $item->id,
            'barcode' => 'CBC-01-000010',
            'transac_type' => Inventory::INCOMING->value,
            'quantity' => 10,
            'unit_price' => 100,
            'unit' => 'pc',
            'total_cost' => 1000,
            'user_id' => $user->id,
            'personnel_id' => $personnel->id,
            'project_code' => 'PC-3000',
        ]);

        $negativeItem = Item::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);

        Transaction::factory()->create([
            'item_id' => $negativeItem->id,
            'barcode' => 'CBC-01-000099',
            'transac_type' => Inventory::OUTGOING->value,
            'quantity' => 1,
            'unit' => 'pc',
            'user_id' => $user->id,
            'personnel_id' => $personnel->id,
        ]);

        $this->getJson(route('api.inventory.items.public'))
            ->assertOk()
            ->assertJsonStructure(['data']);

        $this->getJson(route('api.inventory.equipments.public'))
            ->assertOk()
            ->assertJsonStructure(['data']);

        $this->getJson(route('api.inventory.laboratories.public'))
            ->assertOk()
            ->assertJsonStructure(['data']);

        $remainingResponse = $this->getJson(route('api.inventory.transactions.remaining-stocks'));

        $remainingResponse->assertOk()
            ->assertJsonStructure(['data']);

        $this->assertNotEmpty($remainingResponse->json('data'));
        $this->assertNotContains(
            (string) $negativeItem->id,
            collect($remainingResponse->json('data'))->pluck('item_id')->map(fn ($id) => (string) $id)->all()
        );

        $outgoingPayload = [
            'item_id' => $incoming->item_id,
            'barcode' => $incoming->barcode,
            'transac_type' => Inventory::OUTGOING->value,
            'quantity' => 2,
            'unit' => 'pc',
            'employee_id' => $personnel->employee_id,
            'remarks' => 'Guest outgoing',
        ];

        $outgoingResponse = $this->postJson(route('api.inventory.transactions.store.public'), $outgoingPayload);

        $outgoingResponse->assertStatus(201);

        $this->assertDatabaseHas('transactions', [
            'item_id' => $incoming->item_id,
            'barcode' => $incoming->barcode,
            'transac_type' => Inventory::OUTGOING->value,
            'quantity' => 2,
        ]);

        $remainingAfterResponse = $this->getJson(route('api.inventory.transactions.remaining-stocks'));

        $remainingAfterResponse->assertOk()
            ->assertJsonStructure(['data']);
    }
}
