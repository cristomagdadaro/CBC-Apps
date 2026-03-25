<?php

namespace App\Http\Controllers\Research;

use App\Http\Controllers\Controller;
use App\Http\Requests\Research\StoreResearchMonitoringRecordRequest;
use App\Http\Requests\Research\UpdateResearchMonitoringRecordRequest;
use App\Models\Research\ResearchMonitoringRecord;
use Illuminate\Http\JsonResponse;

class ResearchMonitoringRecordController extends Controller
{
    public function store(StoreResearchMonitoringRecordRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['recorded_by'] = $request->user()?->id;

        $record = ResearchMonitoringRecord::query()->create($payload);

        return response()->json([
            'data' => $record->fresh(),
        ], 201);
    }

    public function update(UpdateResearchMonitoringRecordRequest $request, ResearchMonitoringRecord $record): JsonResponse
    {
        $record->fill($request->validated())->save();

        return response()->json([
            'data' => $record->fresh(),
        ]);
    }

    public function destroy(ResearchMonitoringRecord $record): JsonResponse
    {
        $record->delete();

        return response()->json([
            'data' => ['id' => $record->id],
        ]);
    }
}
