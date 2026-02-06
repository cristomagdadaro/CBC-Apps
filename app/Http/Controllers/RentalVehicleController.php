<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRentalVehicleRequest;
use App\Http\Requests\UpdateRentalVehicleRequest;
use App\Repositories\RentalVehicleRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class RentalVehicleController extends BaseController
{
    protected RentalVehicleRepository $repository;

    public function __construct(RentalVehicleRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(): JsonResponse
    {
        $rentals = $this->repository->paginate(15);
        return response()->json($rentals);
    }

    public function store(CreateRentalVehicleRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Check for conflicts
        $dateFrom = Carbon::parse($data['date_from']);
        $dateTo = Carbon::parse($data['date_to']);

        if ($this->repository->checkConflict(
            $data['vehicle_type'],
            $dateFrom,
            $dateTo,
            $data['time_from'],
            $data['time_to']
        )) {
            return response()->json([
                'message' => 'The selected vehicle is not available for the requested dates and time.',
                'error' => 'conflict'
            ], 409);
        }

        $data['status'] = 'pending';
        $rental = $this->repository->create($data);

        return response()->json(['data' => $rental], 201);
    }

    public function show(string $id): JsonResponse
    {
        $rental = $this->repository->find($id);

        if (!$rental) {
            return response()->json(['message' => 'Rental not found'], 404);
        }

        return response()->json(['data' => $rental]);
    }

    public function update(UpdateRentalVehicleRequest $request, string $id): JsonResponse
    {
        $rental = $this->repository->find($id);

        if (!$rental) {
            return response()->json(['message' => 'Rental not found'], 404);
        }

        $data = $request->validated();

        // Check for conflicts if dates are being updated
        if (isset($data['date_from']) || isset($data['date_to'])) {
            $dateFrom = Carbon::parse($data['date_from'] ?? $rental->date_from);
            $dateTo = Carbon::parse($data['date_to'] ?? $rental->date_to);
            $timeFrom = $data['time_from'] ?? $rental->time_from;
            $timeTo = $data['time_to'] ?? $rental->time_to;
            $vehicleType = $data['vehicle_type'] ?? $rental->vehicle_type;

            if ($this->repository->checkConflict($vehicleType, $dateFrom, $dateTo, $timeFrom, $timeTo, $id)) {
                return response()->json([
                    'message' => 'The selected vehicle is not available for the requested dates and time.',
                    'error' => 'conflict'
                ], 409);
            }
        }

        $updated = $this->repository->update($id, $data);

        return response()->json(['data' => $updated]);
    }

    public function destroy(string $id): JsonResponse
    {
        $rental = $this->repository->find($id);

        if (!$rental) {
            return response()->json(['message' => 'Rental not found'], 404);
        }

        $this->repository->delete($id);

        return response()->json(['message' => 'Rental deleted successfully']);
    }

    public function checkAvailability(string $vehicleType, string $dateFrom, string $dateTo): JsonResponse
    {
        try {
            $from = Carbon::parse($dateFrom);
            $to = Carbon::parse($dateTo);

            $isAvailable = !$this->repository->checkConflict($vehicleType, $from, $to, '00:00', '23:59');

            return response()->json([
                'available' => $isAvailable,
                'vehicle_type' => $vehicleType,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Invalid date format',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getByVehicleType(string $vehicleType): JsonResponse
    {
        $rentals = $this->repository->all(['vehicle_type' => $vehicleType]);
        return response()->json(['data' => $rentals]);
    }
}
