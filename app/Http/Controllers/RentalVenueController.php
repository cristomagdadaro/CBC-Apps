<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRentalVenueRequest;
use App\Http\Requests\UpdateRentalVenueRequest;
use App\Repositories\RentalVenueRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class RentalVenueController extends BaseController
{
    protected RentalVenueRepository $repository;

    public function __construct(RentalVenueRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(): JsonResponse
    {
        $rentals = $this->repository->paginate(15);
        return response()->json($rentals);
    }

    public function store(CreateRentalVenueRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Check for conflicts
        $dateFrom = Carbon::parse($data['date_from']);
        $dateTo = Carbon::parse($data['date_to']);

        if ($this->repository->checkConflict(
            $data['venue_type'],
            $dateFrom,
            $dateTo,
            $data['time_from'],
            $data['time_to']
        )) {
            return response()->json([
                'message' => 'The selected venue is not available for the requested dates and time.',
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

    public function update(UpdateRentalVenueRequest $request, string $id): JsonResponse
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
            $venueType = $data['venue_type'] ?? $rental->venue_type;

            if ($this->repository->checkConflict($venueType, $dateFrom, $dateTo, $timeFrom, $timeTo, $id)) {
                return response()->json([
                    'message' => 'The selected venue is not available for the requested dates and time.',
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

    public function checkAvailability(string $venueType, string $dateFrom, string $dateTo): JsonResponse
    {
        try {
            $from = Carbon::parse($dateFrom);
            $to = Carbon::parse($dateTo);

            $isAvailable = !$this->repository->checkConflict($venueType, $from, $to, '00:00', '23:59');

            return response()->json([
                'available' => $isAvailable,
                'venue_type' => $venueType,
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

    public function getByVenueType(string $venueType): JsonResponse
    {
        $rentals = $this->repository->all(['venue_type' => $venueType]);
        return response()->json(['data' => $rentals]);
    }
}
