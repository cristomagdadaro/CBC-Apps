<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEventSubformResponseRequest;
use App\Http\Requests\GetEventSubformRequest;
use App\Http\Requests\UpdateEventSubformResponseRequest;
use App\Models\EventSubform;
use App\Repositories\EventSubformResponseRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EventSubformResponseController extends BaseController
{
    public function __construct(EventSubformResponseRepo $repository)
    {
        $this->service = $repository;
    }

    protected function repo(): EventSubformResponseRepo
    {
        return $this->service;
    }

    public function index(GetEventSubformRequest $request, string $event_id = null): JsonResponse
    {
        $validated = new Collection($request->validated());
        $resolvedEventId = $event_id ?? $request->input('event_id');
        $data = $this->repo()->searchResponses($validated, $resolvedEventId);

        return response()->json($data, 200);
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

    public function create(CreateEventSubformResponseRequest $request)
    {
        $validated = $request->validated();

        $requirement = EventSubform::select(['id', 'event_id'])->find($validated['form_parent_id']);

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

    public function update(UpdateEventSubformResponseRequest $request, string $event_id): JsonResponse
    {
        try {
            $validated = $request->validated();

            $updated = $this->repo()->updateResponse(
                $this->repo()->model->findOrFail($event_id),
                $validated
            );

            return response()->json([
                'status' => 'success',
                'data' => $updated,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function delete(Request $request, string $response_id): JsonResponse
    {
        $deleted = $this->repo()->delete($response_id);

        return response()->json([
            'status' => 'success',
            'data' => $deleted,
        ], 200);
    }


}
