<?php

namespace App\Policies;

use App\Models\RentalVehicle;
use App\Models\User;
use App\Policies\Concerns\AuthorizesByPermission;

class RentalVehiclePolicy
{
    use AuthorizesByPermission;

    public function viewAny(?User $user): bool
    {
        return $this->allowed($user, 'rental.vehicle.manage');
    }

    public function create(?User $user): bool
    {
        return $this->allowed($user, 'rental.vehicle.manage');
    }

    public function update(?User $user, RentalVehicle $rentalVehicle): bool
    {
        return $this->allowed($user, 'rental.vehicle.manage');
    }

    public function delete(?User $user, RentalVehicle $rentalVehicle): bool
    {
        return $this->allowed($user, 'rental.vehicle.manage');
    }
}
