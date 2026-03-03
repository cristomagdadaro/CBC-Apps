<?php

namespace Tests\Feature\EventAttendance;

use App\Models\Option;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\WithTestRoles;

class EventWorkflowToggleApiTest extends TestCase
{
    use RefreshDatabase;
    use WithTestRoles;

    public function test_workflow_toggles_endpoint_returns_default_values_when_options_are_missing(): void
    {
        $response = $this->getJson(route('api.options.workflow-toggles'));

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.event_workflow_enabled', true)
            ->assertJsonPath('data.participant_workflow_enabled', true)
            ->assertJsonPath('data.participant_verification_enabled', true);
    }

    public function test_admin_can_update_workflow_toggles_via_api(): void
    {
        $admin = $this->createAdminUser();

        $payload = [
            'event_workflow_enabled' => false,
            'participant_workflow_enabled' => false,
            'participant_verification_enabled' => false,
        ];

        $response = $this->actingAs($admin)
            ->putJson(route('api.options.workflow-toggles.update'), $payload);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.event_workflow_enabled', false)
            ->assertJsonPath('data.participant_workflow_enabled', false)
            ->assertJsonPath('data.participant_verification_enabled', false);

        $this->assertDatabaseHas('options', [
            'key' => 'forms_event_workflow_enabled',
            'value' => 'false',
            'type' => 'boolean',
            'group' => 'forms',
        ]);

        $this->assertDatabaseHas('options', [
            'key' => 'forms_participant_workflow_enabled',
            'value' => 'false',
            'type' => 'boolean',
            'group' => 'forms',
        ]);

        $this->assertDatabaseHas('options', [
            'key' => 'forms_participant_verification_enabled',
            'value' => 'false',
            'type' => 'boolean',
            'group' => 'forms',
        ]);
    }

    public function test_guest_participant_lookup_returns_feature_disabled_when_verification_is_off(): void
    {
        Option::factory()->create([
            'key' => 'forms_participant_verification_enabled',
            'value' => 'false',
            'type' => 'boolean',
            'group' => 'forms',
            'label' => 'Participant Verification Enabled',
            'description' => 'Feature toggle',
        ]);

        $response = $this->getJson('/api/guest/forms/event/0504/participant-lookup?email=existing@example.test');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.feature_disabled', true)
            ->assertJsonPath('data.found', false)
            ->assertJsonPath('data.profile_found', false);
    }
}
