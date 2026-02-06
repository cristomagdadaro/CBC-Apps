<?php

namespace Tests\Feature\EventAttendance;

use App\Enums\Subform;
use App\Models\EventSubform;
use App\Models\EventSubformResponse;
use App\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuizbeePreregistrationTest extends TestCase
{
    use RefreshDatabase;

    protected $seeder = \Database\Seeders\DatabaseSeeder::class;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure Cebu City/Cebu exist for validation
        \Illuminate\Support\Facades\DB::table('loc_cities')->insertOrIgnore([
            'id' => 1,
            'city' => 'Cebu City',
            'province' => 'Cebu',
            'region' => 'VII',
        ]);
    }

    public function test_preregistration_quizbee_submission_stores_file_and_response(): void
    {
        Storage::fake('public');

        $form = Form::factory()->create([
            'event_id' => '1546',
            'is_suspended' => false,
            'is_expired' => false,
        ]);

        $subform = EventSubform::create([
            'event_id' => $form->event_id,
            'form_type' => Subform::PREREGISTRATION_QUIZBEE->value,
            'step_type' => Subform::PREREGISTRATION_QUIZBEE->value,
            'step_order' => 1,
            'is_enabled' => true,
            'open_from' => now()->subHour()->toDateTimeString(),
            'open_to' => now()->addHour()->toDateTimeString(),
            'is_required' => true,
        ]);

        $payload = [
            'subform_type' => Subform::PREREGISTRATION_QUIZBEE->value,
            'form_parent_id' => $subform->id,
            'response_data' => [
                'organization' => 'Test High School',
                'city_address' => 'Cebu City',
                'province_address' => 'Cebu',
                'team_name' => 'Team Alpha',
                'participant_1_name' => 'Student One',
                'participant_1_sex' => 'Male',
                'participant_1_gradelevel' => 'Grade 11',
                'participant_2_name' => 'Student Two',
                'participant_2_sex' => 'Female',
                'participant_2_gradelevel' => 'Grade 12',
                'proof_of_enrollment' => UploadedFile::fake()->create('proof.pdf', 200, 'application/pdf'),
                'coach_name' => 'Coach Name',
                'coach_email' => 'coach@example.com',
                'coach_phone' => '09123456789',
                'agreed_tc' => true,
            ],
        ];

        $workflow = app(\App\Services\EventWorkflowService::class);
        $reflection = new \ReflectionClass($workflow);
        $method = $reflection->getMethod('canStartWithoutParticipant');
        $method->setAccessible(true);
        $canStart = $method->invoke($workflow, $subform);
        $this->assertTrue($canStart, 'Quizbee preregistration should allow guest submissions.');

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->post(route('api.subform.response.store'), $payload);

        $response->assertStatus(201);

        $saved = EventSubformResponse::query()->first();
        $this->assertNotNull($saved);
        $this->assertEquals(Subform::PREREGISTRATION_QUIZBEE->value, $saved->subform_type);
        $this->assertArrayHasKey('proof_of_enrollment', $saved->response_data);

        $storedPath = $saved->response_data['proof_of_enrollment'];
        $this->assertNotEmpty($storedPath);
        Storage::disk('public')->assertExists($storedPath);
    }
}
