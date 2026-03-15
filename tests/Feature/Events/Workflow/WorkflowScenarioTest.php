<?php

namespace Tests\Feature\Events\Workflow;

use App\Models\EventSubform;
use App\Models\Form;
use App\Models\ParticipantStepState;
use App\Services\EventWorkflowService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowScenarioTest extends TestCase
{
    use RefreshDatabase;

    protected EventWorkflowService $workflowService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workflowService = app(EventWorkflowService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    /**
     * Test the exact scenario from the issue:
     * Form 1546 with pre-registration, registration, and feedback.
     * After completing pre-registration, registration is not yet open.
     * Should show "This form will be available from [date]" NOT "You have already completed required forms"
     */
    public function test_form_1546_scenario_correct_message_for_not_yet_open_step()
    {
        Carbon::setTestNow('2026-02-05 10:00:00');
        // Create form 1546
        $form = Form::factory()->create([
            'event_id' => '1546',
        ]);

        // Step 1: Pre-registration
        // open_from: 2026-02-04 11:48:00
        // open_to: 2026-02-05 11:48:00
        $preregistration = EventSubform::factory()->create([
            'event_id' => '1546',
            'form_type' => 'preregistration',
            'step_type' => 'preregistration',
            'step_order' => 1,
            'is_enabled' => true,
            'is_required' => true,
            'max_slots' => 1,
            'open_from' => '2026-02-04 11:48:00',
            'open_to' => '2026-02-05 11:48:00',
        ]);

        // Step 2: Registration (NOT YET OPEN)
        // open_from: 2026-02-05 11:48:00
        // open_to: 2026-02-06 11:48:00
        $registration = EventSubform::factory()->create([
            'event_id' => '1546',
            'form_type' => 'registration',
            'step_type' => 'registration',
            'step_order' => 2,
            'is_enabled' => true,
            'is_required' => true,
            'max_slots' => 1,
            'open_from' => '2026-02-05 11:48:00',
            'open_to' => '2026-02-06 11:48:00',
        ]);

        // Step 3: Feedback (FUTURE)
        // open_from: 2026-02-06 11:48:00
        // open_to: 2026-02-07 11:48:00
        $feedback = EventSubform::factory()->create([
            'event_id' => '1546',
            'form_type' => 'feedback',
            'step_type' => 'feedback',
            'step_order' => 3,
            'is_enabled' => true,
            'is_required' => true,
            'max_slots' => 2,
            'open_from' => '2026-02-06 11:48:00',
            'open_to' => '2026-02-07 11:48:00',
        ]);

        // Simulate: User completed pre-registration
        $participantHash = 'user-abc123-hash';
        ParticipantStepState::create([
            'event_id' => '1546',
            'participant_id' => $participantHash,
            'event_subform_id' => $preregistration->id,
            'status' => ParticipantStepState::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        // Get workflow state as the guest user
        $state = $this->workflowService->getWorkflowState('1546', $participantHash);

        $steps = collect($state['steps']);

        // Verify Step 1: Pre-registration should be either EXPIRED or COMPLETED
        $step1 = $steps->firstWhere('form_type', 'preregistration');
        $this->assertTrue(
            in_array($step1['status'], [
                ParticipantStepState::STATUS_COMPLETED,
                ParticipantStepState::STATUS_EXPIRED,
            ]),
            "Step 1 status should be COMPLETED or EXPIRED, got: {$step1['status']}"
        );

        // THE KEY ASSERTION: Step 2 should be NOT_YET_OPEN (NOT 'completed'!)
        $step2 = $steps->firstWhere('form_type', 'registration');
        $this->assertEquals(ParticipantStepState::STATUS_NOT_YET_OPEN, $step2['status'],
            'Registration step should show NOT_YET_OPEN status, not COMPLETED');
        $this->assertNotNull($step2['open_from']);
        $this->assertEquals('2026-02-05 11:48:00', $step2['open_from']);

        // Step 3: Feedback should also be NOT_YET_OPEN
        $step3 = $steps->firstWhere('form_type', 'feedback');
        $this->assertEquals(ParticipantStepState::STATUS_NOT_YET_OPEN, $step3['status']);
        $this->assertEquals('2026-02-06 11:48:00', $step3['open_from']);

        // Verify the message would be correct
        // In the frontend, getStepMessage(step2) would return:
        // "This form will be available from Feb 5, 2026, 11:48 AM"
        $this->assertNotEquals(ParticipantStepState::STATUS_COMPLETED, $step2['status'],
            'Should NOT say "You have already completed required forms"');
    }
}
