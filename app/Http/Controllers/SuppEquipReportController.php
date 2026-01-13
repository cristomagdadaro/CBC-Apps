<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSuppEquipReportRequest;
use App\Http\Requests\GetSuppEquipReportRequest;
use App\Http\Requests\UpdateSuppEquipReportRequest;
use App\Models\Transaction;
use App\Repositories\SuppEquipReportRepo;
use Illuminate\Http\JsonResponse;

class SuppEquipReportController extends BaseController
{
    public function __construct(SuppEquipReportRepo $repository)
    {
        $this->service = $repository;
    }

    public function index(GetSuppEquipReportRequest $request)
    {
        return parent::_index($request);
    }

    public function store(CreateSuppEquipReportRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $transaction = Transaction::with('item')->findOrFail($validated['transaction_id']);

        $payload = array_merge($validated, [
            'item_id' => $transaction->item_id,
            'reported_at' => $validated['reported_at'] ?? now(),
            'user_id' => $request->user()?->id,
        ]);

        $report = $this->service->create($payload);

        return response()->json(['data' => $report], 201);
    }

    public function update(UpdateSuppEquipReportRequest $request, string $id): JsonResponse
    {
        $validated = $request->validated();
        $transaction = Transaction::with('item')->findOrFail($validated['transaction_id']);
        $validated['item_id'] = $transaction->item_id;

        $report = $this->service->update($id, $validated);

        return response()->json(['data' => $report]);
    }

    public function destroy(string $id): JsonResponse
    {
        $report = $this->service->delete($id);

        return response()->json(['data' => $report]);
    }

    public function templates(): JsonResponse
    {
        return response()->json([
            'data' => config('suppequipreportforms'),
        ]);
    }
}
