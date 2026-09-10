<?php

namespace App\Repositories;

use App\Models\ParticipantStepState;

class ParticipantStepStateRepo extends AbstractRepoService
{
    public function __construct(ParticipantStepState $model)
    {
        parent::__construct($model);
    }

    public function hasCompletedStep(string $registrationId, string $requirementId): bool
    {
        return $this->model
            ->where('event_subform_id', $requirementId)
            ->where('participant_id', $registrationId)
            ->where('status', ParticipantStepState::STATUS_COMPLETED)
            ->exists();
    }

    public function markLegacyAsCompleted(string $eventId, string $registrationId, string $requirementId): void
    {
        $this->model->updateOrCreate(
            [
                'event_id' => $eventId,
                'participant_id' => $registrationId,
                'event_subform_id' => $requirementId,
            ],
            [
                'status' => ParticipantStepState::STATUS_COMPLETED,
                'completed_at' => now(),
                'meta' => ['source' => 'legacy-scan'],
            ]
        );
    }
}
