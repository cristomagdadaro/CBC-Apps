<?php

namespace Tests\Feature\Inventory;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\WithTestRoles;

class InventorySuppliersApiTest extends TestCase
{
    use RefreshDatabase, WithTestRoles;

    protected $seeder = \Database\Seeders\DatabaseSeeder::class;

    public function test_supplier_crud_flow(): void
    {
        Sanctum::actingAs($this->createAdminUser());

        $payload = [
            'name' => 'Supplier One',
            'address' => '123 Test St',
            'phone' => '09171234567',
            'email' => 'supplier1@example.com',
            'description' => 'Primary supplier',
        ];

        $createResponse = $this->postJson(route('api.inventory.suppliers.store'), $payload);

        $createResponse->assertStatus(201);
        $supplierId = $createResponse->json('id');

        $this->assertNotEmpty($supplierId);
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplierId,
            'name' => 'Supplier One',
        ]);

        $updatePayload = [
            'name' => 'Supplier One Updated',
            'address' => '456 Updated Ave',
            'phone' => '09179999999',
            'email' => 'supplier1updated@example.com',
            'description' => 'Updated supplier',
        ];

        $this->putJson(route('api.inventory.suppliers.update', ['id' => $supplierId]), $updatePayload)
            ->assertOk();

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplierId,
            'name' => 'Supplier One Updated',
        ]);

        $indexResponse = $this->getJson(route('api.inventory.suppliers.index', ['per_page' => 'all']));

        $indexResponse->assertOk()
            ->assertJsonStructure(['data']);

        $this->deleteJson(route('api.inventory.suppliers.destroy', ['id' => $supplierId]))
            ->assertOk();

        $this->assertSoftDeleted('suppliers', ['id' => $supplierId]);
    }
}
