<?php

namespace App\Http\Controllers;

use App\Http\Requests\Laboratory\LaboratoryCheckInRequest;
use App\Http\Requests\Laboratory\LaboratoryCheckOutRequest;
use App\Http\Requests\Laboratory\LaboratoryReportLocationRequest;
use App\Http\Requests\Laboratory\LaboratoryUpdateEndUseRequest;
use App\Services\Laboratory\LaboratoryLogService;
use Illuminate\Http\JsonResponse;

class ICTEquipmentController extends Controller
{
    public function __construct(private readonly LaboratoryLogService $service)
    {
    }

    public function index(): JsonResponse
    {
        $search = request()->query('search');
        $equipment = $this->service->listEligibleEquipment($search, 'ict');

        return response()->json([
            'data' => $equipment,
        ]);
    }

    public function show(string $identifier): JsonResponse
    {
        $equipmentId = $this->service->resolveEquipmentId($identifier);
        if (!$equipmentId) {
            return response()->json([
                'message' => 'Equipment not found.',
            ], 404);
        }

        $details = $this->service->getEquipmentDetails($equipmentId, 'ict');

        return response()->json([
            'data' => $details,
        ]);
    }

    public function checkIn(LaboratoryCheckInRequest $request, string $identifier): JsonResponse
    {
        $equipmentId = $this->service->resolveEquipmentId($identifier);
        if (!$equipmentId) {
            return response()->json([
                'message' => 'Equipment not found.',
            ], 404);
        }

        $log = $this->service->checkIn($equipmentId, $request->validated(), 'ict');

        return response()->json([
            'message' => 'Equipment checked in successfully.',
            'data' => $log,
        ], 201);
    }

    public function checkOut(LaboratoryCheckOutRequest $request, string $identifier): JsonResponse
    {
        $equipmentId = $this->service->resolveEquipmentId($identifier);
        if (!$equipmentId) {
            return response()->json([
                'message' => 'Equipment not found.',
            ], 404);
        }

        $log = $this->service->checkOut($equipmentId, $request->validated(), 'ict');

        return response()->json([
            'message' => 'Equipment checked out successfully.',
            'data' => $log,
        ]);
    }

    public function updateEndUse(LaboratoryUpdateEndUseRequest $request, string $identifier): JsonResponse
    {
        $equipmentId = $this->service->resolveEquipmentId($identifier);
        if (!$equipmentId) {
            return response()->json([
                'message' => 'Equipment not found.',
            ], 404);
        }

        $log = $this->service->updateEndUse($equipmentId, $request->validated(), 'ict');

        return response()->json([
            'message' => 'Estimated end of use updated successfully.',
            'data' => $log,
        ]);
    }

    public function reportLocation(LaboratoryReportLocationRequest $request, string $identifier): JsonResponse
    {
        $equipmentId = $this->service->resolveEquipmentId($identifier);
        if (!$equipmentId) {
            return response()->json([
                'message' => 'Equipment not found.',
            ], 404);
        }

        $location = $this->service->reportTemporaryLocation($equipmentId, $request->validated(), 'ict');

        return response()->json([
            'message' => 'Temporary location saved successfully.',
            'data' => $location,
        ]);
    }

    public function activeEquipments($employee_id = null): JsonResponse
    {
        return response()->json([
            'data' => $this->service->getActiveEquipment($employee_id, 'ict'),
        ]);
    }
}
