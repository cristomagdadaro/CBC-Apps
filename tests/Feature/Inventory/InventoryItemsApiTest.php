<?php

namespace Tests\Feature\Inventory;

use App\Models\Category;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\WithTestRoles;

class InventoryItemsApiTest extends TestCase
{
    use RefreshDatabase, WithTestRoles;

    public function test_item_crud_flow(): void
    {
        Sanctum::actingAs($this->createAdminUser());

        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $payload = [
            'name' => 'Item A',
            'brand' => 'Brand A',
            'description' => 'Test item',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'image' => null,
        ];

        $createResponse = $this->postJson(route('api.inventory.items.store'), $payload);

        $createResponse->assertStatus(201);
        $itemId = $createResponse->json('id');

        $this->assertNotEmpty($itemId);
        $this->assertDatabaseHas('items', [
            'id' => $itemId,
            'name' => 'Item A',
            'brand' => 'Brand A',
        ]);

        $updatePayload = [
            'name' => 'Item A Updated',
            'brand' => 'Brand A',
            'description' => 'Updated description',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'image' => null,
        ];

        $this->putJson(route('api.inventory.items.update', ['id' => $itemId]), $updatePayload)
            ->assertOk();

        $this->assertDatabaseHas('items', [
            'id' => $itemId,
            'name' => 'Item A Updated',
        ]);

        $indexResponse = $this->getJson(route('api.inventory.items.index', ['per_page' => 'all']));

        $indexResponse->assertOk()
            ->assertJsonStructure(['data']);

        $this->deleteJson(route('api.inventory.items.destroy', ['id' => $itemId]))
            ->assertOk();

        $this->assertSoftDeleted('items', ['id' => $itemId]);
    }
}
