<?php

namespace App\Http\Controllers\Research;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Research\GetResearchExperimentRequest;
use App\Http\Requests\Research\StoreResearchExperimentRequest;
use App\Http\Requests\Research\UpdateResearchExperimentRequest;
use App\Models\Research\ResearchExperiment;
use App\Repositories\ResearchExperimentRepo;
use App\Services\Research\ResearchIdentifierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class ResearchExperimentController extends BaseController
{
    public function __construct(
        ResearchExperimentRepo $repository,
        protected ResearchIdentifierService $identifierService
    ) {
        $this->service = $repository;
    }

    public function index(GetResearchExperimentRequest $request): Collection
    {
        return parent::_index($request);
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
        $this->repo()->deleteCascade($experiment);

        return response()->json([
            'data' => ['id' => $experiment->id],
        ]);
    }

    protected function repo(): ResearchExperimentRepo
    {
        /** @var ResearchExperimentRepo $repository */
        $repository = $this->requireService();

        return $repository;
    }
}
