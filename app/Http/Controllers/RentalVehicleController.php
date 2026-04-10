<?php

namespace App\Http\Controllers;

use App\Events\RentalCalendarChanged;
use App\Http\Requests\CreateRentalVehicleRequest;
use App\Http\Requests\UpdateRentalVehicleRequest;
use App\Models\RentalVehicle;
use App\Repositories\OptionRepo;
use App\Repositories\RentalVehicleRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

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
        $statuses = collect(explode(',', (string) $request->query('statuses', implode(',', RentalVehicle::getStatuses()))))
            ->map(fn ($status) => trim($status))
            ->filter()
            ->values()
            ->all();

        $filters = [
            'vehicle_type' => $request->query('vehicle_type'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'statuses' => $statuses,
        ];

        $rentals = collect($this->repo()->all($filters))
            ->map(fn (RentalVehicle $rental) => $this->buildPublicRentalPayload($rental))
            ->values();

        return response()->json(['data' => $rentals]);
    }

    public function store(CreateRentalVehicleRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (!empty($data['vehicle_type'])) {
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
                    'error' => 'conflict',
                ], 409);
            }
        }

        $data['vehicle_type'] = $data['vehicle_type'] ?? null;
        $data['status'] = RentalVehicle::STATUS_PENDING;
        $rental = $this->repo()->create($data);
        $this->broadcastRentalChange($rental, 'created');

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

        return response()->json([
            'data' => $this->buildPublicRentalPayload($rental),
        ]);
    }

    public function update(UpdateRentalVehicleRequest $request, string $id): JsonResponse
    {
        $rental = $this->repo()->find($id);

        if (!$rental) {
            return response()->json(['message' => 'Rental not found'], 404);
        }

        $data = $request->validated();

        if (isset($data['date_from']) || isset($data['date_to'])) {
            $dateFrom = Carbon::parse($data['date_from'] ?? $rental->date_from);
            $dateTo = Carbon::parse($data['date_to'] ?? $rental->date_to);
            $timeFrom = $data['time_from'] ?? $rental->time_from;
            $timeTo = $data['time_to'] ?? $rental->time_to;
            $vehicleType = $data['vehicle_type'] ?? $rental->vehicle_type;

            if ($vehicleType && $this->repo()->checkConflict($vehicleType, $dateFrom, $dateTo, $timeFrom, $timeTo, $id)) {
                return response()->json([
                    'message' => 'The selected vehicle is not available for the requested dates and time.',
                    'error' => 'conflict',
                ], 409);
            }
        }

        $updated = $this->repo()->update($id, $data);
        $this->broadcastRentalChange($updated, 'updated');

        return response()->json(['data' => $updated]);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $vehicleTypes = collect(app(OptionRepo::class)->getVehicles())
            ->pluck('name')
            ->filter()
            ->values()
            ->all();

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                RentalVehicle::STATUS_PENDING,
                RentalVehicle::STATUS_APPROVED,
                RentalVehicle::STATUS_REJECTED,
                RentalVehicle::STATUS_CANCELLED,
            ])],
            'vehicle_type' => array_values(array_filter([
                Rule::requiredIf(fn () => $request->input('status') === RentalVehicle::STATUS_APPROVED),
                'nullable',
                'string',
                !empty($vehicleTypes) ? Rule::in($vehicleTypes) : null,
            ])),
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $rental = $this->repo()->find($id);

        if (!$rental) {
            return response()->json(['message' => 'Rental not found'], 404);
        }

        $vehicleType = $validated['vehicle_type'] ?? $rental->vehicle_type;

        if ($validated['status'] === RentalVehicle::STATUS_APPROVED) {
            $dateFrom = Carbon::parse($rental->date_from);
            $dateTo = Carbon::parse($rental->date_to);

            if ($this->repo()->checkConflict(
                $vehicleType,
                $dateFrom,
                $dateTo,
                (string) $rental->time_from,
                (string) $rental->time_to,
                $id
            )) {
                return response()->json([
                    'message' => 'The selected vehicle is not available for this trip schedule.',
                    'error' => 'conflict',
                ], 409);
            }
        }

        $updated = $this->repo()->update($id, [
            'status' => $validated['status'],
            'vehicle_type' => $vehicleType,
            'notes' => $validated['notes'] ?? $rental->notes,
        ]);
        $this->broadcastRentalChange($updated, 'status_changed');

        return response()->json(['data' => $this->repo()->find((string) $updated->id)]);
    }

    public function destroy(string $id): JsonResponse
    {
        $rental = $this->repo()->find($id);

        if (!$rental) {
            return response()->json(['message' => 'Rental not found'], 404);
        }

        $this->repo()->delete($id);
        $this->broadcastRentalChange($rental, 'deleted');

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
                'message' => $isAvailable
                    ? 'Vehicle is available for the selected dates.'
                    : 'Vehicle is not available for the selected dates.',
            ];

            if (!$isAvailable) {
                $response['conflicts'] = $conflicts->map(function (RentalVehicle $rental) {
                    return $this->buildPublicAvailabilityWindow(
                        $rental->date_from,
                        $rental->time_from,
                        $rental->date_to,
                        $rental->time_to,
                        $rental->status,
                    );
                })->values();
            }

            return response()->json($response);
        } catch (\Exception) {
            return response()->json([
                'message' => 'Invalid date format',
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

    private function buildPublicRentalPayload(RentalVehicle $rental): array
    {
        return Arr::only($rental->toArray(), [
            'id',
            'requested_by',
            'vehicle_type',
            'trip_type',
            'date_from',
            'date_to',
            'time_from',
            'time_to',
            'status',
        ]);
    }

    private function buildPublicAvailabilityWindow(?string $dateFrom, ?string $timeFrom, ?string $dateTo, ?string $timeTo, ?string $status): array
    {
        return [
            'starts_at' => $this->formatAvailabilityTimestamp($dateFrom, $timeFrom, false),
            'ends_at' => $this->formatAvailabilityTimestamp($dateTo, $timeTo, true),
            'status' => $status,
        ];
    }

    private function formatAvailabilityTimestamp(?string $date, ?string $time, bool $endOfDay): ?string
    {
        if (!$date) {
            return null;
        }

        $resolvedTime = $time ?: ($endOfDay ? '23:59:59' : '00:00:00');

        return Carbon::parse($date . ' ' . $resolvedTime)->toIso8601String();
    }

    private function broadcastRentalChange(RentalVehicle $rental, string $action): void
    {
        event(new RentalCalendarChanged([
            'domain' => 'vehicle',
            'action' => $action,
            'id' => $rental->id,
            'resource_type' => $rental->vehicle_type,
            'status' => $rental->status,
            'starts_at' => $this->formatAvailabilityTimestamp($rental->date_from, $rental->time_from, false),
            'ends_at' => $this->formatAvailabilityTimestamp($rental->date_to, $rental->time_to, true),
            'invalidate' => ['rentals.calendar', 'rentals.vehicles'],
        ]));
    }
}
