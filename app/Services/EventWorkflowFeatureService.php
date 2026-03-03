<?php

namespace App\Services;

use App\Repositories\OptionRepo;

class EventWorkflowFeatureService
{
    public const KEY_EVENT_WORKFLOW = 'forms_event_workflow_enabled';
    public const KEY_PARTICIPANT_WORKFLOW = 'forms_participant_workflow_enabled';
    public const KEY_PARTICIPANT_VERIFICATION = 'forms_participant_verification_enabled';

    public function __construct(protected OptionRepo $optionRepo)
    {
    }

    public function toggles(): array
    {
        return [
            'event_workflow_enabled' => $this->isEventWorkflowEnabled(),
            'participant_workflow_enabled' => $this->isParticipantWorkflowEnabled(),
            'participant_verification_enabled' => $this->isParticipantVerificationEnabled(),
        ];
    }

    public function isEventWorkflowEnabled(): bool
    {
        return $this->resolveBool(self::KEY_EVENT_WORKFLOW, true);
    }

    public function isParticipantWorkflowEnabled(): bool
    {
        return $this->resolveBool(self::KEY_PARTICIPANT_WORKFLOW, true);
    }

    public function isParticipantVerificationEnabled(): bool
    {
        return $this->resolveBool(self::KEY_PARTICIPANT_VERIFICATION, true);
    }

    protected function resolveBool(string $key, bool $default): bool
    {
        $value = $this->optionRepo->getByKey($key);

        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
                return false;
            }
        }

        return $default;
    }
}
