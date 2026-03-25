<?php

namespace App\Http\Controllers\Research;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Research\StoreResearchStudyRequest;
use App\Http\Requests\Research\UpdateResearchStudyRequest;
use App\Models\Research\ResearchStudy;
use App\Repositories\ResearchStudyRepo;
use App\Services\Research\ResearchIdentifierService;
use Illuminate\Http\JsonResponse;

class ResearchStudyController extends BaseController
{
    public function __construct(
        ResearchStudyRepo $repository,
        protected ResearchIdentifierService $identifierService
    ) {
        $this->service = $repository;
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
        $this->repo()->deleteCascade($study);

        return response()->json([
            'data' => ['id' => $study->id],
        ]);
    }

    protected function repo(): ResearchStudyRepo
    {
        /** @var ResearchStudyRepo $repository */
        $repository = $this->requireService();

        return $repository;
    }
}
