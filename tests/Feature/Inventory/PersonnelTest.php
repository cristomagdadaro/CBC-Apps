<?php

namespace Tests\Feature\Inventory;

use App\Models\Personnel;
use App\Models\NewBarcode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\WithTestRoles;

class PersonnelTest extends TestCase
{
    use RefreshDatabase, WithTestRoles;

    protected $seeder = \Database\Seeders\DatabaseSeeder::class;

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
            'is_philrice_employee' => true,
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
            'is_philrice_employee' => true,
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

    public function test_non_philrice_personnel_receives_auto_incremented_cbc_id_and_fresh_profile_flag(): void
    {
        Sanctum::actingAs($this->createAdminUser());

        NewBarcode::query()->create([
            'room' => 'personnel_external_id',
            'barcode' => 'CBC-' . now()->format('y') . '-0007',
            'name' => 'For OJT/Thesis/Outsider ID',
        ]);

        $response = $this->postJson(route('api.inventory.personnels.store'), [
            'fname' => 'Ojt',
            'mname' => null,
            'lname' => 'Student',
            'suffix' => null,
            'position' => 'Thesis Student',
            'phone' => '09170000002',
            'address' => 'Dormitory',
            'email' => null,
            'is_philrice_employee' => false,
        ]);

        $response->assertCreated();

        $personnelId = $response->json('id');
        $expectedEmployeeId = 'CBC-' . now()->format('y') . '-0008';

        $this->assertDatabaseHas('personnels', [
            'id' => $personnelId,
            'employee_id' => $expectedEmployeeId,
        ]);
        $this->assertDatabaseHas('new_barcodes', [
            'room' => 'personnel_external_id',
            'barcode' => $expectedEmployeeId,
        ]);

        $this->assertNull(Personnel::query()->findOrFail($personnelId)->updated_at);
    }
}
