<?php

namespace Tests\Feature\Events\Workflow;

use App\Models\EventSubform;
use App\Models\Form;
use App\Models\ParticipantStepState;
use App\Services\EventWorkflowService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StateManagementTest extends TestCase
{
    use RefreshDatabase;

    protected EventWorkflowService $workflowService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workflowService = app(EventWorkflowService::class);
    }

    /**
     * Test: When a form is completed and the next step hasn't opened yet,
     * the workflow should return 'not_yet_open' status, not 'completed'.
     */
    public function test_next_step_shows_not_yet_open_when_future_dates_apply()
    {
        // Create form
        $form = Form::factory()->create([
            'event_id' => '1546',
        ]);

        // Step 1: Pre-registration (already closed)
        $preregistration = EventSubform::factory()->create([
            'event_id' => '1546',
            'form_type' => 'preregistration',
            'step_order' => 1,
            'is_enabled' => true,
            'open_from' => Carbon::now()->subHours(2),
            'open_to' => Carbon::now()->subHours(1),
        ]);

        // Step 2: Registration (not yet open)
        $registration = EventSubform::factory()->create([
            'event_id' => '1546',
            'form_type' => 'registration',
            'step_order' => 2,
            'is_enabled' => true,
            'open_from' => Carbon::now()->addHours(1),
            'open_to' => Carbon::now()->addHours(2),
        ]);

        // Step 3: Feedback (future)
        $feedback = EventSubform::factory()->create([
            'event_id' => '1546',
            'form_type' => 'feedback',
            'step_order' => 3,
            'is_enabled' => true,
            'open_from' => Carbon::now()->addHours(3),
            'open_to' => Carbon::now()->addHours(4),
        ]);

        // Simulate participant completed preregistration
        $participantId = 'test-participant-hash-123';
        ParticipantStepState::create([
            'event_id' => '1546',
            'participant_id' => $participantId,
            'event_subform_id' => $preregistration->id,
            'status' => ParticipantStepState::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        // Get workflow state
        $state = $this->workflowService->getWorkflowState('1546', $participantId);

        // Verify steps
        $this->assertCount(3, $state['steps']);

        // Step 1 should be EXPIRED (time window has passed, so it shows expired status)
        $step1 = collect($state['steps'])->firstWhere('form_type', 'preregistration');
        $this->assertEquals(ParticipantStepState::STATUS_EXPIRED, $step1['status']);

        // Step 2 should be NOT_YET_OPEN (not AVAILABLE, because it hasn't opened yet)
        $step2 = collect($state['steps'])->firstWhere('form_type', 'registration');
        $this->assertEquals(ParticipantStepState::STATUS_NOT_YET_OPEN, $step2['status']);
        $this->assertNotNull($step2['open_from']);

        // Step 3 should be NOT_YET_OPEN
        $step3 = collect($state['steps'])->firstWhere('form_type', 'feedback');
        $this->assertEquals(ParticipantStepState::STATUS_NOT_YET_OPEN, $step3['status']);
        $this->assertNotNull($step3['open_from']);
    }

    /**
     * Test: After next step opens, it should show as AVAILABLE (assuming previous is completed)
     */
    public function test_step_becomes_available_when_open_time_arrives()
    {
        // Create form
        $form = Form::factory()->create([
            'event_id' => '1547',
        ]);

        // Step 1: Pre-registration (already closed)
        $preregistration = EventSubform::factory()->create([
            'event_id' => '1547',
            'form_type' => 'preregistration',
            'step_order' => 1,
            'is_enabled' => true,
            'open_from' => Carbon::now()->subHours(2),
            'open_to' => Carbon::now()->subHours(1),
        ]);

        // Step 2: Registration (currently open)
        $registration = EventSubform::factory()->create([
            'event_id' => '1547',
            'form_type' => 'registration',
            'step_order' => 2,
            'is_enabled' => true,
            'open_from' => Carbon::now()->subMinutes(30),
            'open_to' => Carbon::now()->addMinutes(30),
        ]);

        // Simulate participant completed preregistration
        $participantId = 'test-participant-hash-456';
        ParticipantStepState::create([
            'event_id' => '1547',
            'participant_id' => $participantId,
            'event_subform_id' => $preregistration->id,
            'status' => ParticipantStepState::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        // Get workflow state
        $state = $this->workflowService->getWorkflowState('1547', $participantId);

        // Step 2 should be AVAILABLE now
        $step2 = collect($state['steps'])->firstWhere('form_type', 'registration');
        $this->assertEquals(ParticipantStepState::STATUS_AVAILABLE, $step2['status']);
    }

    /**
     * Test: Expired step shows expired status
     */
    public function test_expired_step_shows_expired_status()
    {
        // Create form
        $form = Form::factory()->create([
            'event_id' => '1548',
        ]);

        // Step: Registration (already expired)
        $registration = EventSubform::factory()->create([
            'event_id' => '1548',
            'form_type' => 'registration',
            'step_order' => 1,
            'is_enabled' => true,
            'open_from' => Carbon::now()->subHours(2),
            'open_to' => Carbon::now()->subMinutes(1),
        ]);

        // Get workflow state without participant
        $state = $this->workflowService->getWorkflowState('1548', null);

        $step = collect($state['steps'])->firstWhere('form_type', 'registration');
        $this->assertEquals(ParticipantStepState::STATUS_EXPIRED, $step['status']);
    }

    /**
     * Test: First step not yet open shows not_yet_open status
     */
    public function test_first_step_not_yet_open_shows_not_yet_open_status()
    {
        // Create form
        $form = Form::factory()->create([
            'event_id' => '1549',
        ]);

        // Step 1: Pre-registration (not yet open)
        $preregistration = EventSubform::factory()->create([
            'event_id' => '1549',
            'form_type' => 'preregistration',
            'step_order' => 1,
            'is_enabled' => true,
            'open_from' => Carbon::now()->addHours(2),
            'open_to' => Carbon::now()->addHours(3),
        ]);

        // Get workflow state without participant
        $state = $this->workflowService->getWorkflowState('1549', null);

        $step = collect($state['steps'])->firstWhere('form_type', 'preregistration');
        $this->assertEquals(ParticipantStepState::STATUS_NOT_YET_OPEN, $step['status']);
        $this->assertNotNull($step['open_from']);
    }
}
