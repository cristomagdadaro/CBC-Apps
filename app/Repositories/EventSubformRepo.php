<?php

namespace App\Repositories;

use App\Models\EventSubformResponse;
use App\Pipelines\EventSubform\CreateParticipantIfNeeded;
use App\Pipelines\EventSubform\CreateSubformResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Pipeline\Pipeline;

class EventSubformRepo extends AbstractRepoService
{
    public function __construct(EventSubformResponse $model)
    {
        parent::__construct($model);
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
        return $this->model
            ->newQuery()
            ->join('event_requirements', 'event_subform_responses.form_parent_id', '=', 'event_requirements.id')
            ->where('event_requirements.event_id', $event_id)
            ->select('event_subform_responses.*')
            ->get();
    }
}
