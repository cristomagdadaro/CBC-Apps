<?php

namespace Tests\Feature\LabEquipment;

use App\Models\RequestFormPivot;
use App\Models\Requester;
use App\Models\UseRequestForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LabEquipmentRequestIndexApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_request_forms(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $requester = Requester::factory()->create();
        $form = UseRequestForm::factory()->create();

        RequestFormPivot::factory()->count(2)->create([
            'requester_id' => $requester->id,
            'form_id' => $form->id,
        ]);

        $response = $this->getJson(route('api.requestFormPivot.index', ['per_page' => 'all']));

        $response->assertOk()
            ->assertJsonStructure(['data']);

        $this->assertCount(2, $response->json('data'));
    }
}
