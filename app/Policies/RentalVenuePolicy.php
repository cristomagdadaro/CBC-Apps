<?php

namespace App\Policies;

use App\Models\RentalVenue;
use App\Models\User;
use App\Policies\Concerns\AuthorizesByPermission;

class RentalVenuePolicy
{
    use AuthorizesByPermission;

    public function viewAny(?User $user): bool
    {
        return $this->allowed($user, 'rental.venue.manage');
    }

    public function create(?User $user): bool
    {
        return $this->allowed($user, 'rental.venue.manage');
    }

    public function update(?User $user, RentalVenue $rentalVenue): bool
    {
        return $this->allowed($user, 'rental.venue.manage');
    }

    public function delete(?User $user, RentalVenue $rentalVenue): bool
    {
        return $this->allowed($user, 'rental.venue.manage');
    }
}
