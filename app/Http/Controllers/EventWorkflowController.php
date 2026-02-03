<?php

namespace App\Http\Controllers;

use App\Services\EventWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EventWorkflowController extends Controller
{
    public function state(Request $request, string $event_id, EventWorkflowService $workflow): JsonResponse
    {
        $participantId = $request->input('participant_id')
            ?? $request->input('participant_hash');

        $state = $workflow->getWorkflowState($event_id, $participantId);

        return response()->json([
            'status' => 'success',
            'data' => $state,
        ], 200);
    }
}
