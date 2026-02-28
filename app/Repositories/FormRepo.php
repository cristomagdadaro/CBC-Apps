<?php

namespace App\Repositories;

use App\Models\EventSubform;
use App\Models\Form;
use App\Models\Participant;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FormRepo extends AbstractRepoService
{
    public function __construct(Form $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all forms formatted for select fields
     */
    public function getOptions()
    {
        return $this->model
            ->newQuery()
            ->select('id as name', 'title as label')
            ->get();
    }

    public function getGuestFormByEventId(?string $eventId): ?Form
    {
        if (!$eventId) {
            return null;
        }

        $form = $this->model
            ->newQuery()
            ->where('event_id', $eventId)
            ->withCount('participants')
            ->with(['requirements' => function ($query) {
                $query->withCount('responses')
                    ->orderByRaw('CASE WHEN step_order IS NULL THEN 1 ELSE 0 END')
                    ->orderBy('step_order')
                    ->orderBy('created_at');
            }])
            ->first();

            
        if ($form) {
            $form->responses_count = $this->getResponsesCountByEventId($eventId);
        }

        return $form;
    }

    /**
     * Get the total number of responses for a given event ID.
     * @param mixed $eventId
     * @return int
     */
    public function getResponsesCountByEventId(?string $eventId): int
    {
        if (!$eventId) {
            return 0;
        }

        return EventSubform::join('event_subform_responses', 'form_parent_id', '=', 'event_subforms.id')
            ->where('event_subforms.event_id', $eventId)
            ->count();
    }

    public function getParticipantsByEventId(string $eventId): Collection
    {
        return Registration::where('event_id', $eventId)
            ->with('participant')
            ->get()
            ->filter(fn ($registration) => $registration->participant !== null)
            ->pluck('participant')
            ->values();
    }

    public function deleteParticipantById(string $participantId): Model
    {
        $model = Participant::where('id', $participantId)->firstOrFail();
        $model->delete();
        return $model;
    }

    public function getByEventIdWithRequirements(string $eventId): ?Form
    {
        return $this->model
            ->newQuery()
            ->where('event_id', $eventId)
            ->with(['requirements' => function ($query) {
                $query->withCount('responses')
                    ->orderByRaw('CASE WHEN step_order IS NULL THEN 1 ELSE 0 END')
                    ->orderBy('step_order')
                    ->orderBy('created_at');
            }])
            ->first();
    }

    public function createEventWithRequirements(array $data): Model
    {
        // Extract requirements before creating the form to avoid mass assignment issues
        $requirements = $data['requirements'] ?? [];
        unset($data['requirements']);
        
        $form = $this->model->create($data);
        $this->updateRequirements($form->event_id, $requirements);
        return $form;
    }

    public function updateByEventId(string $eventId, array $data): Model
    {
        $model = $this->model
            ->newQuery()
            ->where('event_id', $eventId)
            ->with('requirements')
            ->firstOrFail();

        $model->fill($data);
        $model->save();

        return $model;
    }

    public function deleteByEventId(string $eventId): Model
    {
        $model = $this->model
            ->newQuery()
            ->where('event_id', $eventId)
            ->firstOrFail();

        $model->delete();
        return $model;
    }

    public function updateRequirements(string $eventId, array $requirements): Collection
    {
        $form = $this->model
            ->newQuery()
            ->where('event_id', $eventId)
            ->firstOrFail();

        // Collect incoming form identifiers (form_type + template_id for custom forms)
        $incomingIdentifiers = collect($requirements)->map(function ($req) {
            if ($req['form_type'] === 'custom' && !empty($req['form_type_template_id'])) {
                return 'custom:' . $req['form_type_template_id'];
            }
            return $req['form_type'];
        })->filter()->values()->all();

        // Get existing subforms to determine which to keep/delete
        $existingSubforms = EventSubform::where('event_id', $form->event_id)->get();
        
        foreach ($existingSubforms as $existing) {
            $identifier = $existing->form_type;
            if ($existing->form_type === 'custom' && $existing->form_type_template_id) {
                $identifier = 'custom:' . $existing->form_type_template_id;
            }
            
            if (!in_array($identifier, $incomingIdentifiers)) {
                $existing->delete();
            }
        }

        foreach ($requirements as $index => $req) {
            // Build query for finding existing subform
            $query = EventSubform::withTrashed()
                ->where('event_id', $form->event_id)
                ->where('form_type', $req['form_type']);
            
            // For custom forms, also match by template ID
            if ($req['form_type'] === 'custom' && !empty($req['form_type_template_id'])) {
                $query->where('form_type_template_id', $req['form_type_template_id']);
            }

            $subform = $query->first();
            
            if (!$subform) {
                $subform = new EventSubform([
                    'event_id' => $form->event_id,
                    'form_type' => $req['form_type'],
                ]);
            }

            if ($subform->trashed()) {
                $subform->restore();
            }

            $subform->fill([
                'form_type_template_id' => $req['form_type_template_id'] ?? null,
                'step_type' => $req['step_type'] ?? $req['form_type'],
                'step_order' => $req['step_order'] ?? ($index + 1),
                'is_enabled' => $req['is_enabled'] ?? true,
                'open_from' => $req['open_from'] ?? null,
                'open_to' => $req['open_to'] ?? null,
                'is_required' => $req['is_required'] ?? true,
                'max_slots' => array_key_exists('max_slots', $req) ? $req['max_slots'] : null,
                'config' => $req['config'] ?? [],
                'visibility_rules' => $req['visibility_rules'] ?? null,
                'completion_rules' => $req['completion_rules'] ?? null,
            ]);

            $subform->save();
        }

        return $form->requirements()->get();
    }

    public function getTodayEvents(): Collection
    {
        // We only need the start of today. 
        // Anything ending *after* this point is valid.
        $startOfDay = Carbon::now()->startOfDay();

        return $this->model
            ->newQuery()
            ->select([
                'event_id', 'title', 'venue', 
                'date_from', 'date_to', 
                'time_from', 'time_to'
            ])
            ->where('is_suspended', false)
            // Logic: Show any event that has NOT finished before today
            ->where('date_to', '>=', $startOfDay) 
            ->orderBy('date_from')
            ->orderBy('time_from')
            ->get();
    }
}
