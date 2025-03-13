<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateParticipantRequest;
use App\Models\Registration;
use App\Repositories\ParticipantRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ParticipantController extends BaseController
{
    public function __construct(ParticipantRepo $repository)
    {
        $this->service = $repository;
    }

    public function post(CreateParticipantRequest $request, $event_id = null): JsonResponse
    {
        DB::beginTransaction();

        try {
            $participant = $this->service->create($request->validated());

            do {
                $temp = Str::uuid()->toString();
            } while (Registration::where('id', $temp)->exists());

            Registration::factory()->create([
                'id' => $temp,
                'event_id' => $request->validated('event_id'),
                'participant_id' => $request->validated('id'),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'participant_hash' => $temp,
                'event_id' => $request->validated('event_id'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to register participant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
