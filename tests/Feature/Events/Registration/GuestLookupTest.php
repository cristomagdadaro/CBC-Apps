<?php

namespace Tests\Feature\Events\Registration;

use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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

    public function test_guest_participant_lookup_returns_generic_response_for_unknown_email(): void
    {
        $response = $this->getJson('/api/guest/forms/event/0504/participant-lookup?email=' . urlencode('missing@example.test'));

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.verification_required', true)
            ->assertJsonPath('data.message', 'If this email is eligible, a verification link will be sent.')
            ->assertJsonMissingPath('data.participant_hash');

        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_guest_participant_lookup_returns_generic_response_without_creating_registration_for_existing_profile(): void
    {
        Participant::factory()->create([
            'email' => 'existing@example.test',
        ]);

        $response = $this->getJson('/api/guest/forms/event/0504/participant-lookup?email=' . urlencode('existing@example.test'));

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.verification_required', true)
            ->assertJsonPath('data.message', 'If this email is eligible, a verification link will be sent.')
            ->assertJsonMissingPath('data.participant_hash');

        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_guest_participant_lookup_keeps_existing_registration_unchanged_while_returning_generic_response(): void
    {
        $participant = Participant::factory()->create([
            'email' => 'registered@example.test',
        ]);

        $registration = Registration::create([
            'id' => (string) Str::uuid(),
            'event_subform_id' => '0504',
            'participant_id' => $participant->id,
            'attendance_type' => null,
        ]);

        $response = $this->getJson('/api/guest/forms/event/0504/participant-lookup?email=' . urlencode('registered@example.test'));

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
