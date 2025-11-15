<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateFormRequest;
use App\Http\Requests\DeleteFormRequest;
use App\Http\Requests\DeleteParticipantRequest;
use App\Http\Requests\GetFormsRequest;
use App\Http\Requests\UpdateFormRequest;
use App\Models\EventRequirement;
use App\Models\Form;
use App\Models\Participant;
use App\Models\Registration;
use App\Repositories\FormRepo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class FormController extends BaseController
{

    public function __construct(FormRepo $repository)
    {
        $this->service = $repository;
    }

    public function formGuestView(GetFormsRequest $request, $event_id = null)
    {
        $temp = (new Form)->newQuery();
        return Inertia::render('Forms/FormGuest', [
            'eventForm' => $temp->where('event_id', $event_id)->withCount('participants')->withCount('participants')->first(),
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

        $data = Registration::where('event_id', $event_id)
            ->with('participant')
            ->get()
            ->filter(fn ($registration) => $registration->participant !== null)
            ->pluck('participant')
            ->values();

        return new Collection(['data' => $data]);
    }

    public function deleteParticipants(DeleteParticipantRequest $request, $participant_id): Model
    {
        $model = Participant::where('id', $participant_id)->first();
        $model->delete();
        return $model;
    }

    public function create(CreateFormRequest $request, $event_id = null): Model
    {
        return parent::_store($request);
    }

    public function update(UpdateFormRequest $request, $event_id = null): Model
    {
        $model = $this->service->model->where('event_id', $event_id)->first();
        $model->fill($request->validated());
        $model->save();

        return $model;
    }

    public function delete(DeleteFormRequest $request, $event_id = null): Model
    {
        $model = $this->service->model->where('event_id', $request->validated('event_id'))->first();
        $model->delete();
        return $model;
    }

    public function show(GetFormsRequest $request, $event_id = null): Collection
    {
        $form = Form::where('event_id', $event_id)->with('requirements')->first();

        return new Collection($form ? $form->toArray() : []);
    }

    public function updateRequirements(Request $request, $event_id)
    {
        $form = Form::where('event_id', $event_id)->firstOrFail();

        $validated = $request->validate([
            'requirements' => ['array'],
            'requirements.*.form_type' => ['required', 'string'],
            'requirements.*.is_required' => ['boolean'],
            'requirements.*.config' => ['array'],
        ]);

        $requirements = $validated['requirements'] ?? [];

        // Replace existing requirements for this event
        EventRequirement::where('event_id', $form->event_id)->delete();

        foreach ($requirements as $req) {
            EventRequirement::create([
                'event_id' => $form->event_id,
                'form_type' => $req['form_type'],
                'is_required' => $req['is_required'] ?? true,
                'config' => $req['config'] ?? [],
            ]);
        }

        return response()->json([
            'message' => 'Requirements updated successfully.',
            'requirements' => $form->requirements()->get(),
        ]);
    }
}
