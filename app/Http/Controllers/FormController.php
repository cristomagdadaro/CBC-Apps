<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateFormRequest;
use App\Http\Requests\DeleteFormRequest;
use App\Http\Requests\DeleteParticipantRequest;
use App\Http\Requests\GetFormsRequest;
use App\Http\Requests\UpdateFormRequest;
use App\Repositories\FormRepo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class FormController extends BaseController
{

    protected function repo(): FormRepo
    {
        return $this->service;
    }

    public function __construct(FormRepo $repository)
    {
        $this->service = $repository;
    }

    public function formGuestView(GetFormsRequest $request, $event_id = null)
    {
        return Inertia::render('Forms/FormGuest', [
            'eventForm' => $this->repo()->getGuestFormByEventId($event_id),
            'quote' => Inspiring::quote(),
        ]);
    }

    public function index(GetFormsRequest $request, $event_id = null): Collection
    {
        return parent::_index($request);
    }

    public function indexParticipants(GetFormsRequest $request, $event_id = null): Collection
    {
        if (!$event_id) {
            $event_id = $request->get('search');
        }

        if (!$event_id) {
            return new Collection([]);
        }

        $data = $this->repo()->getParticipantsByEventId($event_id);

        return new Collection(['data' => $data]);
    }

    public function deleteParticipants(DeleteParticipantRequest $request, $participant_id): Model
    {
        return $this->repo()->deleteParticipantById($participant_id);
    }

    public function create(CreateFormRequest $request): Model
    {

        return parent::_store($request);
    }

    public function update(UpdateFormRequest $request, $event_id = null): Model
    {
        $model = $this->repo()->updateByEventId($event_id, $request->validated());
        $this->updateRequirements($request, $event_id);
        return $model;
    }

    public function delete(DeleteFormRequest $request, $event_id = null): Model
    {
        return $this->repo()->deleteByEventId($request->validated('event_id'));
    }

    public function show(GetFormsRequest $request, $event_id = null): Collection
    {
        $form = $this->repo()->getByEventIdWithRequirements($event_id);

        return new Collection($form ? $form->toArray() : []);
    }

    public function updateRequirements(Request $request, $event_id)
    {
        $validated = $request->validate([
            'requirements' => ['array'],
            'requirements.*.form_type' => ['required', 'string'],
            'requirements.*.is_required' => ['boolean'],
            'requirements.*.config' => ['array'],
        ]);

        $requirements = $validated['requirements'] ?? [];

        return response()->json([
            'message' => 'Requirements updated successfully.',
            'requirements' => $this->repo()->updateRequirements($event_id, $requirements),
        ]);
    }
}
