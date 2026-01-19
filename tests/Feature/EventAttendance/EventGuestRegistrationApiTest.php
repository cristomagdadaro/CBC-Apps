<?php

namespace Tests\Feature\EventAttendance;

use App\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventGuestRegistrationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_registration_creates_participant_and_registration(): void
    {
        $form = Form::factory()->create([
            'is_suspended' => false,
            'is_expired' => false,
            'max_slots' => 100,
            'date_from' => now()->addDay()->format('Y-m-d'),
            'date_to' => now()->addDays(2)->format('Y-m-d'),
            'time_from' => '09:00:00',
            'time_to' => '17:00:00',
        ]);

        $payload = [
            'name' => 'Guest Participant',
            'email' => 'guest@example.com',
            'phone' => '09170000002',
            'sex' => 'Female',
            'age' => 25,
            'organization' => 'DA-CBC',
            'designation' => 'Analyst',
            'is_ip' => true,
            'is_pwd' => false,
            'city_address' => 'Tagum',
            'province_address' => 'Davao del Norte',
            'country_address' => 'Philippines',
            'agreed_tc' => true,
            'attendance_type' => 'In-person',
            'event_id' => $form->event_id,
        ];

        $response = $this->postJson(route('api.form.registration.post', ['event_id' => $form->event_id]), $payload);

        $response->assertStatus(201)
            ->assertJsonStructure(['status', 'participant_hash', 'participant', 'event_id']);

        $this->assertDatabaseHas('participants', [
            'email' => 'guest@example.com',
            'name' => 'Guest Participant',
        ]);

        $this->assertDatabaseHas('registrations', [
            'event_id' => $form->event_id,
            'attendance_type' => 'In-person',
        ]);
    }
}
