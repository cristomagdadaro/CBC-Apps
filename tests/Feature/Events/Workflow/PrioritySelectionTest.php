<?php

namespace Tests\Feature\Events\Workflow;

use App\Models\EventSubform;
use App\Models\Form;
use App\Services\EventWorkflowService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrioritySelectionTest extends TestCase
{
    use RefreshDatabase;

    protected EventWorkflowService $workflowService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workflowService = app(EventWorkflowService::class);
    }

    public function test_current_step_prefers_active_registration_over_other_available_steps(): void
    {
        Form::factory()->create(['event_id' => '0504']);

        EventSubform::factory()->create([
            'event_id' => '0504',
            'form_type' => 'preregistration',
            'step_type' => 'preregistration',
            'step_order' => 1,
            'is_enabled' => true,
            'open_from' => Carbon::now()->subHour()->toDateTimeString(),
            'open_to' => Carbon::now()->addHours(2)->toDateTimeString(),
        ]);

        EventSubform::factory()->create([
            'event_id' => '0504',
            'form_type' => 'registration_day1',
            'step_type' => 'registration',
            'step_order' => 2,
            'is_enabled' => true,
            'open_from' => Carbon::now()->subHour()->toDateTimeString(),
            'open_to' => Carbon::now()->addHour()->toDateTimeString(),
        ]);

        $state = $this->workflowService->getWorkflowState('0504', null);

        $this->assertNotNull($state['current_step_id']);

        $current = collect($state['steps'])->firstWhere('id', $state['current_step_id']);
        $this->assertEquals('registration', $current['step_type']);
    }

    public function test_current_step_prefers_latest_open_active_registration_when_multiple_are_available(): void
    {
        Form::factory()->create(['event_id' => '0505']);

        $earlyRegistration = EventSubform::factory()->create([
            'event_id' => '0505',
            'form_type' => 'registration_day1',
            'step_type' => 'registration',
            'step_order' => 2,
            'is_enabled' => true,
            'open_from' => Carbon::now()->subHours(4)->toDateTimeString(),
            'open_to' => Carbon::now()->addHours(2)->toDateTimeString(),
        ]);

        $latestRegistration = EventSubform::factory()->create([
            'event_id' => '0505',
            'form_type' => 'registration_day2',
            'step_type' => 'registration',
            'step_order' => 3,
            'is_enabled' => true,
            'open_from' => Carbon::now()->subMinutes(20)->toDateTimeString(),
            'open_to' => Carbon::now()->addHours(2)->toDateTimeString(),
        ]);

        $state = $this->workflowService->getWorkflowState('0505', null);

        $this->assertEquals($latestRegistration->id, $state['current_step_id']);
        $this->assertNotEquals($earlyRegistration->id, $state['current_step_id']);
    }

    public function test_current_step_is_null_when_no_available_window_exists(): void
    {
        Form::factory()->create(['event_id' => '0506']);

        EventSubform::factory()->create([
            'event_id' => '0506',
            'form_type' => 'registration_day1',
            'step_type' => 'registration',
            'step_order' => 1,
            'is_enabled' => true,
            'open_from' => Carbon::now()->addDay()->toDateTimeString(),
            'open_to' => Carbon::now()->addDays(2)->toDateTimeString(),
        ]);

        $state = $this->workflowService->getWorkflowState('0506', null);

        $this->assertNull($state['current_step_id']);
    }
}
