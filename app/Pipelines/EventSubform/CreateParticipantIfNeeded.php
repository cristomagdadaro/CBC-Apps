<?php

namespace App\Pipelines\EventSubform;

use App\Enums\Subform;
use App\Models\EventRequirement;
use App\Models\Participant;
use App\Models\Registration;
use Closure;
use Illuminate\Support\Arr;

class CreateParticipantIfNeeded
{
    public function handle(array $context, Closure $next): mixed
    {
        $validated = $context['validated'];

        if (in_array($validated['subform_type'], [Subform::PREREGISTRATION->value, Subform::PREREGISTRATION_BIOTECH->value, Subform::REGISTRATION->value], true)) {
            $participantPayload = Arr::only($validated['response_data'], (new Participant())->getFillable());
            $participant = Participant::factory()->create($participantPayload);

            $requirement = EventRequirement::select('id')->find($validated['form_parent_id']);
            $eventId = $requirement?->id ?? $validated['form_parent_id'];

            $registration = Registration::factory()->create([
                'event_id' => $eventId,
                'participant_id' => $participant->id,
                'attendance_type' => Arr::get($validated['response_data'], 'attendance_type'),
            ]);

            $validated['participant_id'] = $registration->id;
            $context['participant'] = $participant;
            $context['registration'] = $registration;
            $context['validated'] = $validated;
        }

        return $next($context);
    }
}
