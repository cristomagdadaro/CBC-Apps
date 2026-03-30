<?php

namespace App\Http\Controllers;

use App\Http\Requests\Laboratory\LaboratoryCheckInRequest;
use App\Http\Requests\Laboratory\LaboratoryCheckOutRequest;
use App\Http\Requests\Laboratory\LaboratoryReportLocationRequest;
use App\Http\Requests\Laboratory\LaboratoryUpdateEndUseRequest;
use App\Services\Laboratory\LaboratoryLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ICTEquipmentController extends BaseController
{
    public function __construct(private readonly LaboratoryLogService $logService)
    {
    }

    public function index(): JsonResponse
    {
        $search = request()->query('search');
        $equipment = $this->logService->listEligibleEquipment($search, 'ict');

        return response()->json([
            'data' => $equipment,
        ]);
    }

    public function show(string $identifier): JsonResponse
    {
        $equipmentId = $this->logService->resolveEquipmentId($identifier);
        if (!$equipmentId) {
            return response()->json([
                'message' => 'Equipment not found.',
            ], 404);
        }

        $details = $this->logService->getEquipmentDetails($equipmentId, 'ict');

        return response()->json([
            'data' => $details,
        ]);
    }

    public function checkIn(LaboratoryCheckInRequest $request, string $identifier): JsonResponse
    {
        $equipmentId = $this->logService->resolveEquipmentId($identifier);
        if (!$equipmentId) {
            return response()->json([
                'message' => 'Equipment not found.',
            ], 404);
        }

        $log = $this->logService->checkIn($equipmentId, $request->validated(), 'ict');

        return response()->json([
            'message' => 'Equipment checked in successfully.',
            'data' => $log,
        ], 201);
    }

    public function checkOut(LaboratoryCheckOutRequest $request, string $identifier): JsonResponse
    {
        $equipmentId = $this->logService->resolveEquipmentId($identifier);
        if (!$equipmentId) {
            return response()->json([
                'message' => 'Equipment not found.',
            ], 404);
        }

        $log = $this->logService->checkOut($equipmentId, $request->validated(), 'ict');

        return response()->json([
            'message' => 'Equipment checked out successfully.',
            'data' => $log,
        ]);
    }

    public function updateEndUse(LaboratoryUpdateEndUseRequest $request, string $identifier): JsonResponse
    {
        $equipmentId = $this->logService->resolveEquipmentId($identifier);
        if (!$equipmentId) {
            return response()->json([
                'message' => 'Equipment not found.',
            ], 404);
        }

        $log = $this->logService->updateEndUse($equipmentId, $request->validated(), 'ict');

        return response()->json([
            'message' => 'Estimated end of use updated successfully.',
            'data' => $log,
        ]);
    }

    public function reportLocation(LaboratoryReportLocationRequest $request, string $identifier): JsonResponse
    {
        $equipmentId = $this->logService->resolveEquipmentId($identifier);
        if (!$equipmentId) {
            return response()->json([
                'message' => 'Equipment not found.',
            ], 404);
        }

        $location = $this->logService->reportTemporaryLocation($equipmentId, $request->validated(), 'ict');

        return response()->json([
            'message' => 'Temporary location saved successfully.',
            'data' => $location,
        ]);
    }

    public function activeEquipments(Request $request, ?string $employee_id = null): JsonResponse
    {
        $this->logService->markOverdue();

        return response()->json([
            'data' => $this->logService->getActiveEquipment($this->resolveActiveEmployeeFilter($request, $employee_id), 'ict'),
        ]);
    }

    private function resolveActiveEmployeeFilter(Request $request, ?string $requestedEmployeeId): ?string
    {
        $user = $request->user();

        if ($user?->is_admin) {
            return $requestedEmployeeId;
        }

        $employeeId = $user?->employee_id;

        if (! $employeeId) {
            abort(422, 'The authenticated user is not linked to a personnel record.');
        }

        return $employeeId;
    }
}
