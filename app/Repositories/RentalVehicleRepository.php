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

    public function search(Collection $parameters, bool $withPagination = true, bool $isTrashed = false)
    {
        $result = parent::search($parameters, $withPagination, $isTrashed);

        if ($result instanceof LengthAwarePaginator) {
            $result->setCollection($this->syncLifecycleStatuses($result->getCollection()));
        } else {
            $result = $this->syncLifecycleStatuses($result);
        }

        return $result;
    }

    protected function buildSearchQuery(Collection $parameters, bool $withPagination, bool $isTrashed)
    {
        $builder = $this->model->newQuery();

        if ($isTrashed) {
            $builder->onlyTrashed();
        }

        $this->applyAppends($builder, $parameters);

        if ($vehicleType = $parameters->get('vehicle_type')) {
            $builder->where('vehicle_type', $vehicleType);
        }

        if ($tripType = $parameters->get('trip_type')) {
            $builder->where('trip_type', $tripType);
        }

        if ($parameters->has('statuses')) {
            $builder->whereIn('status', (array) $parameters->get('statuses'));
        } elseif ($status = $parameters->get('status')) {
            is_array($status)
                ? $builder->whereIn('status', $status)
                : $builder->where('status', $status);
        }

        if ($dateFrom = $parameters->get('date_from')) {
            $builder->where('date_from', '>=', $dateFrom);
        }

        if ($dateTo = $parameters->get('date_to')) {
            $builder->where('date_to', '<=', $dateTo);
        }
        
        $this->applySearchFilters($builder, $parameters);
        
        // Retain default sorting logic if none provided
        if (!$parameters->has('sort')) {
            $parameters->put('sort', 'date_from');
            if (!$parameters->has('order')) {
                $parameters->put('order', 'asc');
            }
        }
        
        $this->applyGroupBy($builder, $parameters);
        $this->applySorting($builder, $parameters);

        if ($withPagination) {
            return $this->applyPagination($builder, $parameters);
        }

        return $builder->get();
    }

    public function find(string $id)
    {
        $query = $this->model->newQuery();
        
        if (\Illuminate\Support\Str::isUuid($id)) {
            $query->where('id', $id);
        } else {
            $query->where('booking_id', $id);
        }
        
        $rental = $query->first();

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
