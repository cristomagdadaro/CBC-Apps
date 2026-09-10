<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\RentalVehicle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class TravelOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $files = [
            base_path('FOR TRAVEL ORDER.xlsx - 2025.csv'),
            base_path('FOR TRAVEL ORDER.xlsx - 2026.csv')
        ];

        foreach ($files as $file) {
            if (!file_exists($file)) {
                $this->command->warn("File not found: {$file}");
                continue;
            }

            $this->command->info("Seeding from: {$file}");
            
            $handle = fopen($file, "r");
            $row = 0;
            $seededCount = 0;

            while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
                $row++;
                // Skip the headers, data starts around line 6
                if ($row < 6) continue;
                
                // Skip completely empty rows
                if (empty(array_filter($data))) continue;

                $namesString = trim($data[3] ?? '');
                $purpose = trim($data[4] ?? '');
                
                // Skip if there are no names and no purpose (likely empty row)
                if (empty($namesString) && empty($purpose)) continue;
                
                // Parse Name/s
                $names = array_filter(array_map('trim', explode("\n", $namesString)));
                $requestedBy = array_shift($names); // First person is assumed to be the requester
                $membersOfParty = array_values($names); // The rest are members

                $dateFromStr = trim($data[5] ?? '');
                $dateToStr = trim($data[6] ?? '');

                $orderDateStr = trim($data[0] ?? '');
                try {
                    $orderDate = $orderDateStr ? Carbon::parse($orderDateStr)->format('Y-m-d') : '2025-01-01';
                } catch (\Exception $e) {
                    $orderDate = '2025-01-01';
                }

                try {
                    $dateFrom = $dateFromStr ? Carbon::parse($dateFromStr)->format('Y-m-d') : $orderDate;
                } catch (\Exception $e) {
                    $dateFrom = $orderDate;
                }

                try {
                    $dateTo = $dateToStr ? Carbon::parse($dateToStr)->format('Y-m-d') : $dateFrom;
                } catch (\Exception $e) {
                    $dateTo = $dateFrom;
                }
                
                $vehicleType = trim($data[7] ?? '');
                $flightDetails = trim($data[8] ?? '');
                $timeMeeting = trim($data[12] ?? '');
                $destination = trim($data[14] ?? '');
                $trackingNumber = trim($data[2] ?? '');
                $statusField = trim($data[18] ?? '');

                $timeFrom = '08:00:00'; // Default
                $timeTo = '17:00:00'; // Default
                try {
                    if ($timeMeeting && strtolower($timeMeeting) !== 'tba' && $timeMeeting !== '-') {
                        $tParts = explode('-', $timeMeeting);
                        $timeFrom = Carbon::parse(trim($tParts[0]))->format('H:i:s');
                        if (isset($tParts[1])) {
                            $timeTo = Carbon::parse(trim($tParts[1]))->format('H:i:s');
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore parse errors on time and fallback to defaults
                }

                $travelDetails = [];
                if ($flightDetails) {
                    $travelDetails['notes'] = "Flight Details:\n" . $flightDetails;
                }
                if ($trackingNumber) {
                    $travelDetails['legacy_tracking_number'] = $trackingNumber;
                }
                if ($statusField) {
                    $travelDetails['legacy_status'] = $statusField;
                }

                // If date from and to are both null, we might skip, but let's just insert
                RentalVehicle::create([
                    'vehicle_type' => substr($vehicleType, 0, 255) ?: null,
                    'trip_type' => 'dedicated_trip',
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'time_from' => $timeFrom,
                    'time_to' => $timeTo,
                    'purpose' => $purpose ?: 'N/A',
                    'destination_location' => substr($destination, 0, 255) ?: 'N/A',
                    'destination_city' => 'N/A',
                    'destination_province' => 'N/A',
                    'destination_region' => 'N/A',
                    'destination_stops' => [],
                    'requested_by' => $requestedBy ?: 'Unknown',
                    'organization' => 'PhilRice',
                    'contact_number' => 'N/A',
                    'members_of_party' => empty($membersOfParty) ? null : $membersOfParty,
                    'is_shared_ride' => false,
                    'status' => RentalVehicle::STATUS_COMPLETED, // Historical data is completed
                    'travel_details' => empty($travelDetails) ? null : $travelDetails,
                ]);

                $seededCount++;
            }
            
            fclose($handle);
            $this->command->info("Seeded {$seededCount} records from {$file}");
        }
    }
}
