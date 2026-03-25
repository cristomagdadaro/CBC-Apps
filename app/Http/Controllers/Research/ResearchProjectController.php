<?php

namespace App\Http\Controllers\Research;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Research\StoreResearchProjectRequest;
use App\Http\Requests\Research\UpdateResearchProjectRequest;
use App\Models\Research\ResearchProject;
use App\Repositories\ResearchProjectRepo;
use App\Services\Research\ResearchIdentifierService;
use Illuminate\Http\JsonResponse;

class ResearchProjectController extends BaseController
{
    public function __construct(
        ResearchProjectRepo $repository,
        protected ResearchIdentifierService $identifierService
    ) {
        $this->service = $repository;
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
        $this->repo()->deleteCascade($project);

        return response()->json([
            'data' => ['id' => $project->id],
        ]);
    }

    protected function repo(): ResearchProjectRepo
    {
        /** @var ResearchProjectRepo $repository */
        $repository = $this->requireService();

        return $repository;
    }
}
