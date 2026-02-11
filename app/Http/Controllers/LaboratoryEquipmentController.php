<?php

namespace App\Http\Controllers;

use App\Http\Requests\Laboratory\LaboratoryCheckInRequest;
use App\Http\Requests\Laboratory\LaboratoryCheckOutRequest;
use App\Services\Laboratory\LaboratoryLogService;
use Illuminate\Http\JsonResponse;

class LaboratoryEquipmentController extends Controller
{
    public function __construct(private readonly LaboratoryLogService $service)
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

    public function show(string $equipmentId): JsonResponse
    {
        $details = $this->service->getEquipmentDetails($equipmentId);

        if (!$details['equipment']) {
            return response()->json([
                'message' => 'Equipment not found or not eligible for laboratory logs.',
            ], 404);
        }

        return response()->json([
            'data' => $details,
        ]);
    }

    public function checkIn(LaboratoryCheckInRequest $request, string $equipmentId): JsonResponse
    {
        $log = $this->service->checkIn($equipmentId, $request->validated());

        return response()->json([
            'message' => 'Equipment checked in successfully.',
            'data' => $log,
        ], 201);
    }

    public function checkOut(LaboratoryCheckOutRequest $request, string $equipmentId): JsonResponse
    {
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
}
