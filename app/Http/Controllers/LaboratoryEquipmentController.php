<?php

namespace App\Http\Controllers;

use App\Http\Requests\Laboratory\LaboratoryCheckInRequest;
use App\Http\Requests\Laboratory\LaboratoryCheckOutRequest;
use App\Http\Requests\Generic\GetRequest;
use App\Repositories\LaboratoryEquipmentLogRepo;
use App\Services\Laboratory\LaboratoryLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LaboratoryEquipmentController extends Controller
{
    public function __construct(
        private readonly LaboratoryLogService $service,
        private readonly LaboratoryEquipmentLogRepo $logRepo,
    )
    {
    }

    public function index(): JsonResponse
    {
        $search = request()->query('search');
        $equipment = $this->service->listEligibleEquipment($search);

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

        $details = $this->service->getEquipmentDetails($equipmentId);

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

        $log = $this->service->checkIn($equipmentId, $request->validated());

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

        $log = $this->service->checkOut($equipmentId, $request->validated());

        return response()->json([
            'message' => 'Equipment checked out successfully.',
            'data' => $log,
        ]);
    }

    public function dashboard(): JsonResponse
    {
        return response()->json([
            'data' => $this->service->getDashboardMetrics(),
        ]);
    }

    public function activeEquipments($employee_id = null): JsonResponse
    {
        return response()->json([
            'data' => $this->service->getActiveEquipment($employee_id),
        ]);
    }

    public function logs(GetRequest $request): Collection
    {
        $result = $this->logRepo->search(new Collection($request->validated()));

        if (is_array($result) && isset($result['data']) && $result['data'] instanceof Collection) {
            $this->service->enrichLogsWithLocationDetails($result['data']);
            return new Collection($result);
        }

        if ($result instanceof LengthAwarePaginator) {
            $collection = $result->getCollection();
            $this->service->enrichLogsWithLocationDetails($collection);
            $result->setCollection($collection);
            return new Collection($result);
        }

        if ($result instanceof Collection) {
            $this->service->enrichLogsWithLocationDetails($result);
            return $result;
        }

        return new Collection($result);
    }
}
