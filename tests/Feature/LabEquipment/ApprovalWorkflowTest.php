<?php

namespace Tests\Feature\LabEquipment;

use App\Models\RequestFormPivot;
use App\Models\Requester;
use App\Models\UseRequestForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\WithTestRoles;

class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase, WithTestRoles;

    protected $seeder = \Database\Seeders\DatabaseSeeder::class;

    public function test_authenticated_user_can_update_request_approval(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $requester = Requester::factory()->create();
        $form = UseRequestForm::factory()->create();

        $pivot = RequestFormPivot::factory()->create([
            'requester_id' => $requester->id,
            'form_id' => $form->id,
            'request_status' => 'pending',
        ]);

        $payload = [
            'request_status' => 'approved',
            'approval_constraint' => 'Valid for 7 days',
            'disapproved_remarks' => null,
        ];

        $response = $this->putJson(route('api.requestFormPivot.put', ['request_pivot_id' => $pivot->id]), $payload);

        $response->assertOk();

        $this->assertDatabaseHas('request_forms_pivot', [
            'id' => $pivot->id,
            'request_status' => 'approved',
            'approved_by' => $admin->name,
        ]);
    }

    public function test_user_without_permission_cannot_update_request_approval(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $pivot = RequestFormPivot::factory()->create([
            'request_status' => 'pending',
        ]);

        $response = $this->putJson(route('api.requestFormPivot.put', ['request_pivot_id' => $pivot->id]), [
            'request_status' => 'approved',
        ]);

        $response->assertForbidden();
    }
}
