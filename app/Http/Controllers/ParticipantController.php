<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateParticipantRequest;
use App\Repositories\ParticipantRepo;
use Illuminate\Http\JsonResponse;

class ParticipantController extends BaseController
{
    public function __construct(ParticipantRepo $repository)
    {
        $this->service = $repository;
    }

    protected function repo(): ParticipantRepo
    {
        return $this->service;
    }

    public function post(CreateParticipantRequest $request, $event_id = null): JsonResponse
    {
        try {
            $result = $this->repo()->createWithRegistration($request->validated());

            return response()->json([
                'status' => 'success',
                'participant_hash' => $result['participant_hash'],
                'participant' => $result['participant'],
                'event_subform_id' => $result['event_subform_id'],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to register participant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
