<?php

namespace Tests\Feature\EventAttendance;

use App\Enums\Subform;
use App\Models\EventSubform;
use App\Models\EventSubformResponse;
use App\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConditionalLimitFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function createFormAndSubform(array $config = []): EventSubform
    {
        $form = Form::factory()->create([
            'event_id' => '3050',
            'is_suspended' => false,
            'is_expired' => false,
        ]);

        return EventSubform::create([
            'event_id' => $form->event_id,
            'form_type' => Subform::PREREGISTRATION_BIOTECH->value,
            'step_type' => Subform::PREREGISTRATION_BIOTECH->value,
            'step_order' => 1,
            'is_enabled' => true,
            'open_from' => now()->subHour()->toDateTimeString(),
            'open_to' => now()->addHour()->toDateTimeString(),
            'is_required' => true,
            'config' => $config,
        ]);
    }

    private function validPayload(EventSubform $subform, array $overrides = []): array
    {
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

        return array_replace_recursive($payload, $overrides);
    }

    public function test_submission_allowed_when_no_limits_configured(): void
    {
        $subform = $this->createFormAndSubform();
        $payload = $this->validPayload($subform);

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->post(route('api.subform.response.store'), $payload);

        $response->assertStatus(201);
    }

    public function test_submission_blocked_when_limit_reached_for_field(): void
    {
        $subform = $this->createFormAndSubform([
            'limits' => [
                ['field' => 'province_address', 'max' => 1],
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

        $payload = $this->validPayload($subform, [
            'response_data' => [
                'province_address' => 'Cebu',
            ],
        ]);

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->post(route('api.subform.response.store'), $payload);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.limit.0', 'Limit reached for Province Address (max 1)');
    }

    public function test_submission_allowed_for_different_value_under_limit(): void
    {
        $subform = $this->createFormAndSubform([
            'limits' => [
                ['field' => 'province_address', 'max' => 1],
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

        $payload = $this->validPayload($subform, [
            'response_data' => [
                'province_address' => 'Bohol',
            ],
        ]);

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->post(route('api.subform.response.store'), $payload);

        $response->assertStatus(201);
    }

    public function test_submission_allowed_when_limit_field_missing(): void
    {
        $subform = $this->createFormAndSubform([
            'limits' => [
                ['field' => 'city_address', 'max' => 1],
            ],
        ]);

        $payload = $this->validPayload($subform, [
            'response_data' => [
                'city_address' => null,
            ],
        ]);

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->post(route('api.subform.response.store'), $payload);

        $response->assertStatus(201);
    }

    public function test_submission_blocked_when_any_limit_exceeded(): void
    {
        $subform = $this->createFormAndSubform([
            'limits' => [
                ['field' => 'province_address', 'max' => 2],
                ['field' => 'city_address', 'max' => 1],
            ],
        ]);

        EventSubformResponse::create([
            'form_parent_id' => $subform->id,
            'participant_id' => null,
            'subform_type' => $subform->form_type,
            'response_data' => [
                'province_address' => 'Cebu',
                'city_address' => 'Cebu City',
            ],
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $payload = $this->validPayload($subform, [
            'response_data' => [
                'province_address' => 'Cebu',
                'city_address' => 'Cebu City',
            ],
        ]);

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->post(route('api.subform.response.store'), $payload);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.limit.0', 'Limit reached for City Address (max 1)');
    }

    public function test_submission_allowed_when_limits_present_but_under_max(): void
    {
        $subform = $this->createFormAndSubform([
            'limits' => [
                ['field' => 'province_address', 'max' => 2],
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

        $payload = $this->validPayload($subform, [
            'response_data' => [
                'province_address' => 'Cebu',
            ],
        ]);

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->post(route('api.subform.response.store'), $payload);

        $response->assertStatus(201);
    }
}
