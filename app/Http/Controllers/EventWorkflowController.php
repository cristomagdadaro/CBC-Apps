<?php

namespace App\Http\Controllers;

use App\Models\EventSubform;
use App\Models\Participant;
use App\Models\Registration;
use App\Services\EventWorkflowService;
use App\Services\EventWorkflowFeatureService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

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
                    'message' => 'If an eligible participant profile exists, proceed to the next registration step.',
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
            $registration = Registration::query()->create([
                'id' => (string) Str::uuid(),
                'event_subform_id' => $event_id,
                'participant_id' => $participant->id,
                'attendance_type' => null,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'found' => true,
                'profile_found' => true,
                'participant_hash' => $registration->id,
                'participant' => [
                    'id' => $participant->id,
                    'email' => $participant->email,
                    'fname' => $participant->fname,
                    'mname' => $participant->mname,
                    'lname' => $participant->lname,
                    'suffix' => $participant->suffix,
                ],
                'message' => 'If an eligible participant profile exists, proceed to the next registration step.',
            ],
        ], 200);
    }
}
