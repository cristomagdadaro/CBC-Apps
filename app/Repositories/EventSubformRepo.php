<?php

namespace App\Repositories;

use App\Models\EventSubform;

class EventSubformRepo extends AbstractRepoService
{
    public function __construct(EventSubform $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all event subforms formatted for select fields
     */
    public function getOptions()
    {
        return $this->model
            ->newQuery()
            ->select('id as name', 'form_type as label')
            ->orderBy('label', 'ASC')
            ->get();
    }

    public function getRegistrationRequirement(string $eventId): ?EventSubform
    {
        return $this->model
            ->newQuery()
            ->where('event_id', $eventId)
            ->where('is_enabled', true)
            ->where(function ($query) {
                $query->where('form_type', 'registration')
                    ->orWhere('step_type', 'registration');
            })
            ->orderByRaw('CASE WHEN step_order IS NULL THEN 1 ELSE 0 END')
            ->orderBy('step_order')
            ->orderBy('created_at')
            ->first();
    }

    public function getRequiredSubforms(string $eventId)
    {
        return $this->model
            ->newQuery()
            ->where('event_id', $eventId)
            ->where('is_required', true)
            ->where('is_enabled', true)
            ->where(function ($query) {
                $query->where('form_type', '!=', 'registration')
                    ->where(function ($inner) {
                        $inner->whereNull('step_type')
                            ->orWhere('step_type', '!=', 'registration');
                    });
            })
            ->orderByRaw('CASE WHEN step_order IS NULL THEN 1 ELSE 0 END')
            ->orderBy('step_order')
            ->orderBy('created_at')
            ->get(['id', 'form_type']);
    }

    public function findEventId(string $id): ?string
    {
        $subform = $this->model
            ->newQuery()
            ->select(['id', 'event_id'])
            ->find($id);

        return $subform?->event_id ? (string) $subform->event_id : null;
    }
}
