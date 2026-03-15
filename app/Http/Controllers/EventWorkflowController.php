<?php

namespace App\Http\Controllers;

use App\Models\EventSubform;
use App\Models\Participant;
use App\Models\Registration;
use App\Services\EventWorkflowService;
use App\Services\EventWorkflowFeatureService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
                    'found' => false,
                    'profile_found' => false,
                    'feature_disabled' => true,
                    'message' => 'Participant verification is currently disabled for this form workflow.',
                ],
            ], 200);
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($validated['email']));
        $participant = Participant::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->latest('created_at')
            ->first();

        if (!$participant) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'found' => false,
                    'profile_found' => false,
                    'message' => 'No participant profile found for this email. Please complete preregistration first.',
                ],
            ], 200);
        }

        $stepIds = EventSubform::query()
            ->where('event_id', $event_id)
            ->pluck('id');

        $registration = Registration::query()
            ->with('participant')
            ->where('participant_id', $participant->id)
            ->where(function ($query) use ($event_id, $stepIds) {
                $query->where('event_subform_id', $event_id);

                if ($stepIds->isNotEmpty()) {
                    $query->orWhereIn('event_subform_id', $stepIds->toArray());
                }
            })
            ->latest('created_at')
            ->first();

        if (!$registration) {
            $registration = Registration::create([
                'event_subform_id' => $event_id,
                'participant_id' => $participant->id,
                'attendance_type' => null,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'found' => true,
                    'profile_found' => true,
                    'participant_hash' => $registration->id,
                    'participant' => $participant,
                    'message' => 'Profile found. Registration has been created automatically for this event.',
                ],
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'found' => true,
                'profile_found' => true,
                'participant_hash' => $registration->id,
                'participant' => $registration->participant,
            ],
        ], 200);
    }
}
