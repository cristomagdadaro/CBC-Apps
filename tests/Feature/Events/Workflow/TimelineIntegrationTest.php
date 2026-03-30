<?php

namespace Tests\Feature\Events\Workflow;

use App\Models\EventSubform;
use App\Models\EventSubformResponse;
use App\Models\Form;
use App\Models\Participant;
use App\Models\ParticipantStepState;
use App\Models\Registration;
use App\Services\EventWorkflowService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TimelineIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected EventWorkflowService $workflowService;
    protected EventSubform $prereg;
    protected EventSubform $registrationDay1;
    protected EventSubform $registrationDay2;
    protected EventSubform $feedback;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workflowService = app(EventWorkflowService::class);
        $this->seedEvent0504Timeline();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    protected function seedEvent0504Timeline(): void
    {
        Form::factory()->create([
            'event_id' => '0504',
            'date_from' => '2026-03-04',
            'date_to' => '2026-03-05',
            'time_from' => '00:00:00',
            'time_to' => '23:59:59',
        ]);

        $this->prereg = EventSubform::factory()->create([
            'event_id' => '0504',
            'form_type' => 'preregistration',
            'step_type' => 'preregistration',
            'step_order' => 1,
            'is_enabled' => true,
            'is_required' => true,
            'open_from' => '2026-03-01 07:00:00',
            'open_to' => '2026-03-04 00:00:00',
        ]);

        $this->registrationDay1 = EventSubform::factory()->create([
            'event_id' => '0504',
            'form_type' => 'registration_day1',
            'step_type' => 'registration',
            'step_order' => 2,
            'is_enabled' => true,
            'is_required' => true,
            'open_from' => '2026-03-04 08:00:00',
            'open_to' => '2026-03-04 12:00:00',
        ]);

        $this->registrationDay2 = EventSubform::factory()->create([
            'event_id' => '0504',
            'form_type' => 'registration_day2',
            'step_type' => 'registration',
            'step_order' => 3,
            'is_enabled' => true,
            'is_required' => true,
            'open_from' => '2026-03-05 08:00:00',
            'open_to' => '2026-03-05 12:00:00',
        ]);

        $this->feedback = EventSubform::factory()->create([
            'event_id' => '0504',
            'form_type' => 'feedback',
            'step_type' => 'feedback',
            'step_order' => 4,
            'is_enabled' => true,
            'is_required' => true,
            'open_from' => '2026-03-05 13:00:00',
            'open_to' => '2026-03-07 22:00:00',
        ]);
    }

    public function test_march_2_prefers_preregistration(): void
    {
        Carbon::setTestNow('2026-03-02 10:00:00');

        $state = $this->workflowService->getWorkflowState('0504', null);

        $current = collect($state['steps'])->firstWhere('id', $state['current_step_id']);
        $this->assertEquals('preregistration', $current['form_type']);
        $this->assertEquals('available', $current['status']);
    }

    public function test_march_4_day1_window_prefers_registration_day1(): void
    {
        Carbon::setTestNow('2026-03-04 09:00:00');

        $state = $this->workflowService->getWorkflowState('0504', null);

        $current = collect($state['steps'])->firstWhere('id', $state['current_step_id']);
        $this->assertEquals($this->registrationDay1->id, $current['id']);
        $this->assertEquals('registration', $current['step_type']);
    }

    public function test_march_5_day2_window_prefers_registration_day2(): void
    {
        Carbon::setTestNow('2026-03-05 09:00:00');

        $state = $this->workflowService->getWorkflowState('0504', null);

        $current = collect($state['steps'])->firstWhere('id', $state['current_step_id']);
        $this->assertEquals($this->registrationDay2->id, $current['id']);
        $this->assertEquals('registration', $current['step_type']);
    }

    public function test_march_5_afternoon_prefers_feedback(): void
    {
        Carbon::setTestNow('2026-03-05 14:00:00');

        $state = $this->workflowService->getWorkflowState('0504', null);

        $current = collect($state['steps'])->firstWhere('id', $state['current_step_id']);
        $this->assertEquals($this->feedback->id, $current['id']);
        $this->assertEquals('feedback', $current['step_type']);
    }

    public function test_outside_windows_has_no_current_step(): void
    {
        Carbon::setTestNow('2026-03-08 09:00:00');

        $state = $this->workflowService->getWorkflowState('0504', null);

        $this->assertNull($state['current_step_id']);
    }

    public function test_cross_device_identity_lookup_returns_generic_verification_response(): void
    {
        Carbon::setTestNow('2026-03-04 09:30:00');

        $participant = Participant::factory()->create([
            'email' => 'user1@example.test',
        ]);

        $response = $this->getJson('/api/guest/forms/event/0504/participant-lookup?email=' . urlencode('user1@example.test'));

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.verification_required', true)
            ->assertJsonPath('data.message', 'If this email is eligible, a verification link will be sent.')
            ->assertJsonMissingPath('data.participant_hash');

        $this->assertDatabaseCount('registrations', 0);
        $this->assertDatabaseHas('participants', [
            'id' => $participant->id,
            'email' => 'user1@example.test',
        ]);
    }

    public function test_identity_lookup_keeps_existing_registration_but_stays_generic(): void
    {
        Carbon::setTestNow('2026-03-04 09:30:00');

        $participant = Participant::factory()->create([
            'email' => 'existing@example.test',
        ]);

        $registration = Registration::create([
            'id' => (string) Str::uuid(),
            'event_subform_id' => '0504',
            'participant_id' => $participant->id,
            'attendance_type' => null,
        ]);

        $response = $this->getJson('/api/guest/forms/event/0504/participant-lookup?email=' . urlencode('existing@example.test'));

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.verification_required', true)
            ->assertJsonPath('data.message', 'If this email is eligible, a verification link will be sent.')
            ->assertJsonMissingPath('data.participant_hash');

        $this->assertDatabaseHas('registrations', [
            'id' => $registration->id,
            'participant_id' => $participant->id,
            'event_subform_id' => '0504',
        ]);
    }

    public function test_day2_only_attendee_with_prereg_completion_can_access_day2_registration(): void
    {
        Carbon::setTestNow('2026-03-05 09:00:00');

        $participantHash = (string) Str::uuid();

        ParticipantStepState::create([
            'event_id' => '0504',
            'participant_id' => $participantHash,
            'event_subform_id' => $this->prereg->id,
            'status' => ParticipantStepState::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $state = $this->workflowService->getWorkflowState('0504', $participantHash);

        $day2 = collect($state['steps'])->firstWhere('id', $this->registrationDay2->id);
        $this->assertEquals('available', $day2['status']);
    }

    public function test_feedback_is_locked_without_registration_completion_for_identified_participant(): void
    {
        Carbon::setTestNow('2026-03-05 14:00:00');

        $participantHash = (string) Str::uuid();

        ParticipantStepState::create([
            'event_id' => '0504',
            'participant_id' => $participantHash,
            'event_subform_id' => $this->prereg->id,
            'status' => ParticipantStepState::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $state = $this->workflowService->getWorkflowState('0504', $participantHash);

        $feedback = collect($state['steps'])->firstWhere('id', $this->feedback->id);
        $this->assertEquals('locked', $feedback['status']);
    }

    public function test_duplicate_registration_submission_returns_duplicate_workflow_status(): void
    {
        Carbon::setTestNow('2026-03-04 09:00:00');

        $participantHash = (string) Str::uuid();

        Registration::create([
            'id' => $participantHash,
            'event_subform_id' => '0504',
            'participant_id' => Participant::factory()->create()->id,
            'attendance_type' => null,
        ]);

        ParticipantStepState::create([
            'event_id' => '0504',
            'participant_id' => $participantHash,
            'event_subform_id' => $this->prereg->id,
            'status' => ParticipantStepState::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        EventSubformResponse::create([
            'id' => (string) Str::uuid(),
            'form_parent_id' => $this->registrationDay1->id,
            'subform_type' => 'registration_day1',
            'participant_id' => $participantHash,
            'response_data' => ['name' => 'User 1'],
            'status' => 'submitted',
            'completion_hash' => 'hash-1',
        ]);

        $payload = [
            'subform_type' => 'registration_day1',
            'form_parent_id' => $this->registrationDay1->id,
            'participant_id' => $participantHash,
            'response_data' => ['name' => 'User 1'],
        ];

        $result = $this->workflowService->submit($payload);

        $this->assertEquals('duplicate', $result['status']);
    }
}
