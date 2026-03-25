<?php

namespace App\Http\Controllers\Research;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Research\StoreResearchSampleRequest;
use App\Http\Requests\Research\UpdateResearchSampleRequest;
use App\Models\Research\ResearchExperiment;
use App\Models\Research\ResearchSample;
use App\Repositories\ResearchSampleRepo;
use App\Services\Research\ResearchIdentifierService;
use Illuminate\Http\JsonResponse;

class ResearchSampleController extends BaseController
{
    public function __construct(
        ResearchSampleRepo $repository,
        protected ResearchIdentifierService $identifierService
    ) {
        $this->service = $repository;
    }

    public function store(StoreResearchSampleRequest $request): JsonResponse
    {
        $userId = $request->user()?->id;
        $payload = $request->validated();

        /** @var ResearchExperiment $experiment */
        $experiment = ResearchExperiment::query()->findOrFail($payload['experiment_id']);
        $identity = $this->identifierService->nextSampleIdentity($experiment, $payload['commodity'] ?? null);

        $payload['uid'] = $identity['uid'];
        $payload['sequence_number'] = $identity['sequence_number'];
        $payload['created_by'] = $userId;
        $payload['last_updated_by'] = $userId;

        $sample = ResearchSample::query()->create($payload);

        return response()->json([
            'data' => $sample->fresh(['monitoringRecords']),
        ], 201);
    }

    public function update(UpdateResearchSampleRequest $request, ResearchSample $sample): JsonResponse
    {
        $payload = $request->validated();
        $payload['last_updated_by'] = $request->user()?->id;

        $sample->fill($payload)->save();

        return response()->json([
            'data' => $sample->fresh(['monitoringRecords']),
        ]);
    }

    public function destroy(ResearchSample $sample): JsonResponse
    {
        $this->repo()->deleteCascade($sample);

        return response()->json([
            'data' => ['id' => $sample->id],
        ]);
    }

    protected function repo(): ResearchSampleRepo
    {
        /** @var ResearchSampleRepo $repository */
        $repository = $this->requireService();

        return $repository;
    }
}
