<?php

namespace App\Http\Controllers\Research;

use App\Http\Controllers\Controller;
use App\Http\Requests\Research\StoreResearchProjectRequest;
use App\Http\Requests\Research\UpdateResearchProjectRequest;
use App\Models\Research\ResearchExperiment;
use App\Models\Research\ResearchMonitoringRecord;
use App\Models\Research\ResearchProject;
use App\Models\Research\ResearchSample;
use App\Models\Research\ResearchStudy;
use App\Services\Research\ResearchIdentifierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ResearchProjectController extends Controller
{
    public function __construct(
        protected ResearchIdentifierService $identifierService
    ) {
    }

    public function store(StoreResearchProjectRequest $request): JsonResponse
    {
        $userId = $request->user()?->id;
        $payload = $request->validated();
        $payload['code'] = $payload['code'] ?? $this->identifierService->nextProjectCode();
        $payload['created_by'] = $userId;
        $payload['last_updated_by'] = $userId;

        $project = ResearchProject::query()->create($payload);

        return response()->json([
            'data' => $project,
        ], 201);
    }

    public function update(UpdateResearchProjectRequest $request, ResearchProject $project): JsonResponse
    {
        $payload = $request->validated();
        $payload['last_updated_by'] = $request->user()?->id;

        $project->fill($payload)->save();

        return response()->json([
            'data' => $project->fresh(),
        ]);
    }

    public function destroy(ResearchProject $project): JsonResponse
    {
        DB::transaction(function () use ($project) {
            $studyIds = ResearchStudy::query()->where('project_id', $project->id)->pluck('id');
            $experimentIds = ResearchExperiment::query()->whereIn('study_id', $studyIds)->pluck('id');
            $sampleIds = ResearchSample::query()->whereIn('experiment_id', $experimentIds)->pluck('id');

            ResearchMonitoringRecord::query()->whereIn('sample_id', $sampleIds)->delete();
            ResearchSample::query()->whereIn('id', $sampleIds)->delete();
            ResearchExperiment::query()->whereIn('id', $experimentIds)->delete();
            ResearchStudy::query()->whereIn('id', $studyIds)->delete();
            $project->delete();
        });

        return response()->json([
            'data' => ['id' => $project->id],
        ]);
    }
}
