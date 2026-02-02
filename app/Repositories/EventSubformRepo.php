<?php

namespace App\Repositories;

use App\Models\EventRequirement;
use App\Models\EventSubformResponse;
use App\Pipelines\EventSubform\CreateParticipantIfNeeded;
use App\Pipelines\EventSubform\CreateSubformResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Database\Eloquent\Builder;

class EventSubformRepo extends AbstractRepoService
{
    public function __construct(EventSubformResponse $model)
    {
        parent::__construct($model);
        // Default appends for responses
        $this->appendWith = ['participant'];
    }

    /**
     * Override parent filter to support filtering by event_id through event_requirements.
     * The event_subform_responses table has form_parent_id (event_requirements.id),
     * not event_id directly.
     */
    public function applyParentFilter(Builder &$query, Collection $parameters): void
    {
        $filterByParentColumn = $parameters->get('filter_by_parent_column');
        $filterByParentId = $parameters->get('filter_by_parent_id');

        if (empty($filterByParentColumn) || empty($filterByParentId)) {
            return;
        }

        // Special handling: if filtering by event_id, we need to join through event_requirements
        if ($filterByParentColumn === 'event_id') {
            // Get all requirement IDs for this event
            $requirementIds = EventRequirement::where('event_id', $filterByParentId)
                ->pluck('id')
                ->toArray();

            if (!empty($requirementIds)) {
                $query->whereIn('form_parent_id', $requirementIds);
            } else {
                // No requirements found - return empty result
                $query->whereRaw('1 = 0');
            }
            return;
        }

        // Default behavior for other columns (e.g., form_parent_id directly)
        $query->where($filterByParentColumn, $filterByParentId);
    }

    public function createWithOptionalParticipant(array $validated): array
    {
        return DB::transaction(function () use ($validated) {
            $context = app(Pipeline::class)
                ->send([
                    'validated' => $validated,
                    'participant' => null,
                    'registration' => null,
                    'subformResponse' => null,
                ])
                ->through([
                    CreateParticipantIfNeeded::class,
                    CreateSubformResponse::class,
                ])
                ->thenReturn();

            return [
                'participant' => $context['participant'] ?? null,
                'registration' => $context['registration'] ?? null,
                'subformResponse' => $context['subformResponse'],
            ];
        });
    }

    public function getResponsesByEventId(string $event_id)
    {
        // Retrieve all subform responses linked to registrations of the specified event
        $requirementIds = EventRequirement::where('event_id', $event_id)
            ->pluck('id')
            ->toArray();

        return $this->model
            ->whereIn('form_parent_id', $requirementIds)
            ->with('participant')
            ->get();
    }
}
