<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEventSubformResponseRequest;
use App\Http\Requests\GetEventSubformRequest;
use App\Http\Requests\UpdateEventSubformResponseRequest;
use App\Models\EventSubform;
use App\Repositories\EventSubformResponseRepo;
use App\Services\EventWorkflowService;
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
        $parameters = new Collection($request->query());
        $resolvedEventId = $event_id ?? $request->input('event_id');
        $data = $this->repo()->searchResponses($parameters, $resolvedEventId);

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

    public function create(CreateEventSubformResponseRequest $request, EventWorkflowService $workflow)
    {
        $validated = $this->withUploadedProof($request, $request->validated());

        $requirement = EventSubform::select(['id', 'event_id'])->find($validated['form_parent_id']);

        $result = $workflow->submit($validated);

        $statusCode = $result['status'] === 'duplicate' ? 200 : 201;

        $participantHash = $result['registration']?->id ?? ($validated['participant_id'] ?? null);

        return response()->json([
            'status' => 'success',
            'workflow_status' => $result['status'],
            'participant_hash' => $participantHash,
            'participant' => $result['participant'],
            'event_subform_id' => $requirement?->event_id ?? $validated['form_parent_id'],
            'requirement_id' => $validated['form_parent_id'],
            'data' => $result['subformResponse'],
        ], $statusCode);
    }

    public function update(UpdateEventSubformResponseRequest $request, string $event_id): JsonResponse
    {
        try {
            $validated = $this->withUploadedProof($request, $request->validated());

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

    private function withUploadedProof(Request $request, array $validated): array
    {
        if ($request->hasFile('response_data.proof_of_enrollment')) {
            $file = $request->file('response_data.proof_of_enrollment');
            $path = $file->store('quizbee/proof-of-enrollment', 'public');
            $validated['response_data']['proof_of_enrollment'] = $path;
        }

        return $validated;
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
