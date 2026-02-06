<?php

namespace Tests\Feature\EventAttendance;

use App\Enums\Subform;
use App\Models\EventSubform;
use App\Models\EventSubformResponse;
use App\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConditionalLimitTest extends TestCase
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

    public function test_conditional_limit_blocks_over_limit_by_field(): void
    {
        $form = Form::factory()->create([
            'event_id' => '2040',
            'is_suspended' => false,
            'is_expired' => false,
        ]);

        $subform = EventSubform::create([
            'event_id' => $form->event_id,
            'form_type' => Subform::PREREGISTRATION_BIOTECH->value,
            'step_type' => Subform::PREREGISTRATION_BIOTECH->value,
            'step_order' => 1,
            'is_enabled' => true,
            'open_from' => now()->subHour()->toDateTimeString(),
            'open_to' => now()->addHour()->toDateTimeString(),
            'is_required' => true,
            'config' => [
                'limits' => [
                    ['field' => 'province_address', 'max' => 1],
                ],
            ],
        ]);

        EventSubformResponse::create([
            'form_parent_id' => $subform->id,
            'participant_id' => null,
            'subform_type' => $subform->form_type,
            'response_data' => [
                'province_address' => 'Cebu',
            ],
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $payload = [
            'subform_type' => $subform->form_type,
            'form_parent_id' => $subform->id,
            'response_data' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '09123456789',
                'sex' => 'Male',
                'age' => 18,
                'organization' => 'Test School',
                'designation' => 'Student',
                'is_ip' => false,
                'is_pwd' => false,
                'city_address' => 'Cebu City',
                'province_address' => 'Cebu',
                'country_address' => 'PH',
                'attendance_type' => 'Online',
                'join_quiz_bee' => true,
                'agreed_tc' => true,
            ],
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->post(route('api.subform.response.store'), $payload);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.limit.0', 'Limit reached for Province Address (max 1)');
    }
}
