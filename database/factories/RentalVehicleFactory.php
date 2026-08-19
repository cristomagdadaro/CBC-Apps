<?php

namespace Database\Factories;

use App\Enums\RentalTripType;
use App\Models\LocCity;
use App\Models\Personnel;
use App\Models\RentalVehicle;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RentalVehicleFactory extends Factory
{
    protected static $personnels = null;

    public function definition(): array
    {
        if (self::$personnels === null) {
            self::$personnels = Personnel::query()->get(['fname', 'mname', 'lname', 'suffix', 'phone']);
        }

        $dateFrom = Carbon::now()->addDays($this->faker->numberBetween(1, 10));
        $dateTo = $dateFrom->copy()->addDays($this->faker->numberBetween(1, 5));
        
        $personnel = self::$personnels->isNotEmpty() ? self::$personnels->random() : null;

        $tripType = $this->faker->randomElement(RentalTripType::values());
        $isMultiStopTrip = $tripType === RentalTripType::MULTI_STOP_TRIP->value;
        $stopCount = $this->faker->numberBetween(1, 4);
        $destinationStops = $isMultiStopTrip
            ? array_map(
                fn (int $offset) => sprintf('Stop %d - %s', $offset + 1, $this->faker->streetName()),
                range(0, $stopCount - 1)
            )
            : [];
        $isSharedRide = $this->faker->boolean(30);
        $sharedRideReference = $isSharedRide ? 'SHR-' . Str::upper(Str::random(6)) : null;

        $requestedBy = $personnel
            ? trim(implode(' ', array_filter([
                $personnel->fname,
                $personnel->mname,
                $personnel->lname,
                $personnel->suffix,
            ])))
            : $this->faker->name();

        $destinationCity = LocCity::query()
            ->select(['city', 'province', 'region'])
            ->inRandomOrder()
            ->first();

        $membersOfParty = [];
        $membersCount = $this->faker->numberBetween(0, 4);
        if (self::$personnels->isNotEmpty()) {
            $membersOfParty = self::$personnels
                ->random(min($membersCount, self::$personnels->count()))
                ->map(fn ($item) => trim(implode(' ', array_filter([
                    $item->fname,
                    $item->mname,
                    $item->lname,
                    $item->suffix,
                ]))))
                ->filter()
                ->values()
                ->all();
        } else {
            for ($index = 0; $index < $membersCount; $index++) {
                $membersOfParty[] = $this->faker->name();
            }
        }

        $destinationLocation = $destinationCity
            ? sprintf('CBC Outpost — %s', $destinationCity->city)
            : $this->faker->streetAddress();

        return [
            'vehicle_type' => $this->faker->randomElement(['innova', 'pickup', 'van', 'suv']),
            'trip_type' => $tripType,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'time_from' => $this->faker->randomElement(['08:00', '09:00', '10:00']),
            'time_to' => $this->faker->randomElement(['17:00', '18:00', '19:00']),
            'purpose' => $this->faker->sentence(),
            'destination_location' => $destinationLocation,
            'destination_city' => $destinationCity?->city ?? $this->faker->city(),
            'destination_province' => $destinationCity?->province ?? $this->faker->state(),
            'destination_region' => $destinationCity?->region ?? $this->faker->randomElement(['NCR', 'CALABARZON', 'Central Visayas', 'CAR']),
            'destination_stops' => $destinationStops,
            'requested_by' => $requestedBy,
            'members_of_party' => $membersOfParty,
            'is_shared_ride' => $isSharedRide,
            'shared_ride_reference' => $sharedRideReference,
            'contact_number' => $personnel?->phone ?: $this->faker->phoneNumber(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected', 'completed', 'cancelled']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}