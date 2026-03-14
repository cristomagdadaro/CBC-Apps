<?php

namespace Database\Seeders;

use App\Models\Personnel;
use App\Models\RentalVenue;
use App\Models\RentalVehicle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RentalsSeeder extends Seeder
{
    private function formatPersonnelName(Personnel $personnel): string
    {
        return trim(implode(' ', array_filter([
            $personnel->fname,
            $personnel->mname,
            $personnel->lname,
            $personnel->suffix,
        ])));
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

                RentalVehicle::query()->create([
                    'vehicle_type' => $vehicleTypes[$i % count($vehicleTypes)],
                    'date_from' => $dateFrom->toDateString(),
                    'date_to' => $dateTo->toDateString(),
                    'time_from' => ($i % 2 === 0) ? '08:00:00' : '13:00:00',
                    'time_to' => ($i % 2 === 0) ? '12:00:00' : '17:00:00',
                    'purpose' => "{$monthLabel} vehicle transport booking #" . ($i + 1),
                    'requested_by' => $requestedBy,
                    'members_of_party' => $membersOfParty,
                    'contact_number' => $contactNumber,
                    'status' => $status,
                    'notes' => "Auto-seeded {$monthLabel} vehicle booking for calendar testing and database search.",
                ]);

                RentalVenue::query()->create([
                    'venue_type' => $venueTypes[$i % count($venueTypes)],
                    'date_from' => $dateFrom->toDateString(),
                    'date_to' => $dateTo->toDateString(),
                    'time_from' => ($i % 2 === 0) ? '09:00:00' : '14:00:00',
                    'time_to' => ($i % 2 === 0) ? '12:00:00' : '18:00:00',
                    'expected_attendees' => 20 + ($i * 5),
                    'event_name' => "{$monthLabel} Center Event #" . ($i + 1),
                    'requested_by' => $requestedBy,
                    'contact_number' => $contactNumber,
                    'status' => $status,
                    'notes' => "Auto-seeded {$monthLabel} venue booking for past/current/future calendar navigation.",
                ]);
            }
        }
    }
}
