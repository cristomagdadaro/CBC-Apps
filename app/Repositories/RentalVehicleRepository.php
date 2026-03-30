<?php

namespace App\Repositories;

use App\Models\RentalVehicle;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RentalVehicleRepository extends AbstractRepoService
{
    public function __construct(RentalVehicle $model)
    {
        parent::__construct($model);
    }

    public function all(array $filters = [])
    {
        $query = $this->model->newQuery();

        if (!empty($filters['vehicle_type'])) {
            $query->where('vehicle_type', $filters['vehicle_type']);
        }

        if (!empty($filters['trip_type'])) {
            $query->where('trip_type', $filters['trip_type']);
        }

        if (!empty($filters['statuses'])) {
            $query->whereIn('status', (array) $filters['statuses']);
        } elseif (!empty($filters['status'])) {
            $statusFilter = $filters['status'];
            is_array($statusFilter)
                ? $query->whereIn('status', $statusFilter)
                : $query->where('status', $statusFilter);
        }

        if (!empty($filters['date_from'])) {
            $query->where('date_from', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('date_to', '<=', $filters['date_to']);
        }

        return $this->syncLifecycleStatuses(
            $query->orderBy('date_from', 'asc')
                ->orderBy('time_from', 'asc')
                ->get()
        );
    }

    public function paginate(array $params = [], int $perPage = 15)
    {
        $query = $this->model->newQuery();

        $search = trim((string) ($params['search'] ?? ''));
        $filter = $params['filter'] ?? null;
        $isExact = filter_var($params['is_exact'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $sort = $params['sort'] ?? 'date_from';
        $order = strtolower((string) ($params['order'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $page = max((int) ($params['page'] ?? 1), 1);
        $perPageValue = (int) ($params['per_page'] ?? $perPage);
        $perPageValue = $perPageValue > 0 ? $perPageValue : $perPage;

        $filterableColumns = [
            'id',
            'vehicle_type',
            'trip_type',
            'requested_by',
            'contact_number',
            'date_from',
            'date_to',
            'status',
            'purpose',
            'destination_location',
            'destination_city',
            'destination_province',
            'destination_region',
            'notes',
        ];

        if (!empty($search)) {
            $operator = $isExact ? '=' : 'like';
            $searchValue = $isExact ? $search : "%{$search}%";

            if ($filter && in_array($filter, $filterableColumns, true)) {
                $query->where($filter, $operator, $searchValue);
            } else {
                $query->where(function ($subQuery) use ($searchValue) {
                    $subQuery->where('vehicle_type', 'like', $searchValue)
                        ->orWhere('trip_type', 'like', $searchValue)
                        ->orWhere('requested_by', 'like', $searchValue)
                        ->orWhere('purpose', 'like', $searchValue)
                        ->orWhere('status', 'like', $searchValue)
                        ->orWhere('contact_number', 'like', $searchValue);
                });
            }
        }

        if (!in_array($sort, $filterableColumns, true)) {
            $sort = 'date_from';
        }

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query
            ->orderBy($sort, $order)
            ->paginate($perPageValue, ['*'], 'page', $page);

        $paginator->setCollection($this->syncLifecycleStatuses($paginator->getCollection()));

        return $paginator;
    }

    public function find(string $id)
    {
        $rental = $this->model->newQuery()->find($id);

        return $rental ? $this->syncLifecycleStatus($rental) : null;
    }

    public function create(array $data): RentalVehicle
    {
        /** @var RentalVehicle $rental */
        $rental = parent::create($data);

        return $rental;
    }

    public function update(int|string $id, array $data): Model
    {
        $rental = $this->find($id);
        $rental->update($data);

        return $rental;
    }

    public function delete(int|string $id): Model
    {
        return parent::delete($id);
    }

    public function checkConflict(string $vehicleType, Carbon $dateFrom, Carbon $dateTo, ?string $timeFrom = null, ?string $timeTo = null, ?string $excludeId = null): bool
    {
        if (blank($vehicleType)) {
            return false;
        }

        $query = $this->model->newQuery()
            ->where('vehicle_type', $vehicleType)
            ->whereIn('status', $this->blockingStatuses());

        $this->applyConflictWindow($query, $dateFrom, $dateTo, $timeFrom, $timeTo);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function getConflicts(string $vehicleType, Carbon $dateFrom, Carbon $dateTo, ?string $timeFrom = null, ?string $timeTo = null, ?string $excludeId = null)
    {
        if (blank($vehicleType)) {
            return collect();
        }

        $query = $this->model->newQuery()
            ->where('vehicle_type', $vehicleType)
            ->whereIn('status', $this->blockingStatuses());

        $this->applyConflictWindow($query, $dateFrom, $dateTo, $timeFrom, $timeTo);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $this->syncLifecycleStatuses($query->orderBy('date_from')->orderBy('time_from')->get());
    }

    private function applyConflictWindow($query, Carbon $dateFrom, Carbon $dateTo, ?string $timeFrom, ?string $timeTo): void
    {
        $requestedStart = $this->combineDateAndTime($dateFrom, $timeFrom, false);
        $requestedEnd = $this->combineDateAndTime($dateTo, $timeTo, true);

        $query->whereRaw($this->startTimestampExpression() . ' < ?', [$requestedEnd->toDateTimeString()])
            ->whereRaw($this->endTimestampExpression() . ' > ?', [$requestedStart->toDateTimeString()]);
    }

    private function startTimestampExpression(): string
    {
        return match (DB::getDriverName()) {
            'sqlite' => "datetime(date_from || ' ' || COALESCE(time_from, '00:00:00'))",
            default => "TIMESTAMP(date_from, COALESCE(time_from, '00:00:00'))",
        };
    }

    private function endTimestampExpression(): string
    {
        return match (DB::getDriverName()) {
            'sqlite' => "datetime(date_to || ' ' || COALESCE(time_to, '23:59:59'))",
            default => "TIMESTAMP(date_to, COALESCE(time_to, '23:59:59'))",
        };
    }

    private function blockingStatuses(): array
    {
        return [
            RentalVehicle::STATUS_APPROVED,
            RentalVehicle::STATUS_IN_PROGRESS,
        ];
    }

    private function syncLifecycleStatuses(Collection $rentals): Collection
    {
        return $rentals->map(fn (RentalVehicle $rental) => $this->syncLifecycleStatus($rental));
    }

    private function syncLifecycleStatus(RentalVehicle $rental): RentalVehicle
    {
        $nextStatus = $this->resolveLifecycleStatus($rental);

        if ($nextStatus && $nextStatus !== $rental->status) {
            $rental->forceFill(['status' => $nextStatus])->saveQuietly();
            $rental->status = $nextStatus;
        }

        return $rental;
    }

    private function resolveLifecycleStatus(RentalVehicle $rental): ?string
    {
        $status = strtolower((string) $rental->status);

        if (in_array($status, [
            RentalVehicle::STATUS_PENDING,
            RentalVehicle::STATUS_REJECTED,
            RentalVehicle::STATUS_CANCELLED,
        ], true)) {
            return null;
        }

        $startAt = $this->combineDateAndTime($rental->date_from, $rental->time_from, false);
        $endAt = $this->combineDateAndTime($rental->date_to, $rental->time_to, true);
        $now = now();

        if ($endAt && $now->gt($endAt)) {
            return RentalVehicle::STATUS_COMPLETED;
        }

        if ($startAt && $endAt && $now->betweenIncluded($startAt, $endAt)) {
            return RentalVehicle::STATUS_IN_PROGRESS;
        }

        if ($status === RentalVehicle::STATUS_IN_PROGRESS && $startAt && $now->lt($startAt)) {
            return RentalVehicle::STATUS_APPROVED;
        }

        return null;
    }

    private function combineDateAndTime(mixed $date, mixed $time, bool $endOfDay): ?Carbon
    {
        if (!$date) {
            return null;
        }

        $resolvedDate = $date instanceof CarbonInterface
            ? $date->copy()
            : Carbon::parse((string) $date);

        if ($time) {
            return Carbon::parse(sprintf('%s %s', $resolvedDate->toDateString(), $time));
        }

        return $endOfDay ? $resolvedDate->endOfDay() : $resolvedDate->startOfDay();
    }
}
