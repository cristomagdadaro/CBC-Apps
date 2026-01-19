<?php

namespace Tests\Feature\EventAttendance;

use App\Enums\Subform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventFormRequirementsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_event_form_and_attach_requirements(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $payload = [
            'title' => 'National Biotechnology Week 2026',
            'description' => 'Annual event for biotech updates.',
            'details' => 'Detailed agenda for the event.',
            'date_from' => Carbon::now()->addDay()->format('Y-m-d'),
            'date_to' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'time_from' => '09:00:00',
            'time_to' => '17:00:00',
            'venue' => 'Main Hall',
            'is_suspended' => false,
            'max_slots' => 100,
        ];

        $createResponse = $this->postJson(route('api.form.post'), $payload);

        $createResponse->assertStatus(201);

        $eventId = $createResponse->json('event_id');
        $this->assertNotEmpty($eventId);

        $requirementsPayload = [
            'requirements' => [
                [
                    'form_type' => Subform::PREREGISTRATION->value,
                    'is_required' => true,
                    'config' => [
                        'open_from' => now()->subDay()->toDateTimeString(),
                        'open_to' => now()->addDay()->toDateTimeString(),
                        'attendance_type_required' => true,
                    ],
                ],
                [
                    'form_type' => Subform::REGISTRATION->value,
                    'is_required' => true,
                    'config' => [
                        'open_from' => now()->subDay()->toDateTimeString(),
                        'open_to' => now()->addDay()->toDateTimeString(),
                        'attendance_type_required' => true,
                    ],
                ],
                [
                    'form_type' => Subform::PRETEST->value,
                    'is_required' => false,
                    'config' => [
                        'open_from' => now()->subDay()->toDateTimeString(),
                        'open_to' => now()->addDay()->toDateTimeString(),
                    ],
                ],
                [
                    'form_type' => Subform::POSTTEST->value,
                    'is_required' => false,
                    'config' => [
                        'open_from' => now()->subDay()->toDateTimeString(),
                        'open_to' => now()->addDay()->toDateTimeString(),
                    ],
                ],
                [
                    'form_type' => Subform::FEEDBACK->value,
                    'is_required' => true,
                    'config' => [
                        'open_from' => now()->subDay()->toDateTimeString(),
                        'open_to' => now()->addDay()->toDateTimeString(),
                    ],
                ],
            ],
        ];

        $requirementsResponse = $this->postJson(route('forms.requirements.update', ['event_id' => $eventId]), $requirementsPayload);

        $requirementsResponse->assertOk()
            ->assertJsonStructure(['message', 'requirements']);

        $this->assertDatabaseCount('event_requirements', 5);
        $this->assertDatabaseHas('event_requirements', [
            'event_id' => $eventId,
            'form_type' => Subform::PREREGISTRATION->value,
        ]);

        $showResponse = $this->getJson(route('api.form.show', ['event_id' => $eventId]));

        $showResponse->assertOk()
            ->assertJsonStructure(['requirements']);
    }
}
