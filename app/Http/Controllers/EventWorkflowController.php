<?php

namespace App\Http\Controllers;

use App\Services\EventWorkflowFeatureService;
use App\Services\EventWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventWorkflowController extends BaseController
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

    public function resolveParticipantByEmail(Request $request, string $event_id, EventWorkflowFeatureService $features): JsonResponse
    {
        if (!$features->isParticipantVerificationEnabled()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'feature_disabled' => true,
                    'found' => false,
                    'profile_found' => false,
                    'message' => 'Participant verification is currently disabled.',
                ],
            ], 200);
        }

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'verification_required' => true,
                'message' => 'If this email is eligible, a verification link will be sent.',
            ],
        ], 200);
    }
}
