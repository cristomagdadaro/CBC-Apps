<?php

namespace App\Http\Controllers\Research;

use App\Http\Controllers\Controller;
use App\Http\Requests\Research\StoreResearchExperimentRequest;
use App\Http\Requests\Research\UpdateResearchExperimentRequest;
use App\Models\Research\ResearchExperiment;
use App\Models\Research\ResearchMonitoringRecord;
use App\Models\Research\ResearchSample;
use App\Services\Research\ResearchIdentifierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ResearchExperimentController extends Controller
{
    public function __construct(
        protected ResearchIdentifierService $identifierService
    ) {
    }

    public function store(StoreResearchExperimentRequest $request): JsonResponse
    {
        $userId = $request->user()?->id;
        $payload = $request->validated();
        $payload['code'] = $payload['code'] ?? $this->identifierService->nextExperimentCode();
        $payload['created_by'] = $userId;
        $payload['last_updated_by'] = $userId;

        $experiment = ResearchExperiment::query()->create($payload);

        return response()->json([
            'data' => $experiment->fresh(),
        ], 201);
    }

    public function update(UpdateResearchExperimentRequest $request, ResearchExperiment $experiment): JsonResponse
    {
        $payload = $request->validated();
        $payload['last_updated_by'] = $request->user()?->id;

        $experiment->fill($payload)->save();

        return response()->json([
            'data' => $experiment->fresh(),
        ]);
    }

    public function destroy(ResearchExperiment $experiment): JsonResponse
    {
        DB::transaction(function () use ($experiment) {
            $sampleIds = ResearchSample::query()->where('experiment_id', $experiment->id)->pluck('id');

            ResearchMonitoringRecord::query()->whereIn('sample_id', $sampleIds)->delete();
            ResearchSample::query()->whereIn('id', $sampleIds)->delete();
            $experiment->delete();
        });

        return response()->json([
            'data' => ['id' => $experiment->id],
        ]);
    }
}
