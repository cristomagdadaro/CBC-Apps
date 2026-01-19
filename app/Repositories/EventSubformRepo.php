<?php

namespace App\Repositories;

use App\Enums\Subform;
use App\Models\EventSubformResponse;
use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class EventSubformRepo extends AbstractRepoService
{
    public function __construct(EventSubformResponse $model)
    {
        parent::__construct($model);
    }

    public function createWithOptionalParticipant(array $validated): array
    {
        return DB::transaction(function () use ($validated) {
            $participant = null;
            $registration = null;

            if (in_array($validated['subform_type'], [Subform::PREREGISTRATION->value, Subform::REGISTRATION->value], true)) {
                $participantPayload = Arr::only($validated['response_data'], (new Participant())->getFillable());
                $participant = Participant::factory()->create($participantPayload);

                $registration = Registration::factory()->create([
                    'event_id' => $validated['form_parent_id'],
                    'participant_id' => $participant->id,
                    'attendance_type' => Arr::get($validated['response_data'], 'attendance_type'),
                ]);

                $validated['participant_id'] = $registration->id;
            }

            $subformResponse = $this->create([
                'form_parent_id' => $validated['form_parent_id'],
                'participant_id' => $validated['participant_id'] ?? null,
                'subform_type' => $validated['subform_type'],
                'response_data' => $validated['response_data'],
            ]);

            return [
                'participant' => $participant,
                'registration' => $registration,
                'subformResponse' => $subformResponse,
            ];
        });
    }
}
