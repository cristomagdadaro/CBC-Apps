<?php

namespace App\Http\Controllers;

use App\Events\RentalCalendarChanged;
use App\Http\Requests\CreateRentalVenueRequest;
use App\Http\Requests\UpdateRentalVenueRequest;
use App\Models\RentalVenue;
use App\Repositories\RentalVenueRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class RentalVenueController extends BaseController
{
    public function __construct(RentalVenueRepository $repository)
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
        $statuses = collect(explode(',', (string) $request->query('statuses', implode(',', RentalVenue::getStatuses()))))
            ->map(fn ($status) => trim($status))
            ->filter()
            ->values()
            ->all();

        $filters = [
            'venue_type' => $request->query('venue_type'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'statuses' => $statuses,
        ];

        $rentals = collect($this->repo()->all($filters))
            ->map(fn (RentalVenue $rental) => $this->buildPublicRentalPayload($rental))
            ->values();

        return response()->json(['data' => $rentals]);
    }

    public function store(CreateRentalVenueRequest $request): JsonResponse
    {
        $data = $request->validated();

        $dateFrom = Carbon::parse($data['date_from']);
        $dateTo = Carbon::parse($data['date_to']);

        if ($this->repo()->checkConflict(
            $data['venue_type'],
            $dateFrom,
            $dateTo,
            $data['time_from'],
            $data['time_to']
        )) {
            return response()->json([
                'message' => 'The selected venue is not available for the requested dates and time.',
                'error' => 'conflict',
            ], 409);
        }

        $data['status'] = RentalVenue::STATUS_PENDING;
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

    public function update(UpdateRentalVenueRequest $request, string $id): JsonResponse
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
            $venueType = $data['venue_type'] ?? $rental->venue_type;

            if ($this->repo()->checkConflict($venueType, $dateFrom, $dateTo, $timeFrom, $timeTo, $id)) {
                return response()->json([
                    'message' => 'The selected venue is not available for the requested dates and time.',
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
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                RentalVenue::STATUS_PENDING,
                RentalVenue::STATUS_APPROVED,
                RentalVenue::STATUS_REJECTED,
                RentalVenue::STATUS_CANCELLED,
            ])],
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
        $this->broadcastRentalChange($updated, 'status_changed');

        return response()->json(['data' => $updated]);
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

    public function checkAvailability(string $venueType, string $dateFrom, string $dateTo): JsonResponse
    {
        try {
            $from = Carbon::parse($dateFrom);
            $to = Carbon::parse($dateTo);

            $conflicts = $this->repo()->getConflicts($venueType, $from, $to);
            $isAvailable = $conflicts->isEmpty();

            $response = [
                'available' => $isAvailable,
                'venue_type' => $venueType,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'message' => $isAvailable
                    ? 'Venue is available for the selected dates.'
                    : 'Venue is not available for the selected dates.',
            ];

            if (!$isAvailable) {
                $response['conflicts'] = $conflicts->map(function (RentalVenue $rental) {
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

    public function getByVenueType(string $venueType): JsonResponse
    {
        $rentals = $this->repo()->all(['venue_type' => $venueType]);

        return response()->json(['data' => $rentals]);
    }

    protected function repo(): RentalVenueRepository
    {
        /** @var RentalVenueRepository $repository */
        $repository = $this->requireService();

        return $repository;
    }

    private function buildPublicRentalPayload(RentalVenue $rental): array
    {
        return Arr::only($rental->toArray(), [
            'id',
            'venue_type',
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

    private function broadcastRentalChange(RentalVenue $rental, string $action): void
    {
        event(new RentalCalendarChanged([
            'domain' => 'venue',
            'action' => $action,
            'id' => $rental->id,
            'resource_type' => $rental->venue_type,
            'status' => $rental->status,
            'starts_at' => $this->formatAvailabilityTimestamp($rental->date_from, $rental->time_from, false),
            'ends_at' => $this->formatAvailabilityTimestamp($rental->date_to, $rental->time_to, true),
            'invalidate' => ['rentals.calendar', 'rentals.venues'],
        ]));
    }
}
