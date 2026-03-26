<?php

namespace App\Http\Controllers\Research;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Research\GetResearchStudyRequest;
use App\Http\Requests\Research\StoreResearchStudyRequest;
use App\Http\Requests\Research\UpdateResearchStudyRequest;
use App\Models\Research\ResearchStudy;
use App\Repositories\ResearchStudyRepo;
use App\Services\Research\ResearchAccessService;
use App\Services\Research\ResearchIdentifierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class ResearchStudyController extends BaseController
{
    public function __construct(
        ResearchStudyRepo $repository,
        protected ResearchIdentifierService $identifierService,
        protected ResearchAccessService $accessService
    ) {
        $this->service = $repository;
    }

    public function index(GetResearchStudyRequest $request): Collection
    {
        return parent::_index($request);
    }

    public function store(StoreResearchStudyRequest $request): JsonResponse
    {
        $userId = $request->user()?->id;
        $payload = $this->hydrateStudyPayload($request->validated());
        $payload['code'] = $payload['code'] ?? $this->identifierService->nextStudyCode();
        $payload['created_by'] = $userId;
        $payload['last_updated_by'] = $userId;

        $study = ResearchStudy::query()->create($payload);
        $study->loadMissing('project');
        $this->accessService->syncProjectMembers($study->project);

        return response()->json([
            'data' => $study->fresh(),
        ], 201);
    }

    public function update(UpdateResearchStudyRequest $request, ResearchStudy $study): JsonResponse
    {
        $payload = $this->hydrateStudyPayload($request->validated());
        $payload['last_updated_by'] = $request->user()?->id;

        $study->fill($payload)->save();
        $study->loadMissing('project');
        $this->accessService->syncProjectMembers($study->project);

        return response()->json([
            'data' => $study->fresh(),
        ]);
    }

    public function destroy(ResearchStudy $study): JsonResponse
    {
        $study->loadMissing('project');
        $project = $study->project;
        $this->repo()->deleteCascade($study);

        if ($project) {
            $this->accessService->syncProjectMembers($project);
        }

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

    protected function hydrateStudyPayload(array $payload): array
    {
        $payload['study_leader'] = $this->accessService->personPayload($payload['study_leader_id'] ?? null);
        $payload['supervisor'] = $this->accessService->personPayload($payload['supervisor_id'] ?? null);
        $payload['staff_members'] = $this->accessService->peoplePayload($payload['staff_member_ids'] ?? []);

        unset(
            $payload['study_leader_id'],
            $payload['supervisor_id'],
            $payload['staff_member_ids'],
        );

        return $payload;
    }
}
