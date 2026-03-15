<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRentalVehicleRequest;
use App\Http\Requests\UpdateRentalVehicleRequest;
use App\Repositories\RentalVehicleRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RentalVehicleController extends BaseController
{
    public function __construct(RentalVehicleRepository $repository)
    {
        $this->service = $repository;
    }

    public function index(Request $request): JsonResponse
    {
        $rentals = $this->repo()->paginate(
            $request->only(['search', 'filter', 'is_exact', 'sort', 'order', 'page', 'per_page']),
            (int) $request->query('per_page', 15)
        );
        return response()->json($rentals);
    }

    public function publicIndex(Request $request): JsonResponse
    {
        $statuses = collect(explode(',', (string) $request->query('statuses', 'pending,approved,rejected')))
            ->map(fn ($status) => trim($status))
            ->filter()
            ->values()
            ->all();

        $filters = [
            'vehicle_type' => $request->query('vehicle_type'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ];

        $rentals = collect($this->repo()->all($filters))
            ->when(!empty($statuses), fn ($items) => $items->whereIn('status', $statuses))
            ->values();

        return response()->json(['data' => $rentals]);
    }

    public function store(CreateRentalVehicleRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Check for conflicts
        $dateFrom = Carbon::parse($data['date_from']);
        $dateTo = Carbon::parse($data['date_to']);

        if ($this->repo()->checkConflict(
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
        $rental = $this->repo()->create($data);

        return response()->json(['data' => $rental], 201);
    }

    public function show(string $id): JsonResponse
    {
        $rental = $this->repo()->find($id);

        if (!$rental) {
            return response()->json(['message' => 'Rental not found'], 404);
        }

        return response()->json(['data' => $rental]);
    }

    public function publicShow(string $id): JsonResponse
    {
        $rental = $this->repo()->find($id);

        if (!$rental) {
            return response()->json(['message' => 'Rental not found'], 404);
        }

        return response()->json(['data' => $rental]);
    }

    public function update(UpdateRentalVehicleRequest $request, string $id): JsonResponse
    {
        $rental = $this->repo()->find($id);

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

            if ($this->repo()->checkConflict($vehicleType, $dateFrom, $dateTo, $timeFrom, $timeTo, $id)) {
                return response()->json([
                    'message' => 'The selected vehicle is not available for the requested dates and time.',
                    'error' => 'conflict'
                ], 409);
            }
        }

        $updated = $this->repo()->update($id, $data);

        return response()->json(['data' => $updated]);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $rental = $this->repo()->find($id);

        if (!$rental) {
            return response()->json(['message' => 'Rental not found'], 404);
        }

        $updated = $this->repo()->update($id, [
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $rental->notes,
        ]);

        return response()->json(['data' => $updated]);
    }

    public function destroy(string $id): JsonResponse
    {
        $rental = $this->repo()->find($id);

        if (!$rental) {
            return response()->json(['message' => 'Rental not found'], 404);
        }

        $this->repo()->delete($id);

        return response()->json(['message' => 'Rental deleted successfully']);
    }

    public function checkAvailability(string $vehicleType, string $dateFrom, string $dateTo): JsonResponse
    {
        try {
            $from = Carbon::parse($dateFrom);
            $to = Carbon::parse($dateTo);

            $conflicts = $this->repo()->getConflicts($vehicleType, $from, $to);
            $isAvailable = $conflicts->isEmpty();

            $response = [
                'available' => $isAvailable,
                'vehicle_type' => $vehicleType,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ];

            if ($isAvailable) {
                $response['message'] = 'Vehicle is available for the selected dates.';
            } else {
                // Build descriptive message about conflicts
                $conflictMessages = $conflicts->map(function ($rental) {
                    return "Booked by {$rental->requested_by} from {$rental->date_from} to {$rental->date_to}";
                })->implode('; ');
                $response['message'] = "Vehicle is not available for the selected dates. Conflicts: {$conflictMessages}";
                $response['conflicts'] = $conflicts->map(function ($rental) {
                    return [
                        'requested_by' => $rental->requested_by,
                        'date_from' => $rental->date_from,
                        'date_to' => $rental->date_to,
                        'time_from' => $rental->time_from,
                        'time_to' => $rental->time_to,
                        'status' => $rental->status,
                    ];
                });
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Invalid date format',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getByVehicleType(string $vehicleType): JsonResponse
    {
        $rentals = $this->repo()->all(['vehicle_type' => $vehicleType]);
        return response()->json(['data' => $rentals]);
    }

    protected function repo(): RentalVehicleRepository
    {
        /** @var RentalVehicleRepository $repository */
        $repository = $this->requireService();

        return $repository;
    }
}
