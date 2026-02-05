<?php

namespace Tests\Feature\LabEquipment;

use App\Models\RequestFormPivot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabEquipmentRequestGuestApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_create_request_form_pivot(): void
    {
        $payload = [
            'name' => 'Guest Requester',
            'affiliation' => 'CBC',
            'email' => 'guest.requester@example.com',
            'position' => 'Research Assistant',
            'phone' => '09170000003',
            'request_type' => ['Supplies', 'Laboratory Access'],
            'request_details' => 'Need access to lab equipment.',
            'request_purpose' => 'Research activity',
            'project_title' => 'Genome Study',
            'date_of_use' => now()->addDays(3)->toDateString(),
            'time_of_use' => '10:00:00',
            'date_of_use_end' => now()->addDays(4)->toDateString(),
            'time_of_use_end' => '16:00:00',
            'labs_to_use' => ['Lab A', 'Lab B'],
            'equipments_to_use' => ['Microscope'],
            'consumables_to_use' => ['Pipettes'],
            'agreed_clause_1' => true,
            'agreed_clause_2' => true,
            'agreed_clause_3' => true,
        ];

        $response = $this->postJson(route('api.requestFormPivot.post'), $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('requesters', [
            'email' => 'guest.requester@example.com',
            'name' => 'Guest Requester',
        ]);

        $this->assertDatabaseHas('use_request_forms', [
            'request_purpose' => 'Research activity',
            'project_title' => 'Genome Study',
            'date_of_use_end' => now()->addDays(4)->toDateString(),
            'time_of_use_end' => '16:00:00',
        ]);

        $this->assertDatabaseHas('request_forms_pivot', [
            'request_status' => 'pending',
            'agreed_clause_1' => 1,
            'agreed_clause_2' => 1,
            'agreed_clause_3' => 1,
        ]);
    }
}
