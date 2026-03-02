<?php

namespace App\Services;

use App\Enums\Subform;
use App\Models\EventSubform;
use App\Models\EventSubformResponse;
use App\Models\ParticipantStepState;
use App\Pipelines\EventSubform\CreateParticipantIfNeeded;
use App\Pipelines\EventSubform\CreateSubformResponse;
use Carbon\Carbon;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EventWorkflowService
{
    public function getWorkflowState(string $eventId, ?string $participantId = null): array
    {
        $steps = $this->getOrderedSteps($eventId);
        $states = $this->getParticipantStates($eventId, $participantId);

        $resolved = $steps->map(function (EventSubform $step, int $index) use ($steps, $states, $participantId) {
            $status = $this->resolveStepStatus($step, $steps, $states, $participantId);

            return [
                'id' => $step->id,
                'event_id' => $step->event_id,
                'name' => $step->template?->name ?? $step->form_type,
                'description' => $step->template?->description ?? null,
                'form_type' => $step->form_type,
                'step_type' => $step->step_type ?? $step->form_type,
                'step_order' => $step->step_order,
                'is_enabled' => $step->is_enabled,
                'is_required' => $step->is_required,
                'open_from' => $this->resolveOpenFrom($step),
                'open_to' => $this->resolveOpenTo($step),
                'max_slots' => $step->max_slots,
                'responses_count' => $step->responses_count ?? 0,
                'visibility_rules' => $step->visibility_rules ?? [],
                'completion_rules' => $step->completion_rules ?? [],
                'field_schema' => $step->resolved_field_schema ?? [],
                'form_config' => is_array($step->template?->form_config)
                    ? $step->template->form_config
                    : (is_array(Arr::get($step->config, 'form_config')) ? Arr::get($step->config, 'form_config') : []),
                'status' => $status,
            ];
        })->values();

        $currentStep = $resolved->firstWhere('status', ParticipantStepState::STATUS_AVAILABLE);

        return [
            'event_id' => $eventId,
            'participant_id' => $participantId,
            'current_step_id' => $currentStep['id'] ?? null,
            'steps' => $resolved,
        ];
    }

    public function submit(array $validated): array
    {
        /** @var EventSubform $step */
        $step = EventSubform::query()->findOrFail($validated['form_parent_id']);
        $participantId = Arr::get($validated, 'participant_id');

        $this->assertCanSubmit($step, $participantId, Arr::get($validated, 'subform_type'), Arr::get($validated, 'response_data', []));

        return DB::transaction(function () use ($validated, $step, $participantId) {
            $context = app(Pipeline::class)
                ->send([
                    'validated' => $validated,
                    'participant' => null,
                    'registration' => null,
                    'subformResponse' => null,
                ])
                ->through([
                    CreateParticipantIfNeeded::class,
                ])
                ->thenReturn();

            $validated = $context['validated'] ?? $validated;
            $participantId = Arr::get($validated, 'participant_id', $participantId);

            if (!$participantId && !$this->canStartWithoutParticipant($step)) {
                $this->throwWorkflowError('participant_id', 'Participant is required to continue this step.');
            }

            if ($participantId) {
                $existing = EventSubformResponse::query()
                    ->where('form_parent_id', $step->id)
                    ->where('participant_id', $participantId)
                    ->first();

                if ($existing) {
                    $this->ensureStepState($step, $participantId, $existing->completion_hash, 'duplicate');

                    return [
                        'status' => 'duplicate',
                        'participant' => $context['participant'] ?? null,
                        'registration' => $context['registration'] ?? null,
                        'subformResponse' => $existing,
                    ];
                }
            }

            $this->assertSlotAvailability($step);

            $completionHash = $participantId
                ? $this->buildCompletionHash($participantId, $step->id, $validated['response_data'] ?? [])
                : null;

            $context['validated'] = $validated;
            $context['validated']['completion_hash'] = $completionHash;
            $context['validated']['status'] = 'submitted';

            $context = app(Pipeline::class)
                ->send($context)
                ->through([
                    CreateSubformResponse::class,
                ])
                ->thenReturn();

            $response = $context['subformResponse'];

            if ($participantId) {
                $this->ensureStepState($step, $participantId, $completionHash, 'submitted');
            }

            return [
                'status' => 'created',
                'participant' => $context['participant'] ?? null,
                'registration' => $context['registration'] ?? null,
                'subformResponse' => $response,
            ];
        });
    }

    public function assertCanSubmit(EventSubform $step, ?string $participantId, ?string $subformType = null, array $responseData = []): void
    {
        if ($subformType && $subformType !== $step->form_type) {
            $this->throwWorkflowError('subform_type', 'This step does not match the submitted form type.');
        }
        if (!$step->is_enabled) {
            $this->throwWorkflowError('step', 'This step is currently disabled.');
        }

        if (!$step->isOpen()) {
            $this->throwWorkflowError('step', 'This step is outside the allowed time window.');
        }

        if (!$this->isVisible($step, $participantId)) {
            $this->throwWorkflowError('step', 'This step is not available based on current conditions.');
        }

        $this->assertConditionalLimits($step, $responseData);

        if (!$participantId) {
            if (!$this->isFirstStep($step)) {
                $this->throwWorkflowError('step', 'You must complete the prior steps before starting this one.');
            }

            if (!$this->canStartWithoutParticipant($step)) {
                $this->throwWorkflowError('participant_id', 'Participant is required for this step.');
            }

            return;
        }

        $previousStep = $this->getPreviousStep($step);
        if ($previousStep && !$this->isStepCompleted($previousStep, $participantId)) {
            $this->throwWorkflowError('step', 'Complete the previous step before proceeding.');
        }
    }

    protected function assertConditionalLimits(EventSubform $step, array $responseData): void
    {
        $limits = data_get($step->config, 'limits', []);

        if (!is_array($limits) || empty($limits)) {
            return;
        }

        foreach ($limits as $limit) {
            $field = Arr::get($limit, 'field');
            $max = Arr::get($limit, 'max');

            if (!$field || !is_numeric($max)) {
                continue;
            }

            $value = data_get($responseData, $field);
            if ($value === null || $value === '') {
                continue;
            }

            $count = EventSubformResponse::query()
                ->where('form_parent_id', $step->id)
                ->where("response_data->$field", $value)
                ->count();

            if ($count >= (int) $max) {
                $label = ucwords(str_replace('_', ' ', $field));
                $this->throwWorkflowError('limit', "Limit reached for $label (max $max)");
            }
        }
    }

    protected function getOrderedSteps(string $eventId): Collection
    {
        return EventSubform::query()
            ->where('event_id', $eventId)
            ->with('template')
            ->withCount('responses')
            ->orderByRaw('CASE WHEN step_order IS NULL THEN 1 ELSE 0 END')
            ->orderBy('step_order')
            ->orderBy('created_at')
            ->get();
    }

    protected function getParticipantStates(string $eventId, ?string $participantId): Collection
    {
        if (!$participantId) {
            return collect();
        }

        return ParticipantStepState::query()
            ->where('event_id', $eventId)
            ->where('participant_id', $participantId)
            ->get()
            ->keyBy('event_subform_id');
    }

    protected function resolveStepStatus(EventSubform $step, Collection $steps, Collection $states, ?string $participantId): string
    {
        if (!$step->is_enabled) {
            return ParticipantStepState::STATUS_DISABLED;
        }

        if (!$this->isVisible($step, $participantId)) {
            return ParticipantStepState::STATUS_HIDDEN;
        }

        // Check time-based constraints first, before completion status
        if ($this->isBeforeOpenFrom($step)) {
            return ParticipantStepState::STATUS_NOT_YET_OPEN;
        }

        if ($this->isAfterOpenTo($step)) {
            return ParticipantStepState::STATUS_EXPIRED;
        }

        if (!$step->isOpen()) {
            return ParticipantStepState::STATUS_EXPIRED;
        }

        // Check completion status after time constraints
        if ($participantId && $this->isStepCompleted($step, $participantId)) {
            return ParticipantStepState::STATUS_COMPLETED;
        }

        if ($this->isStepFull($step)) {
            return ParticipantStepState::STATUS_FULL;
        }

        if (!$participantId) {
            return $this->isFirstStep($step)
                ? ParticipantStepState::STATUS_AVAILABLE
                : ParticipantStepState::STATUS_LOCKED;
        }

        $previous = $this->getPreviousStep($step);
        if ($previous && !$this->isStepCompleted($previous, $participantId)) {
            return ParticipantStepState::STATUS_LOCKED;
        }

        return ParticipantStepState::STATUS_AVAILABLE;
    }

    protected function isFirstStep(EventSubform $step): bool
    {
        $first = $this->getOrderedSteps($step->event_id)
            ->first(fn ($item) => $item->is_enabled);
        return $first?->id === $step->id;
    }

    protected function getPreviousStep(EventSubform $step): ?EventSubform
    {
        $steps = $this->getOrderedSteps($step->event_id);
        $index = $steps->search(fn ($item) => $item->id === $step->id);

        if ($index === false || $index === 0) {
            return null;
        }

        for ($i = $index - 1; $i >= 0; $i--) {
            $candidate = $steps[$i] ?? null;
            if ($candidate && $candidate->is_enabled) {
                return $candidate;
            }
        }

        return null;
    }

    protected function isStepCompleted(EventSubform $step, string $participantId): bool
    {
        $state = ParticipantStepState::query()
            ->where('event_subform_id', $step->id)
            ->where('participant_id', $participantId)
            ->first();

        if ($state) {
            return $state->status === ParticipantStepState::STATUS_COMPLETED;
        }

        $legacy = EventSubformResponse::query()
            ->where('form_parent_id', $step->id)
            ->where('subform_type', $step->form_type)
            ->where('status', 'submitted')
            ->where('participant_id', $participantId)
            ->first();

        if ($legacy) {
            $this->ensureStepState($step, $participantId, $legacy->completion_hash, 'legacy');
            return true;
        }

        return false;
    }

    protected function ensureStepState(EventSubform $step, string $participantId, ?string $hash, string $source): void
    {
        ParticipantStepState::updateOrCreate(
            [
                'event_id' => $step->event_id,
                'participant_id' => $participantId,
                'event_subform_id' => $step->id,
            ],
            [
                'status' => ParticipantStepState::STATUS_COMPLETED,
                'completed_at' => now(),
                'completion_hash' => $hash,
                'meta' => ['source' => $source],
            ]
        );
    }

    protected function assertSlotAvailability(EventSubform $step): void
    {
        if (!$step->max_slots || $step->max_slots <= 0) {
            return;
        }

        $count = EventSubformResponse::query()
            ->where('form_parent_id', $step->id)
            ->lockForUpdate()
            ->count();

        if ($count >= $step->max_slots) {
            $this->throwWorkflowError('step', 'This step has reached the maximum number of slots.');
        }
    }

    protected function isStepFull(EventSubform $step): bool
    {
        if (!$step->max_slots || $step->max_slots <= 0) {
            return false;
        }

        $count = EventSubformResponse::query()
            ->where('form_parent_id', $step->id)
            ->count();

        return $count >= $step->max_slots;
    }

    protected function resolveOpenFrom(EventSubform $step): ?string
    {
        $openFrom = $step->open_from ?? data_get($step->config, 'open_from');
        return $openFrom ? Carbon::parse($openFrom)->toDateTimeString() : null;
    }

    protected function resolveOpenTo(EventSubform $step): ?string
    {
        $openTo = $step->open_to ?? data_get($step->config, 'open_to');
        return $openTo ? Carbon::parse($openTo)->toDateTimeString() : null;
    }

    protected function isBeforeOpenFrom(EventSubform $step): bool
    {
        $openFrom = $step->open_from ?? data_get($step->config, 'open_from');
        if (!$openFrom) {
            return false;
        }

        return now()->lt(Carbon::parse($openFrom));
    }

    protected function isAfterOpenTo(EventSubform $step): bool
    {
        $openTo = $step->open_to ?? data_get($step->config, 'open_to');
        if (!$openTo) {
            return false;
        }

        return now()->gt(Carbon::parse($openTo));
    }

    protected function buildCompletionHash(string $participantId, string $stepId, array $payload): string
    {
        return hash('sha256', $participantId . '|' . $stepId . '|' . json_encode($payload));
    }

    protected function canStartWithoutParticipant(EventSubform $step): bool
    {
        $allowed = [
            Subform::PREREGISTRATION->value,
            Subform::PREREGISTRATION_BIOTECH->value,
            Subform::PREREGISTRATION_QUIZBEE->value,
        ];
        $type = $step->form_type ?? $step->step_type;

        return in_array($type, $allowed, true);
    }

    protected function isVisible(EventSubform $step, ?string $participantId): bool
    {
        $rules = $step->visibility_rules ?? [];

        if (!$rules) {
            return true;
        }

        $requiredSteps = Arr::get($rules, 'requires_steps', []);
        if (empty($requiredSteps) || !$participantId) {
            return true;
        }

        $steps = EventSubform::query()
            ->where('event_id', $step->event_id)
            ->whereIn('form_type', $requiredSteps)
            ->get();

        foreach ($steps as $required) {
            if (!$this->isStepCompleted($required, $participantId)) {
                return false;
            }
        }

        return true;
    }

    protected function throwWorkflowError(string $field, string $message): void
    {
        throw ValidationException::withMessages([
            $field => $message,
        ]);
    }
}
