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
     * Generate a completely random date within a wide range
     */
    private function getRandomDate(): Carbon
    {
        // Random year: 2 years ago to 2 years in the future
        $year = rand(Carbon::now()->year - 2, Carbon::now()->year + 2);
        
        // Random month (1-12)
        $month = rand(1, 12);
        
        // Random day (1 to last day of month)
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $day = rand(1, $daysInMonth);
        
        return Carbon::create($year, $month, $day);
    }

    /**
     * Generate random time
     */
    private function getRandomTime(): string
    {
        $hour = rand(6, 20); // 6 AM to 8 PM
        $minute = rand(0, 3) * 15; // 00, 15, 30, 45
        return sprintf('%02d:%02d:00', $hour, $minute);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicleTypes = ['innova', 'pickup', 'van', 'suv'];
        $venueTypes = ['plenary', 'training_room', 'mph'];
        $statuses = ['pending', 'approved', 'rejected', 'completed', 'cancelled'];
        $tripTypes = RentalTripType::values();
        $multiStopType = RentalTripType::MULTI_STOP_TRIP->value;
        $personnels = Personnel::query()->get(['fname', 'mname', 'lname', 'suffix', 'phone']);

        // Create 100 vehicle rentals with completely random dates
        for ($i = 0; $i < 100; $i++) {
            $dateFrom = $this->getRandomDate();
            
            // Random duration: 0 to 14 days (0 = same day)
            $durationDays = rand(0, 14);
            $dateTo = $dateFrom->copy()->addDays($durationDays);
            
            // Random status
            $status = $statuses[array_rand($statuses)];
            
            // Random personnel or fallback
            $personnel = $personnels->isNotEmpty() && rand(0, 10) > 2 
                ? $personnels->random() 
                : null;

            $requestedBy = $personnel
                ? $this->formatPersonnelName($personnel)
                : "Random User " . Str::random(8);

            $contactNumber = $personnel?->phone
                ?: '09' . rand(10, 99) . str_pad((string) rand(0, 9999999), 7, '0', STR_PAD_LEFT);

            // Random members of party (0 to 6 people)
            $membersOfParty = $personnels->isNotEmpty() 
                ? $personnels->shuffle()->take(rand(0, 6))->map(fn ($item) => $this->formatPersonnelName($item))->filter()->values()->all()
                : [];

            // Random trip type
            $tripType = $tripTypes[array_rand($tripTypes)];
            $isMultiStopTrip = $tripType === $multiStopType;
            
            $destination = $this->getDestinationDetails($i);
            $destinationStops = $isMultiStopTrip ? $this->getDestinationStops($i) : [];
            
            // Random shared ride (20% chance)
            $isSharedRide = rand(0, 4) === 0;
            $sharedRideReference = $isSharedRide ? 'SHR-' . Str::upper(Str::random(6)) : null;

            RentalVehicle::factory()->create([
                'vehicle_type' => $vehicleTypes[array_rand($vehicleTypes)],
                'trip_type' => $tripType,
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
                'time_from' => $this->getRandomTime(),
                'time_to' => $this->getRandomTime(),
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
                'created_at' => $dateFrom->copy()->subDays(rand(1, 30)), // Created before start
                'updated_at' => $dateFrom->copy()->subDays(rand(0, 29)),
            ]);
        }

        // Create 80 venue rentals with completely random dates
        for ($i = 0; $i < 80; $i++) {
            $dateFrom = $this->getRandomDate();
            
            // Random duration: 0 to 7 days
            $durationDays = rand(0, 7);
            $dateTo = $dateFrom->copy()->addDays($durationDays);
            
            $status = $statuses[array_rand($statuses)];
            
            $personnel = $personnels->isNotEmpty() && rand(0, 10) > 2 
                ? $personnels->random() 
                : null;

            $requestedBy = $personnel
                ? $this->formatPersonnelName($personnel)
                : "Random User " . Str::random(8);

            $contactNumber = $personnel?->phone
                ?: '09' . rand(10, 99) . str_pad((string) rand(0, 9999999), 7, '0', STR_PAD_LEFT);

            $destination = $this->getDestinationDetails($i + 1000);

            RentalVenue::factory()->create([
                'venue_type' => $venueTypes[array_rand($venueTypes)],
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
                'time_from' => $this->getRandomTime(),
                'time_to' => $this->getRandomTime(),
                'expected_attendees' => rand(5, 200),
                'destination_location' => $destination['location'],
                'destination_city' => $destination['city'],
                'destination_province' => $destination['province'],
                'destination_region' => $destination['region'],
                'requested_by' => $requestedBy,
                'contact_number' => $contactNumber,
                'status' => $status,
                'created_at' => $dateFrom->copy()->subDays(rand(1, 30)),
                'updated_at' => $dateFrom->copy()->subDays(rand(0, 29)),
            ]);
        }

        // Create some edge case scenarios
        $this->createEdgeCases($personnels, $vehicleTypes, $venueTypes, $statuses);
    }

    /**
     * Create specific edge case scenarios
     */
    private function createEdgeCases($personnels, $vehicleTypes, $venueTypes, $statuses): void
    {
        $now = Carbon::now();
        
        // 1. Event spanning across month boundary
        $lastDayOfMonth = $now->copy()->endOfMonth();
        RentalVehicle::factory()->create([
            'vehicle_type' => $vehicleTypes[0],
            'trip_type' => RentalTripType::ONE_WAY_DROP_OFF->value,
            'date_from' => $lastDayOfMonth->toDateString(),
            'date_to' => $lastDayOfMonth->copy()->addDays(3)->toDateString(),
            'time_from' => '09:00:00',
            'time_to' => '17:00:00',
            'destination_location' => 'Cross-Month Event',
            'destination_city' => 'Quezon City',
            'destination_province' => 'Metro Manila',
            'destination_region' => 'NCR',
            'destination_stops' => [],
            'requested_by' => 'Edge Case: Month Boundary',
            'members_of_party' => [],
            'is_shared_ride' => false,
            'shared_ride_reference' => null,
            'contact_number' => '09123456789',
            'status' => 'approved',
        ]);

        // 2. Event spanning across year boundary
        $newYearEve = Carbon::create($now->year, 12, 31);
        RentalVenue::factory()->create([
            'venue_type' => $venueTypes[0],
            'date_from' => $newYearEve->toDateString(),
            'date_to' => $newYearEve->copy()->addDays(2)->toDateString(),
            'time_from' => '20:00:00',
            'time_to' => '02:00:00',
            'expected_attendees' => 100,
            'destination_location' => 'Year-End Party',
            'destination_city' => 'Makati',
            'destination_province' => 'Metro Manila',
            'destination_region' => 'NCR',
            'requested_by' => 'Edge Case: Year Boundary',
            'contact_number' => '09987654321',
            'status' => 'approved',
        ]);

        // 3. Leap year date (February 29)
        $leapYear = $now->year % 4 === 0 ? $now->year : $now->year + (4 - ($now->year % 4));
        if (Carbon::create($leapYear, 2, 29)->isLeapYear()) {
            RentalVehicle::factory()->create([
                'vehicle_type' => $vehicleTypes[1],
                'trip_type' => RentalTripType::DEDICATED_TRIP->value,
                'date_from' => "{$leapYear}-02-29",
                'date_to' => "{$leapYear}-03-02",
                'time_from' => '08:00:00',
                'time_to' => '16:00:00',
                'destination_location' => 'Leap Year Event',
                'destination_city' => 'Cebu City',
                'destination_province' => 'Cebu',
                'destination_region' => 'VII',
                'destination_stops' => [],
                'requested_by' => 'Edge Case: Leap Year',
                'members_of_party' => [],
                'is_shared_ride' => false,
                'shared_ride_reference' => null,
                'contact_number' => '09112233445',
                'status' => 'approved',
            ]);
        }

        // 4. Very long duration event (30 days)
        $longStart = $this->getRandomDate();
        RentalVehicle::factory()->create([
            'vehicle_type' => $vehicleTypes[2],
            'trip_type' => RentalTripType::MULTI_STOP_TRIP,
            'date_from' => $longStart->toDateString(),
            'date_to' => $longStart->copy()->addDays(30)->toDateString(),
            'time_from' => '06:00:00',
            'time_to' => '22:00:00',
            'destination_location' => 'Long Duration Event',
            'destination_city' => 'Davao City',
            'destination_province' => 'Davao del Sur',
            'destination_region' => 'XI',
            'destination_stops' => ['Stop 1', 'Stop 2', 'Stop 3'],
            'requested_by' => 'Edge Case: 30 Days',
            'members_of_party' => $personnels->isNotEmpty() 
                ? $personnels->take(5)->map(fn ($p) => $this->formatPersonnelName($p))->all() 
                : [],
            'is_shared_ride' => true,
            'shared_ride_reference' => 'SHR-LONG01',
            'contact_number' => '09223344556',
            'status' => 'approved',
        ]);

        // 5. Same day event (0 duration)
        $sameDay = $this->getRandomDate();
        RentalVenue::factory()->create([
            'venue_type' => $venueTypes[1],
            'date_from' => $sameDay->toDateString(),
            'date_to' => $sameDay->toDateString(),
            'time_from' => '09:00:00',
            'time_to' => '12:00:00',
            'expected_attendees' => 15,
            'destination_location' => 'Same Day Event',
            'destination_city' => 'Mandaluyong',
            'destination_province' => 'Metro Manila',
            'destination_region' => 'NCR',
            'requested_by' => 'Edge Case: Same Day',
            'contact_number' => '09334455667',
            'status' => 'completed',
        ]);

        // 6. Overlapping events (multiple events on same dates)
        $overlapDate = $this->getRandomDate();
        for ($j = 0; $j < 3; $j++) {
            RentalVehicle::factory()->create([
                'vehicle_type' => $vehicleTypes[$j % count($vehicleTypes)],
                'trip_type' => RentalTripType::ONE_WAY_DROP_OFF->value,
                'date_from' => $overlapDate->toDateString(),
                'date_to' => $overlapDate->copy()->addDays(2)->toDateString(),
                'time_from' => sprintf('%02d:00:00', 8 + ($j * 2)),
                'time_to' => sprintf('%02d:00:00', 12 + ($j * 2)),
                'destination_location' => "Overlapping Event " . ($j + 1),
                'destination_city' => 'Pasig',
                'destination_province' => 'Metro Manila',
                'destination_region' => 'NCR',
                'destination_stops' => [],
                'requested_by' => "Overlapping User " . ($j + 1),
                'members_of_party' => [],
                'is_shared_ride' => false,
                'shared_ride_reference' => null,
                'contact_number' => '0944556677' . $j,
                'status' => $statuses[$j % count($statuses)],
            ]);
        }

        // 7. Historical date (5 years ago)
        $historicalDate = Carbon::now()->subYears(5)->addDays(rand(0, 365));
        RentalVehicle::factory()->create([
            'vehicle_type' => $vehicleTypes[0],
            'trip_type' => RentalTripType::DEDICATED_TRIP->value,
            'date_from' => $historicalDate->toDateString(),
            'date_to' => $historicalDate->copy()->addDay()->toDateString(),
            'time_from' => '10:00:00',
            'time_to' => '14:00:00',
            'destination_location' => 'Historical Event',
            'destination_city' => 'Baguio',
            'destination_province' => 'Benguet',
            'destination_region' => 'CAR',
            'destination_stops' => [],
            'requested_by' => 'Edge Case: 5 Years Ago',
            'members_of_party' => [],
            'is_shared_ride' => false,
            'shared_ride_reference' => null,
            'contact_number' => '09556677889',
            'status' => 'completed',
            'created_at' => $historicalDate->copy()->subWeek(),
            'updated_at' => $historicalDate,
        ]);

        // 8. Far future date (5 years ahead)
        $futureDate = Carbon::now()->addYears(5)->addDays(rand(0, 365));
        RentalVenue::factory()->create([
            'venue_type' => $venueTypes[2],
            'date_from' => $futureDate->toDateString(),
            'date_to' => $futureDate->copy()->addDays(3)->toDateString(),
            'time_from' => '08:00:00',
            'time_to' => '17:00:00',
            'expected_attendees' => 500,
            'destination_location' => 'Future Planning Event',
            'destination_city' => 'Taguig',
            'destination_province' => 'Metro Manila',
            'destination_region' => 'NCR',
            'requested_by' => 'Edge Case: 5 Years Future',
            'contact_number' => '09667788990',
            'status' => 'pending',
        ]);
    }
}