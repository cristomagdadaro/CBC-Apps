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

class RequestIndexTest extends TestCase
{
    use RefreshDatabase, WithTestRoles;

    protected $seeder = \Database\Seeders\DatabaseSeeder::class;

    public function test_authenticated_user_can_list_request_forms(): void
    {
        Sanctum::actingAs($this->createAdminUser());

        $requester = Requester::factory()->create();
        $form = UseRequestForm::factory()->create();

        RequestFormPivot::factory()->count(2)->create([
            'requester_id' => $requester->id,
            'form_id' => $form->id,
        ]);

        $response = $this->getJson(route('api.requestFormPivot.index', [
            'per_page' => 'all',
            'filter_by_parent_column' => 'requester_id',
            'filter_by_parent_id' => $requester->id,
        ]));

        $response->assertOk()
            ->assertJsonStructure(['data']);

        $this->assertCount(2, $response->json('data'));
    }
}
