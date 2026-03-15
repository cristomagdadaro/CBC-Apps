<?php
namespace App\Http\Controllers;

use App\Http\Requests\Generic\GetRequest;
use App\Http\Requests\Laboratory\LaboratoryCheckInRequest;
use App\Http\Requests\Laboratory\LaboratoryCheckOutRequest;
use App\Http\Requests\Laboratory\LaboratoryReportLocationRequest;
use App\Http\Requests\Laboratory\LaboratoryUpdateEndUseRequest;
use App\Repositories\LaboratoryEquipmentLogRepo;
use App\Services\Laboratory\LaboratoryLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LaboratoryEquipmentController extends BaseController
{
    private readonly LaboratoryLogService $logService;

    public function __construct(
        LaboratoryLogService $logService,
        LaboratoryEquipmentLogRepo $logRepo,
    ) {
        $this->logService = $logService;
        $this->service = $logRepo;
    }

    public function index(): JsonResponse
    {
        $search    = request()->query('search');
        $equipment = $this->logService->listEligibleEquipment($search);

        return response()->json([
            'data' => $equipment,
        ]);
    }

    public function show(string $identifier): JsonResponse
    {
        $equipmentId = $this->logService->resolveEquipmentId($identifier);
        if (! $equipmentId) {
            return response()->json([
                'message' => 'Equipment not found.',
            ], 404);
        }

        $details = $this->logService->getEquipmentDetails($equipmentId);

        return response()->json([
            'data' => $details,
        ]);
    }

    public function checkIn(LaboratoryCheckInRequest $request, string $identifier): JsonResponse
    {
        $equipmentId = $this->logService->resolveEquipmentId($identifier);
        if (! $equipmentId) {
            return response()->json([
                'message' => 'Equipment not found.',
            ], 404);
        }

        $log = $this->logService->checkIn($equipmentId, $request->validated());

        return response()->json([
            'message' => 'Equipment checked in successfully.',
            'data'    => $log,
        ], 201);
    }

    public function checkOut(LaboratoryCheckOutRequest $request, string $identifier): JsonResponse
    {
        $equipmentId = $this->logService->resolveEquipmentId($identifier);
        if (! $equipmentId) {
            return response()->json([
                'message' => 'Equipment not found.',
            ], 404);
        }

        $log = $this->logService->checkOut($equipmentId, $request->validated());

        return response()->json([
            'message' => 'Equipment checked out successfully.',
            'data'    => $log,
        ]);
    }

    public function updateEndUse(LaboratoryUpdateEndUseRequest $request, string $identifier): JsonResponse
    {
        $equipmentId = $this->logService->resolveEquipmentId($identifier);
        if (! $equipmentId) {
            return response()->json([
                'message' => 'Equipment not found.',
            ], 404);
        }

        $log = $this->logService->updateEndUse($equipmentId, $request->validated());

        return response()->json([
            'message' => 'Estimated end of use updated successfully.',
            'data'    => $log,
        ]);
    }

    public function reportLocation(LaboratoryReportLocationRequest $request, string $identifier): JsonResponse
    {
        $equipmentId = $this->logService->resolveEquipmentId($identifier);
        if (! $equipmentId) {
            return response()->json([
                'message' => 'Equipment not found.',
            ], 404);
        }

        $location = $this->logService->reportTemporaryLocation($equipmentId, $request->validated());

        return response()->json([
            'message' => 'Temporary location saved successfully.',
            'data'    => $location,
        ]);
    }

    public function dashboard(): JsonResponse
    {
        return response()->json([
            'data' => $this->logService->getDashboardMetrics(),
        ]);
    }

    public function activeEquipments($employee_id = null): JsonResponse
    {
        return response()->json([
            'data' => $this->logService->getActiveEquipment($employee_id),
        ]);
    }

    public function logs(GetRequest $request): Collection
    {
        $result = $this->logRepo()->search(new Collection($request->validated()));

        if (is_array($result) && isset($result['data']) && $result['data'] instanceof Collection) {
            $this->logService->enrichLogsWithLocationDetails($result['data']);
            return new Collection($result);
        }

        if ($result instanceof LengthAwarePaginator) {
            $collection = $result->getCollection();
            $this->logService->enrichLogsWithLocationDetails($collection);
            $result->setCollection($collection);
            return new Collection($result);
        }

        if ($result instanceof Collection) {
            $this->logService->enrichLogsWithLocationDetails($result);
            return $result;
        }

        return new Collection($result);
    }

    private function logRepo(): LaboratoryEquipmentLogRepo
    {
        return $this->requireService();
    }
}
