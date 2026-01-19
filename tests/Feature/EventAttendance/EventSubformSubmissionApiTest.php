<?php

namespace Tests\Feature\EventAttendance;

use App\Enums\Subform;
use App\Models\EventRequirement;
use App\Models\Form;
use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventSubformSubmissionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_preregistration_and_registration_subforms_create_participants(): void
    {
        $form = Form::factory()->create([
            'is_suspended' => false,
            'is_expired' => false,
            'max_slots' => 50,
            'date_from' => now()->addDay()->format('Y-m-d'),
            'date_to' => now()->addDays(2)->format('Y-m-d'),
            'time_from' => '09:00:00',
            'time_to' => '17:00:00',
        ]);

        EventRequirement::factory()->create(['event_id' => $form->event_id, 'form_type' => Subform::PREREGISTRATION->value]);
        EventRequirement::factory()->create(['event_id' => $form->event_id, 'form_type' => Subform::REGISTRATION->value]);
        EventRequirement::factory()->create(['event_id' => $form->event_id, 'form_type' => Subform::PRETEST->value]);
        EventRequirement::factory()->create(['event_id' => $form->event_id, 'form_type' => Subform::POSTTEST->value]);
        EventRequirement::factory()->create(['event_id' => $form->event_id, 'form_type' => Subform::FEEDBACK->value]);

        $baseResponseData = [
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@example.com',
            'phone' => '09170000001',
            'sex' => 'Male',
            'age' => 30,
            'organization' => 'CBC',
            'designation' => 'Researcher',
            'is_ip' => false,
            'is_pwd' => false,
            'city_address' => 'Davao City',
            'province_address' => 'Davao del Sur',
            'country_address' => 'Philippines',
            'attendance_type' => 'Online',
            'agreed_tc' => true,
        ];

        $preregPayload = [
            'subform_type' => Subform::PREREGISTRATION->value,
            'form_parent_id' => $form->event_id,
            'response_data' => $baseResponseData,
        ];

        $preregResponse = $this->postJson(route('api.subform.response.store'), $preregPayload);
        $preregResponse->assertStatus(201)
            ->assertJsonStructure(['status', 'participant_hash', 'participant', 'event_id', 'data']);

        $this->assertDatabaseHas('event_subform_responses', [
            'form_parent_id' => $form->event_id,
            'subform_type' => Subform::PREREGISTRATION->value,
        ]);

        $registrationPayload = [
            'subform_type' => Subform::REGISTRATION->value,
            'form_parent_id' => $form->event_id,
            'response_data' => array_merge($baseResponseData, [
                'email' => 'maria@example.com',
                'name' => 'Maria Clara',
            ]),
        ];

        $registrationResponse = $this->postJson(route('api.subform.response.store'), $registrationPayload);
        $registrationResponse->assertStatus(201)
            ->assertJsonStructure(['status', 'participant_hash', 'participant', 'event_id', 'data']);

        $this->assertDatabaseHas('event_subform_responses', [
            'form_parent_id' => $form->event_id,
            'subform_type' => Subform::REGISTRATION->value,
        ]);
    }

    public function test_pretest_posttest_and_feedback_submissions(): void
    {
        $form = Form::factory()->create([
            'is_suspended' => false,
            'is_expired' => false,
            'max_slots' => 50,
            'date_from' => now()->addDay()->format('Y-m-d'),
            'date_to' => now()->addDays(2)->format('Y-m-d'),
            'time_from' => '09:00:00',
            'time_to' => '17:00:00',
        ]);

        EventRequirement::factory()->create(['event_id' => $form->event_id, 'form_type' => Subform::PRETEST->value]);
        EventRequirement::factory()->create(['event_id' => $form->event_id, 'form_type' => Subform::POSTTEST->value]);
        EventRequirement::factory()->create(['event_id' => $form->event_id, 'form_type' => Subform::FEEDBACK->value]);

        $pretestParticipant = Participant::factory()->create([
            'agreed_tc' => true,
        ]);

        $pretestRegistration = Registration::factory()->create([
            'event_id' => $form->event_id,
            'participant_id' => $pretestParticipant->id,
            'attendance_type' => 'Online',
        ]);

        $posttestParticipant = Participant::factory()->create([
            'agreed_tc' => true,
        ]);

        $posttestRegistration = Registration::factory()->create([
            'event_id' => $form->event_id,
            'participant_id' => $posttestParticipant->id,
            'attendance_type' => 'Online',
        ]);

        $feedbackParticipant = Participant::factory()->create([
            'agreed_tc' => true,
        ]);

        $feedbackRegistration = Registration::factory()->create([
            'event_id' => $form->event_id,
            'participant_id' => $feedbackParticipant->id,
            'attendance_type' => 'Online',
        ]);

        $pretestPayload = [
            'subform_type' => Subform::PRETEST->value,
            'form_parent_id' => $form->event_id,
            'participant_id' => $pretestRegistration->id,
            'response_data' => [
                'score' => 12,
                'remarks' => 'Pretest done',
            ],
        ];

        $this->postJson(route('api.subform.response.store'), $pretestPayload)
            ->assertStatus(201);

        $posttestPayload = [
            'subform_type' => Subform::POSTTEST->value,
            'form_parent_id' => $form->event_id,
            'participant_id' => $posttestRegistration->id,
            'response_data' => [
                'score' => 18,
                'remarks' => 'Posttest done',
            ],
        ];

        $this->postJson(route('api.subform.response.store'), $posttestPayload)
            ->assertStatus(201);

        $feedbackPayload = [
            'subform_type' => Subform::FEEDBACK->value,
            'form_parent_id' => $form->event_id,
            'participant_id' => $feedbackRegistration->id,
            'response_data' => [
                'clarity_objective' => 4,
                'time_allotment' => 4,
                'attainment_objective' => 5,
                'relevance_usefulness' => 5,
                'overall_quality_content' => 4,
                'overall_quality_resource_persons' => 5,
                'time_management_organization' => 4,
                'support_staff' => 4,
                'overall_quality_activity_admin' => 5,
                'knowledge_gain' => 5,
                'comments_event_coordination' => 'Well organized.',
                'other_topics' => 'Advanced biotech',
                'agreed_tc' => true,
            ],
        ];

        $this->postJson(route('api.subform.response.store'), $feedbackPayload)
            ->assertStatus(201);

        $this->assertDatabaseHas('event_subform_responses', [
            'form_parent_id' => $form->event_id,
            'subform_type' => Subform::PRETEST->value,
        ]);

        $this->assertDatabaseHas('event_subform_responses', [
            'form_parent_id' => $form->event_id,
            'subform_type' => Subform::POSTTEST->value,
        ]);

        $this->assertDatabaseHas('event_subform_responses', [
            'form_parent_id' => $form->event_id,
            'subform_type' => Subform::FEEDBACK->value,
        ]);
    }
}
