<?php

namespace App\Repositories;

use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ParticipantRepo extends AbstractRepoService
{
    public function __construct(Participant $model)
    {
        parent::__construct($model);
    }

    public function createWithRegistration(array $validated): array
    {
        return DB::transaction(function () use ($validated) {
            $participant = $this->create($validated);
            $eventId = $validated['event_id'] ?? null;

            do {
                $temp = Str::uuid()->toString();
            } while (Registration::where('id', $temp)->exists());

            Registration::factory()->create([
                'id' => $temp,
                'event_subform_id' => $eventId,
                'participant_id' => $participant ? $participant->id : null,
                'attendance_type' => $validated['attendance_type'] ?? null,
            ]);

            return [
                'participant' => $participant,
                'participant_hash' => $temp,
                'event_subform_id' => $eventId,
            ];
        });
    }
}
