<?php

namespace Tests\Feature\Events;

use App\Models\EventSubform;
use App\Models\Form;
use App\Models\Participant;
use App\Models\Registration;
use App\Repositories\FormRepo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FormRepoParticipantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_participants_by_event_id_uses_event_subform_ids_and_legacy_event_id_matches(): void
    {
        $repo = app(FormRepo::class);

        Form::factory()->create(['event_id' => '0504']);
        Form::factory()->create(['event_id' => '9999']);

        $participantFromSubform = Participant::factory()->create();
        $participantFromLegacyId = Participant::factory()->create();
        $participantFromOtherEvent = Participant::factory()->create();

        $eventSubform = EventSubform::factory()->create([
            'event_id' => '0504',
        ]);

        $otherEventSubform = EventSubform::factory()->create([
            'event_id' => '9999',
        ]);

        Registration::create([
            'id' => (string) Str::uuid(),
            'event_subform_id' => $eventSubform->id,
            'participant_id' => $participantFromSubform->id,
            'attendance_type' => null,
        ]);

        Registration::create([
            'id' => (string) Str::uuid(),
            'event_subform_id' => '0504',
            'participant_id' => $participantFromLegacyId->id,
            'attendance_type' => null,
        ]);

        Registration::create([
            'id' => (string) Str::uuid(),
            'event_subform_id' => $otherEventSubform->id,
            'participant_id' => $participantFromOtherEvent->id,
            'attendance_type' => null,
        ]);

        $participants = $repo->getParticipantsByEventId('0504');

        $this->assertEqualsCanonicalizing([
            $participantFromSubform->id,
            $participantFromLegacyId->id,
        ], $participants->pluck('id')->all());
    }
}
