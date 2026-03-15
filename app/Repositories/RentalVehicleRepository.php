<?php

namespace App\Repositories;

use App\Models\RentalVehicle;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('date_from', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('date_to', '<=', $filters['date_to']);
        }

        return $query->orderBy('date_from', 'asc')->get();
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

        return $query
            ->orderBy($sort, $order)
            ->paginate($perPageValue, ['*'], 'page', $page);
    }

    public function find(string $id)
    {
        return $this->model->newQuery()->find($id);
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

    public function checkConflict(string $vehicleType, Carbon $dateFrom, Carbon $dateTo, string $timeFrom, string $timeTo, ?string $excludeId = null): bool
    {
        $query = $this->model->newQuery()->where('vehicle_type', $vehicleType)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($dateFrom, $dateTo) {
                $q->where(function ($subQ) use ($dateFrom, $dateTo) {
                    $subQ->whereBetween('date_from', [$dateFrom, $dateTo])
                        ->orWhereBetween('date_to', [$dateFrom, $dateTo])
                        ->orWhere(function ($innerQ) use ($dateFrom, $dateTo) {
                            $innerQ->where('date_from', '<=', $dateFrom)
                                ->where('date_to', '>=', $dateTo);
                        });
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function getConflicts(string $vehicleType, Carbon $dateFrom, Carbon $dateTo, ?string $excludeId = null)
    {
        $query = $this->model->newQuery()->where('vehicle_type', $vehicleType)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($dateFrom, $dateTo) {
                $q->where(function ($subQ) use ($dateFrom, $dateTo) {
                    $subQ->whereBetween('date_from', [$dateFrom, $dateTo])
                        ->orWhereBetween('date_to', [$dateFrom, $dateTo])
                        ->orWhere(function ($innerQ) use ($dateFrom, $dateTo) {
                            $innerQ->where('date_from', '<=', $dateFrom)
                                ->where('date_to', '>=', $dateTo);
                        });
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get();
    }
}
