<?php

namespace App\Http\Controllers\Research;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Research\StoreResearchMonitoringRecordRequest;
use App\Http\Requests\Research\UpdateResearchMonitoringRecordRequest;
use App\Models\Research\ResearchMonitoringRecord;
use App\Repositories\ResearchMonitoringRecordRepo;
use Illuminate\Http\JsonResponse;

class ResearchMonitoringRecordController extends BaseController
{
    public function __construct(ResearchMonitoringRecordRepo $repository)
    {
        $this->service = $repository;
    }

    public function store(StoreResearchMonitoringRecordRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['recorded_by'] = $request->user()?->id;

        $record = $this->repo()->create($payload);

        return response()->json([
            'data' => $record->fresh(),
        ], 201);
    }

    public function update(UpdateResearchMonitoringRecordRequest $request, ResearchMonitoringRecord $record): JsonResponse
    {
        $record = $this->repo()->update($record->id, $request->validated());

        return response()->json([
            'data' => $record->fresh(),
        ]);
    }

    public function destroy(ResearchMonitoringRecord $record): JsonResponse
    {
        $this->repo()->delete($record->id);

        return response()->json([
            'data' => ['id' => $record->id],
        ]);
    }

    protected function repo(): ResearchMonitoringRecordRepo
    {
        /** @var ResearchMonitoringRecordRepo $repository */
        $repository = $this->requireService();

        return $repository;
    }
}
