<?php

namespace App\Http\Controllers\Research;

use App\Http\Controllers\Controller;
use App\Http\Requests\Research\StoreResearchStudyRequest;
use App\Http\Requests\Research\UpdateResearchStudyRequest;
use App\Models\Research\ResearchExperiment;
use App\Models\Research\ResearchMonitoringRecord;
use App\Models\Research\ResearchSample;
use App\Models\Research\ResearchStudy;
use App\Services\Research\ResearchIdentifierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ResearchStudyController extends Controller
{
    public function __construct(
        protected ResearchIdentifierService $identifierService
    ) {
    }

    public function store(StoreResearchStudyRequest $request): JsonResponse
    {
        $userId = $request->user()?->id;
        $payload = $request->validated();
        $payload['code'] = $payload['code'] ?? $this->identifierService->nextStudyCode();
        $payload['created_by'] = $userId;
        $payload['last_updated_by'] = $userId;

        $study = ResearchStudy::query()->create($payload);

        return response()->json([
            'data' => $study->fresh(),
        ], 201);
    }

    public function update(UpdateResearchStudyRequest $request, ResearchStudy $study): JsonResponse
    {
        $payload = $request->validated();
        $payload['last_updated_by'] = $request->user()?->id;

        $study->fill($payload)->save();

        return response()->json([
            'data' => $study->fresh(),
        ]);
    }

    public function destroy(ResearchStudy $study): JsonResponse
    {
        DB::transaction(function () use ($study) {
            $experimentIds = ResearchExperiment::query()->where('study_id', $study->id)->pluck('id');
            $sampleIds = ResearchSample::query()->whereIn('experiment_id', $experimentIds)->pluck('id');

            ResearchMonitoringRecord::query()->whereIn('sample_id', $sampleIds)->delete();
            ResearchSample::query()->whereIn('id', $sampleIds)->delete();
            ResearchExperiment::query()->whereIn('id', $experimentIds)->delete();
            $study->delete();
        });

        return response()->json([
            'data' => ['id' => $study->id],
        ]);
    }
}
