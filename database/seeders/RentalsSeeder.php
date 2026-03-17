<?php

namespace Database\Seeders;

use App\Enums\RentalTripType;
use App\Models\LocCity;
use App\Models\Personnel;
use App\Models\RentalVenue;
use App\Models\RentalVehicle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RentalsSeeder extends Seeder
{
    private ?Collection $destinationCities = null;

    private function formatPersonnelName(Personnel $personnel): string
    {
        return trim(implode(' ', array_filter([
            $personnel->fname,
            $personnel->mname,
            $personnel->lname,
            $personnel->suffix,
        ])));
    }

    private function getDestinationCities(): Collection
    {
        return $this->destinationCities ??= LocCity::query()
            ->select(['city', 'province', 'region'])
            ->inRandomOrder()
            ->limit(10)
            ->get();
    }

    private function getDestinationDetails(int $index): array
    {
        $cities = $this->getDestinationCities();

        if ($cities->isEmpty()) {
            return [
                'location' => 'CBC Central Office',
                'city' => 'Quezon City',
                'province' => 'Metro Manila',
                'region' => 'NCR',
            ];
        }

        $city = $cities->get($index % $cities->count());

        return [
            'location' => sprintf('CBC Outpost (%s)', $city->city),
            'city' => $city->city,
            'province' => $city->province,
            'region' => $city->region,
        ];
    }

    private function getDestinationStops(int $index): array
    {
        $zones = ['North Wing', 'South Annex', 'East Pavilion', 'West Lobby', 'Service Yard'];
        $stopCount = ($index % 3) + 1;

        return array_map(
            fn (int $offset) => sprintf('Stop %d - %s', $offset + 1, $zones[($index + $offset) % count($zones)]),
            range(0, $stopCount - 1),
        );
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $startOfCurrentMonth = Carbon::now()->startOfMonth();
        $months = [
            $startOfCurrentMonth->copy()->subMonth(),
            $startOfCurrentMonth->copy(),
            $startOfCurrentMonth->copy()->addMonth(),
        ];

        $vehicleTypes = ['innova', 'pickup', 'van', 'suv'];
        $venueTypes = ['plenary', 'training_room', 'mph'];
        $statuses = ['pending', 'approved', 'rejected'];
        $tripTypes = RentalTripType::values();
        $multiStopType = RentalTripType::MULTI_STOP_TRIP->value;
        $personnels = Personnel::query()->get(['fname', 'mname', 'lname', 'suffix', 'phone']);

        foreach ($months as $monthIndex => $monthStart) {
            $monthLabel = $monthStart->format('F Y');

            for ($i = 0; $i < 16; $i++) {
                $dateFrom = $monthStart->copy()->addDays(($i * 2) % max($monthStart->daysInMonth - 1, 1));
                $dateTo = $dateFrom->copy()->addDays($i % 3);
                $status = $statuses[$i % count($statuses)];
                $personnel = $personnels->isNotEmpty() ? $personnels->random() : null;

                $requestedBy = $personnel
                    ? $this->formatPersonnelName($personnel)
                    : "Rental Seeder User " . ($monthIndex + 1) . '-' . ($i + 1);

                $contactNumber = $personnel?->phone
                    ?: '0917' . str_pad((string) (1000000 + ($monthIndex * 100) + $i), 7, '0', STR_PAD_LEFT);

                $membersOfParty = $personnels
                    ->shuffle()
                    ->take(rand(0, 4))
                    ->map(fn ($item) => $this->formatPersonnelName($item))
                    ->filter()
                    ->values()
                    ->all();

                $seedIndex = ($monthIndex * 16) + $i;
                $tripTypeIndex = $seedIndex % count($tripTypes);
                $tripType = $tripTypes[$tripTypeIndex];
                $isMultiStopTrip = $tripType === $multiStopType;
                $destination = $this->getDestinationDetails($seedIndex);
                $destinationStops = $isMultiStopTrip ? $this->getDestinationStops($seedIndex) : [];
                $isSharedRide = $i % 4 === 0;
                $sharedRideReference = $isSharedRide ? 'SHR-' . Str::upper(Str::random(6)) : null;

                RentalVehicle::factory()->create([
                    'vehicle_type' => $vehicleTypes[$i % count($vehicleTypes)],
                    'trip_type' => $tripType,
                    'date_from' => $dateFrom->toDateString(),
                    'date_to' => $dateTo->toDateString(),
                    'time_from' => ($i % 2 === 0) ? '08:00:00' : '13:00:00',
                    'time_to' => ($i % 2 === 0) ? '12:00:00' : '17:00:00',
                    'destination_location' => $destination['location'],
                    'destination_city' => $destination['city'],
                    'destination_province' => $destination['province'],
                    'destination_region' => $destination['region'],
                    'destination_stops' => $destinationStops,
                    'requested_by' => $requestedBy,
                    'members_of_party' => $membersOfParty,
                    'is_shared_ride' => $isSharedRide,
                    'shared_ride_reference' => $sharedRideReference,
                    'contact_number' => $contactNumber,
                    'status' => $status,
                ]);

                RentalVenue::factory()->create([
                    'venue_type' => $venueTypes[$i % count($venueTypes)],
                    'date_from' => $dateFrom->toDateString(),
                    'date_to' => $dateTo->toDateString(),
                    'time_from' => ($i % 2 === 0) ? '09:00:00' : '14:00:00',
                    'time_to' => ($i % 2 === 0) ? '12:00:00' : '18:00:00',
                    'expected_attendees' => 20 + ($i * 5),
                    'destination_location' => $destination['location'],
                    'destination_city' => $destination['city'],
                    'destination_province' => $destination['province'],
                    'destination_region' => $destination['region'],
                    'requested_by' => $requestedBy,
                    'contact_number' => $contactNumber,
                    'status' => $status,
                ]);
            }
        }
    }
}
