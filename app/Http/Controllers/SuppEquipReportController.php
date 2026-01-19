<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSuppEquipReportRequest;
use App\Http\Requests\GetSuppEquipReportRequest;
use App\Http\Requests\UpdateSuppEquipReportRequest;
use App\Repositories\SuppEquipReportRepo;
use Illuminate\Http\JsonResponse;

class SuppEquipReportController extends BaseController
{
    public function __construct(SuppEquipReportRepo $repository)
    {
        $this->service = $repository;
    }

    protected function repo(): SuppEquipReportRepo
    {
        return $this->service;
    }

    public function index(GetSuppEquipReportRequest $request)
    {
        return parent::_index($request);
    }

    public function store(CreateSuppEquipReportRequest $request): JsonResponse
    {
        $report = $this->repo()->createWithTransaction(
            $request->validated(),
            $request->user()?->id
        );

        return response()->json(['data' => $report], 201);
    }

    public function update(UpdateSuppEquipReportRequest $request, string $id): JsonResponse
    {
        $report = $this->repo()->updateWithTransaction($id, $request->validated());

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
