<?php

namespace Tests\Feature\Events\Registration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestLookupTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider invalidEmailProvider
     */
    public function test_guest_participant_lookup_validates_email(string $email): void
    {
        $response = $this->getJson('/api/guest/forms/event/0504/participant-lookup?email=' . urlencode($email));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_guest_participant_lookup_returns_not_found_for_unknown_email(): void
    {
        $response = $this->getJson('/api/guest/forms/event/0504/participant-lookup?email=' . urlencode('missing@example.test'));

        $response->assertStatus(200)
            ->assertJsonPath('data.found', false)
            ->assertJsonPath('data.profile_found', false);
    }

    public function test_guest_participant_lookup_auto_creates_registration_if_profile_exists(): void
    {
        $participant = \App\Models\Participant::factory()->create([
            'email' => 'existing@example.test',
        ]);

        $response = $this->getJson('/api/guest/forms/event/0504/participant-lookup?email=' . urlencode('existing@example.test'));

        $response->assertStatus(200)
            ->assertJsonPath('data.found', true)
            ->assertJsonPath('data.profile_found', true)
            ->assertJsonPath('data.participant.email', 'existing@example.test');

        $participantHash = $response->json('data.participant_hash');

        $this->assertNotEmpty($participantHash);
        $this->assertDatabaseHas('registrations', [
            'id' => $participantHash,
            'participant_id' => $participant->id,
            'event_subform_id' => '0504',
        ]);
    }

    public function test_guest_participant_lookup_returns_existing_registration_hash(): void
    {
        $participant = \App\Models\Participant::factory()->create([
            'email' => 'registered@example.test',
        ]);

        $registration = \App\Models\Registration::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'event_subform_id' => '0504',
            'participant_id' => $participant->id,
            'attendance_type' => null,
        ]);

        $response = $this->getJson('/api/guest/forms/event/0504/participant-lookup?email=' . urlencode('registered@example.test'));

        $response->assertStatus(200)
            ->assertJsonPath('data.found', true)
            ->assertJsonPath('data.participant_hash', $registration->id);
    }

    public static function invalidEmailProvider(): array
    {
        $cases = [];

        $invalid = [
            '',
            'not-an-email',
            '@example.com',
            'user@',
            'user@@example.com',
            'user@ example.com',
            'user example.com',
            'userexample.com',
            'user@.com',
            'plainaddress',
        ];

        foreach ($invalid as $index => $value) {
            $cases['invalid_email_' . $index] = [$value];
        }

        return $cases;
    }
}
