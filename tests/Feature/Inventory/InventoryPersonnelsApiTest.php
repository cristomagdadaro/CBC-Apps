<?php

namespace Tests\Feature\Inventory;

use App\Models\Personnel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\WithTestRoles;

class InventoryPersonnelsApiTest extends TestCase
{
    use RefreshDatabase, WithTestRoles;

    public function test_personnel_crud_flow(): void
    {
        Sanctum::actingAs($this->createAdminUser());

        $payload = [
            'fname' => 'Jane',
            'mname' => 'Q',
            'lname' => 'Doe',
            'suffix' => null,
            'position' => 'Technician',
            'phone' => '09170000000',
            'address' => 'Sample Address',
            'email' => 'jane.doe@example.com',
            'employee_id' => 'EMP-001',
        ];

        $createResponse = $this->postJson(route('api.inventory.personnels.store'), $payload);

        $createResponse->assertStatus(201);
        $personnelId = $createResponse->json('id');

        $this->assertNotEmpty($personnelId);
        $this->assertDatabaseHas('personnels', [
            'id' => $personnelId,
            'fname' => 'Jane',
            'lname' => 'Doe',
        ]);

        $updatePayload = [
            'fname' => 'Jane',
            'mname' => 'Q',
            'lname' => 'Smith',
            'suffix' => null,
            'position' => 'Senior Technician',
            'phone' => '09170000001',
            'address' => 'Updated Address',
            'email' => 'jane.smith@example.com',
            'employee_id' => 'EMP-001',
        ];

        $this->putJson(route('api.inventory.personnels.update', ['id' => $personnelId]), $updatePayload)
            ->assertOk();

        $this->assertDatabaseHas('personnels', [
            'id' => $personnelId,
            'lname' => 'Smith',
        ]);

        $indexResponse = $this->getJson(route('api.inventory.personnels.index', ['per_page' => 'all']));

        $indexResponse->assertOk()
            ->assertJsonStructure(['data']);

        $this->deleteJson(route('api.inventory.personnels.destroy', ['id' => $personnelId]))
            ->assertOk();

        $this->assertSoftDeleted('personnels', ['id' => $personnelId]);
    }
}
