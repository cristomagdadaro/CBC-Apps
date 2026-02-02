<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEventSubformRequest;
use App\Http\Requests\GetEventSubformRequest;
use App\Models\EventRequirement;
use App\Repositories\EventSubformRepo;

class EventSubformController extends BaseController
{
    public function __construct(EventSubformRepo $repository)
    {
        $this->service = $repository;
    }

    protected function repo(): EventSubformRepo
    {
        return $this->service;
    }

    public function index(GetEventSubformRequest $request)
    {
        return parent::_index($request);
    }

    public function indexResponses(GetEventSubformRequest $request, string $event_id = null)
    {
        $validated = $request->validated();

        $data = $this->repo()->getResponsesByEventId($event_id);

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'params' => $validated,
        ], 200);
    }

    public function create(CreateEventSubformRequest $request)
    {
        $validated = $request->validated();

        $requirement = EventRequirement::select(['id', 'event_id'])->find($validated['form_parent_id']);

        $result = $this->repo()->createWithOptionalParticipant($validated);

        return response()->json([
            'status' => 'success',
            'participant_hash' => $result['registration']?->id,
            'participant' => $result['participant'],
            'event_id' => $requirement?->event_id ?? $validated['form_parent_id'],
            'requirement_id' => $validated['form_parent_id'],
            'data' => $result['subformResponse'],
        ], 201);
    }


}
