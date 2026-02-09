<?php

namespace App\Repositories;

use App\Models\RentalVehicle;
use Carbon\Carbon;

class RentalVehicleRepository
{
    public function all(array $filters = [])
    {
        $query = RentalVehicle::query();

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

    public function paginate(int $perPage = 15)
    {
        return RentalVehicle::orderBy('date_from', 'asc')->paginate($perPage);
    }

    public function find(string $id)
    {
        return RentalVehicle::find($id);
    }

    public function create(array $data): RentalVehicle
    {
        return RentalVehicle::create($data);
    }

    public function update(string $id, array $data): RentalVehicle
    {
        $rental = $this->find($id);
        $rental->update($data);
        return $rental;
    }

    public function delete(string $id): bool
    {
        return RentalVehicle::destroy($id) > 0;
    }

    public function checkConflict(string $vehicleType, Carbon $dateFrom, Carbon $dateTo, string $timeFrom, string $timeTo, ?string $excludeId = null): bool
    {
        $query = RentalVehicle::where('vehicle_type', $vehicleType)
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
        $query = RentalVehicle::where('vehicle_type', $vehicleType)
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
