<?php

namespace App\Repositories;

use App\Models\EventRequirement;
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

    public function getGuestFormByEventId(?string $eventId): ?Form
    {
        if (!$eventId) {
            return null;
        }

        return $this->model
            ->newQuery()
            ->where('event_id', $eventId)
            ->withCount('participants')
            ->with('requirements')
            ->first();
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
            ->with('requirements')
            ->first();
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

        foreach ($requirements as $req) {
            EventRequirement::updateOrCreate(
                [
                    'event_id' => $form->event_id,
                    'form_type' => $req['form_type'], // use form_type as unique key
                ],
                [
                    'is_required' => $req['is_required'] ?? true,
                    'config' => $req['config'] ?? [],
                ]
            );
        }

        return $form->requirements()->get();
    }

    public function getTodayEvents(): Collection
    {
        $startOfDay = Carbon::now()->startOfDay();
        $endOfDay = Carbon::now()->endOfDay();

        return $this->model
            ->newQuery()
            ->select([
                'event_id',
                'title',
                'venue',
                'date_from',
                'date_to',
                'time_from',
                'time_to',
            ])
            ->where('is_suspended', false)
            ->where(function ($query) use ($startOfDay, $endOfDay) {
                $query
                    ->whereBetween('date_from', [$startOfDay, $endOfDay])
                    ->orWhereBetween('date_to', [$startOfDay, $endOfDay])
                    ->orWhere(function ($subQuery) use ($startOfDay, $endOfDay) {
                        $subQuery
                            ->where('date_from', '<=', $startOfDay)
                            ->where('date_to', '>=', $endOfDay);
                    });
            })
            ->orderBy('date_from')
            ->orderBy('time_from')
            ->get();
    }
}
