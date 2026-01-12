<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEventSubformRequest;
use App\Http\Requests\GetEventSubformRequest;
use App\Enums\Subform;
use App\Models\Registration;
use App\Models\Participant;
use App\Repositories\EventSubformRepo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class EventSubformController extends BaseController
{
    public function __construct(EventSubformRepo $repository)
    {
        $this->service = $repository;
    }

    public function index(GetEventSubformRequest $request)
    {
        return parent::_index($request);
    }

    public function create(CreateEventSubformRequest $request)
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($validated) {
            $participant = null;
            $registration = null;

            if (in_array($validated['subform_type'], [Subform::PREREGISTRATION->value, Subform::REGISTRATION->value], true)) {
                $participantPayload = Arr::only($validated['response_data'], (new Participant())->getFillable());
                $participant = Participant::factory()->create($participantPayload);

                $registration = Registration::factory()->create([
                    'event_id' => $validated['form_parent_id'],
                    'participant_id' => $participant->id,
                    'attendance_type' => Arr::get($validated['response_data'], 'attendance_type'),
                ]);

                $validated['participant_id'] = $registration->id;
            }

            $subformResponse = $this->service->create([
                'form_parent_id' => $validated['form_parent_id'],
                'participant_id' => $validated['participant_id'] ?? null,
                'subform_type' => $validated['subform_type'],
                'response_data' => $validated['response_data'],
            ]);

            return response()->json([
                'status' => 'success',
                'participant_hash' => $registration?->id,
                'participant' => $participant,
                'event_id' => $validated['form_parent_id'],
                'data' => $subformResponse,
            ], 201);
        });
    }


}
