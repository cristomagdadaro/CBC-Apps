<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRegistrationRequest;
use App\Models\Registration;
use App\Repositories\ParticipantRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ParticipantController extends BaseController
{
    public function __construct(ParticipantRepo $repository)
    {
        $this->service = $repository;
    }
    public function post(CreateRegistrationRequest $request, $event_id = null): JsonResponse
    {
        $participant = $this->service->create($request->validated());

        do {
            $temp = Str::uuid()->toString();
        } while (Registration::where('id', $temp)->exists());

        Registration::factory()->create([
            'id' => $temp,
            'event_id' => $request->validated('event_id'),
            'participant_id' => $request->validated('id'),
        ]);

        // Generate an 8-character hash of the registration ID
        $hashed = substr(md5($temp), 0, 8); // Uses first 8 characters of MD5 hash

        return response()->json([
            'status' => 'success',
            'participant_hash' => $hashed
        ], 201);
    }
}
