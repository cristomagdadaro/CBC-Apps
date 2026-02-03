<?php

namespace Tests\Feature\EventAttendance;

use App\Models\EventSubform;
use App\Models\EventSubformResponse;
use App\Models\Form;
use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventWorkflowApiTest extends TestCase
{
    use RefreshDatabase;

    private function createFormWithSteps(): array
    {
        $form = Form::factory()->create([
            'event_id' => '9999',
            'is_suspended' => false,
            'is_expired' => false,
            'date_from' => now()->addDay()->format('Y-m-d'),
            'date_to' => now()->addDays(2)->format('Y-m-d'),
            'time_from' => '09:00:00',
            'time_to' => '17:00:00',
        ]);

        $openFrom = now()->subHour();
        $openTo = now()->addDay();

        $prereg = EventSubform::factory()->create([
            'event_id' => $form->event_id,
            'form_type' => 'preregistration',
            'step_type' => 'preregistration',
            'step_order' => 1,
            'is_enabled' => true,
            'open_from' => $openFrom,
            'open_to' => $openTo,
            'config' => [
                'open_from' => $openFrom->toDateTimeString(),
                'open_to' => $openTo->toDateTimeString(),
            ],
        ]);

        $registration = EventSubform::factory()->create([
            'event_id' => $form->event_id,
            'form_type' => 'registration',
            'step_type' => 'registration',
            'step_order' => 2,
            'is_enabled' => true,
            'open_from' => $openFrom,
            'open_to' => $openTo,
            'config' => [
                'open_from' => $openFrom->toDateTimeString(),
                'open_to' => $openTo->toDateTimeString(),
            ],
        ]);

        return [$form, $prereg, $registration];
    }

    private function baseResponseData(): array
    {
        return [
            'name' => 'Workflow Tester',
            'email' => 'workflow@example.com',
            'phone' => '09170000001',
            'sex' => 'Female',
            'age' => 25,
            'organization' => 'DA-CBC',
            'designation' => 'Analyst',
            'is_ip' => true,
            'is_pwd' => false,
            'city_address' => 'Tagum',
            'province_address' => 'Davao del Norte',
            'country_address' => 'Philippines',
            'attendance_type' => 'In-person',
            'agreed_tc' => true,
        ];
    }

    public function test_workflow_state_returns_ordered_steps(): void
    {
        [$form] = $this->createFormWithSteps();

        $response = $this->getJson(route('api.event.workflow.state.guest', ['event_id' => $form->event_id]));

        $response->assertOk();

        $steps = $response->json('data.steps');

        $this->assertCount(2, $steps);
        $this->assertEquals('preregistration', $steps[0]['form_type']);
        $this->assertEquals(1, $steps[0]['step_order']);
        $this->assertEquals('available', $steps[0]['status']);
        $this->assertEquals('registration', $steps[1]['form_type']);
        $this->assertEquals(2, $steps[1]['step_order']);
    }

    public function test_cannot_submit_out_of_order_step(): void
    {
        [$form, $prereg, $registration] = $this->createFormWithSteps();

        $participant = Participant::factory()->create();
        $registrationRow = Registration::factory()->create([
            'participant_id' => $participant->id,
            'event_subform_id' => $registration->id,
        ]);

        $payload = [
            'form_parent_id' => $registration->id,
            'subform_type' => 'registration',
            'participant_id' => $registrationRow->id,
            'response_data' => $this->baseResponseData(),
        ];

        $response = $this->postJson(route('api.subform.response.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('step');
    }

    public function test_preregistration_then_registration_is_idempotent(): void
    {
        [$form, $prereg, $registration] = $this->createFormWithSteps();

        $preregPayload = [
            'form_parent_id' => $prereg->id,
            'subform_type' => 'preregistration',
            'response_data' => $this->baseResponseData(),
        ];

        $preregResponse = $this->postJson(route('api.subform.response.store'), $preregPayload);
        $preregResponse->assertStatus(201);

        $participantHash = $preregResponse->json('participant_hash');

        $registrationPayload = [
            'form_parent_id' => $registration->id,
            'subform_type' => 'registration',
            'participant_id' => $participantHash,
            'response_data' => $this->baseResponseData(),
        ];

        $registrationResponse = $this->postJson(route('api.subform.response.store'), $registrationPayload);
        $registrationResponse->assertStatus(201)
            ->assertJsonPath('workflow_status', 'created');

        $duplicateResponse = $this->postJson(route('api.subform.response.store'), $registrationPayload);
        $duplicateResponse->assertStatus(200)
            ->assertJsonPath('workflow_status', 'duplicate');

        $this->assertEquals(1, EventSubformResponse::where('form_parent_id', $registration->id)->count());
    }
}
