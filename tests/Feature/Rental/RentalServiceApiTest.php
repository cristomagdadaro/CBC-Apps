<?php

namespace Tests\Feature\Rental;

use App\Models\Option;
use App\Models\RentalVehicle;
use App\Models\RentalVenue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RentalServiceApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($this->admin, 'sanctum');

        $this->seedLocation();
        $this->seedVehicleOptions();
        $this->seedVenueOptions();
    }

    // ========================
    // VEHICLE RENTAL TESTS
    // ========================

    public function test_create_vehicle_rental_with_valid_data()
    {
        $response = $this->postJson('/api/rental/vehicles', $this->vehiclePayload());

        $response->assertStatus(201);
        $response->assertJsonStructure(['data' => ['id', 'vehicle_type', 'trip_type', 'status']]);
        $this->assertEquals('pending', $response['data']['status']);
    }

    public function test_create_vehicle_rental_without_assigned_vehicle_type()
    {
        $response = $this->postJson('/api/rental/vehicles', $this->vehiclePayload([
            'vehicle_type' => null,
            'trip_type' => 'multi_stop_trip',
            'destination_stops' => ['Rice Program Unit', 'Training Hall'],
        ]));

        $response->assertStatus(201)
            ->assertJsonPath('data.vehicle_type', null)
            ->assertJsonPath('data.trip_type', 'multi_stop_trip')
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_create_vehicle_rental_fails_with_invalid_vehicle_type()
    {
        $response = $this->postJson('/api/rental/vehicles', $this->vehiclePayload([
            'vehicle_type' => 'invalid_vehicle',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('vehicle_type');
    }

    public function test_create_vehicle_rental_fails_with_past_date()
    {
        $response = $this->postJson('/api/rental/vehicles', $this->vehiclePayload([
            'vehicle_type' => 'van',
            'date_from' => now()->subDays(5)->format('Y-m-d'),
            'date_to' => now()->subDays(3)->format('Y-m-d'),
        ]));

        $response->assertStatus(422);
    }

    public function test_check_vehicle_availability_returns_true_when_available()
    {
        $vehicleType = 'innova';
        $dateFrom = now()->addDays(10)->format('Y-m-d');
        $dateTo = now()->addDays(12)->format('Y-m-d');

        $response = $this->getJson("/api/rental/vehicles/check-availability/{$vehicleType}/{$dateFrom}/{$dateTo}");

        $response->assertStatus(200);
        $this->assertTrue($response['available']);
    }

    public function test_check_vehicle_availability_returns_false_when_booked()
    {
        $vehicleType = 'pickup';
        $dateFrom = now()->addDays(5);
        $dateTo = now()->addDays(7);

        // Create existing rental
        RentalVehicle::create([
            'vehicle_type' => $vehicleType,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'time_from' => '08:00',
            'time_to' => '17:00',
            'purpose' => 'Existing booking',
            'requested_by' => 'Admin',
            'contact_number' => '09171234567',
            'status' => 'approved',
        ]);

        $response = $this->getJson("/api/rental/vehicles/check-availability/{$vehicleType}/{$dateFrom->format('Y-m-d')}/{$dateTo->format('Y-m-d')}");

        $response->assertStatus(200);
        $this->assertFalse($response['available']);
    }

    public function test_create_vehicle_rental_fails_when_conflicting_booking_exists()
    {
        $vehicleType = 'suv';
        $dateFrom = now()->addDays(5);
        $dateTo = now()->addDays(7);

        // Create existing rental
        RentalVehicle::create([
            'vehicle_type' => $vehicleType,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'time_from' => '08:00:00',
            'time_to' => '17:00:00',
            'purpose' => 'Existing booking',
            'requested_by' => 'Admin',
            'contact_number' => '09171234567',
            'status' => 'approved',
        ]);

        // Try to book same vehicle for overlapping dates
        $response = $this->postJson('/api/rental/vehicles', $this->vehiclePayload([
            'vehicle_type' => $vehicleType,
            'date_from' => $dateFrom->copy()->addDay()->format('Y-m-d'),
            'date_to' => $dateTo->format('Y-m-d'),
            'purpose' => 'Conflicting booking',
        ]));

        $response->assertStatus(409);
        $response->assertJson(['error' => 'conflict']);
    }

    public function test_get_all_vehicle_rentals()
    {
        RentalVehicle::factory()->count(3)->create();

        $response = $this->getJson('/api/rental/vehicles');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'total', 'per_page', 'current_page', 'last_page']);
    }

    public function test_get_vehicle_rental_by_id()
    {
        $rental = RentalVehicle::create([
            'vehicle_type' => 'innova',
            'date_from' => now()->addDays(5),
            'date_to' => now()->addDays(7),
            'time_from' => '08:00',
            'time_to' => '17:00',
            'purpose' => 'Test',
            'requested_by' => 'John Doe',
            'contact_number' => '09171234567',
            'status' => 'pending',
        ]);

        $response = $this->getJson("/api/rental/vehicles/{$rental->id}");

        $response->assertStatus(200);
        $response->assertJson(['data' => ['id' => $rental->id]]);
    }

    public function test_vehicle_approval_requires_vehicle_assignment()
    {
        $rental = RentalVehicle::create([
            'vehicle_type' => null,
            'trip_type' => 'dedicated_trip',
            'date_from' => now()->addDays(5),
            'date_to' => now()->addDays(7),
            'time_from' => '08:00:00',
            'time_to' => '17:00:00',
            'purpose' => 'Test',
            'destination_location' => 'Main Office',
            'destination_city' => 'Science City of Muñoz',
            'destination_province' => 'Nueva Ecija',
            'destination_region' => 'REGION III',
            'requested_by' => 'John Doe',
            'contact_number' => '09171234567',
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/rental/vehicles/{$rental->id}/status", [
            'status' => 'approved',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('vehicle_type');
    }

    public function test_vehicle_approval_assigns_vehicle_type()
    {
        $rental = RentalVehicle::create([
            'vehicle_type' => null,
            'trip_type' => 'dedicated_trip',
            'date_from' => now()->addDays(5),
            'date_to' => now()->addDays(7),
            'time_from' => '08:00:00',
            'time_to' => '17:00:00',
            'purpose' => 'Test',
            'destination_location' => 'Main Office',
            'destination_city' => 'Science City of Muñoz',
            'destination_province' => 'Nueva Ecija',
            'destination_region' => 'REGION III',
            'requested_by' => 'John Doe',
            'contact_number' => '09171234567',
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/rental/vehicles/{$rental->id}/status", [
            'status' => 'approved',
            'vehicle_type' => 'pickup',
            'notes' => 'Assigned during approval',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.vehicle_type', 'pickup')
            ->assertJsonPath('data.notes', 'Assigned during approval');
    }

    public function test_update_vehicle_rental()
    {
        $rental = RentalVehicle::create([
            'vehicle_type' => 'innova',
            'date_from' => now()->addDays(5),
            'date_to' => now()->addDays(7),
            'time_from' => '08:00',
            'time_to' => '17:00',
            'purpose' => 'Test',
            'requested_by' => 'John Doe',
            'contact_number' => '09171234567',
            'status' => 'pending',
        ]);

        $updateData = [
            'status' => 'approved',
            'notes' => 'Updated notes',
        ];

        $response = $this->putJson("/api/rental/vehicles/{$rental->id}", $updateData);

        $response->assertStatus(200);
        $this->assertEquals('approved', $response['data']['status']);
    }

    public function test_delete_vehicle_rental()
    {
        $rental = RentalVehicle::create([
            'vehicle_type' => 'van',
            'date_from' => now()->addDays(5),
            'date_to' => now()->addDays(7),
            'time_from' => '08:00',
            'time_to' => '17:00',
            'purpose' => 'Test',
            'requested_by' => 'John Doe',
            'contact_number' => '09171234567',
            'status' => 'pending',
        ]);

        $response = $this->deleteJson("/api/rental/vehicles/{$rental->id}");

        $response->assertStatus(200);
        $this->assertNull(RentalVehicle::find($rental->id));
    }

    public function test_get_vehicles_by_type()
    {
        RentalVehicle::create([
            'vehicle_type' => 'innova',
            'date_from' => now()->addDays(5),
            'date_to' => now()->addDays(7),
            'time_from' => '08:00',
            'time_to' => '17:00',
            'purpose' => 'Test',
            'requested_by' => 'John Doe',
            'contact_number' => '09171234567',
            'status' => 'pending',
        ]);

        RentalVehicle::create([
            'vehicle_type' => 'pickup',
            'date_from' => now()->addDays(8),
            'date_to' => now()->addDays(10),
            'time_from' => '08:00',
            'time_to' => '17:00',
            'purpose' => 'Test',
            'requested_by' => 'Jane Doe',
            'contact_number' => '09181234567',
            'status' => 'pending',
        ]);

        $response = $this->getJson('/api/rental/vehicles/by-type/innova');

        $response->assertStatus(200);
        $this->assertCount(1, $response['data']);
    }

    // ========================
    // VENUE RENTAL TESTS
    // ========================

    public function test_create_venue_rental_with_valid_data()
    {
        $response = $this->postJson('/api/rental/venues', $this->venuePayload());

        $response->assertStatus(201);
        $response->assertJsonStructure(['data' => ['id', 'venue_type', 'status']]);
        $this->assertEquals('pending', $response['data']['status']);
    }

    public function test_create_venue_rental_fails_with_invalid_venue_type()
    {
        $response = $this->postJson('/api/rental/venues', $this->venuePayload([
            'venue_type' => 'invalid_venue',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('venue_type');
    }

    public function test_create_venue_rental_fails_with_zero_attendees()
    {
        $data = $this->venuePayload([
            'venue_type' => 'training_room',
            'expected_attendees' => 0,
        ]);

        $response = $this->postJson('/api/rental/venues', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('expected_attendees');
    }

    public function test_check_venue_availability_returns_true_when_available()
    {
        $venueType = 'plenary';
        $dateFrom = now()->addDays(20)->format('Y-m-d');
        $dateTo = now()->addDays(20)->format('Y-m-d');

        $response = $this->getJson("/api/rental/venues/check-availability/{$venueType}/{$dateFrom}/{$dateTo}");

        $response->assertStatus(200);
        $this->assertTrue($response['available']);
    }

    public function test_check_venue_availability_returns_false_when_booked()
    {
        $venueType = 'mph';
        $dateFrom = now()->addDays(15);
        $dateTo = now()->addDays(15);

        // Create existing rental
        RentalVenue::create([
            'venue_type' => $venueType,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'time_from' => '09:00',
            'time_to' => '17:00',
            'expected_attendees' => 150,
            'event_name' => 'Existing Event',
            'requested_by' => 'Admin',
            'contact_number' => '09171234567',
            'status' => 'approved',
        ]);

        $response = $this->getJson("/api/rental/venues/check-availability/{$venueType}/{$dateFrom->format('Y-m-d')}/{$dateTo->format('Y-m-d')}");

        $response->assertStatus(200);
        $this->assertFalse($response['available']);
    }

    public function test_create_venue_rental_fails_when_conflicting_booking_exists()
    {
        $venueType = 'training_room';
        $dateFrom = now()->addDays(15);
        $dateTo = now()->addDays(15);

        // Create existing rental
        RentalVenue::create([
            'venue_type' => $venueType,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'time_from' => '09:00',
            'time_to' => '12:00',
            'expected_attendees' => 50,
            'event_name' => 'Existing Event',
            'requested_by' => 'Admin',
            'contact_number' => '09171234567',
            'status' => 'approved',
        ]);

        // Try to book same venue for overlapping time
        $data = $this->venuePayload([
            'venue_type' => $venueType,
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to' => $dateTo->format('Y-m-d'),
            'time_from' => '11:00:00',
            'time_to' => '15:00:00',
            'expected_attendees' => 100,
            'event_name' => 'Conflicting Event',
            'requested_by' => 'Jane Doe',
            'contact_number' => '09181234567',
        ]);

        $response = $this->postJson('/api/rental/venues', $data);

        $response->assertStatus(409);
        $response->assertJson(['error' => 'conflict']);
    }

    public function test_get_all_venue_rentals()
    {
        RentalVenue::create([
            'venue_type' => 'plenary',
            'date_from' => now()->addDays(15),
            'date_to' => now()->addDays(15),
            'time_from' => '09:00',
            'time_to' => '17:00',
            'expected_attendees' => 200,
            'event_name' => 'Event 1',
            'requested_by' => 'John Doe',
            'contact_number' => '09171234567',
        ]);

        $response = $this->getJson('/api/rental/venues');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'total', 'per_page', 'current_page', 'last_page']);
    }

    public function test_get_venue_rental_by_id()
    {
        $rental = RentalVenue::create([
            'venue_type' => 'mph',
            'date_from' => now()->addDays(20),
            'date_to' => now()->addDays(20),
            'time_from' => '10:00',
            'time_to' => '14:00',
            'expected_attendees' => 300,
            'event_name' => 'Workshop',
            'requested_by' => 'Jane Smith',
            'contact_number' => '09181234567',
        ]);

        $response = $this->getJson("/api/rental/venues/{$rental->id}");

        $response->assertStatus(200);
        $response->assertJson(['data' => ['id' => $rental->id]]);
    }

    public function test_update_venue_rental()
    {
        $rental = RentalVenue::create([
            'venue_type' => 'plenary',
            'date_from' => now()->addDays(15),
            'date_to' => now()->addDays(15),
            'time_from' => '09:00',
            'time_to' => '17:00',
            'expected_attendees' => 200,
            'event_name' => 'Event',
            'requested_by' => 'John Doe',
            'contact_number' => '09171234567',
        ]);

        $updateData = [
            'status' => 'approved',
            'expected_attendees' => 250,
        ];

        $response = $this->putJson("/api/rental/venues/{$rental->id}", $updateData);

        $response->assertStatus(200);
        $this->assertEquals('approved', $response['data']['status']);
        $this->assertEquals(250, $response['data']['expected_attendees']);
    }

    public function test_delete_venue_rental()
    {
        $rental = RentalVenue::create([
            'venue_type' => 'training_room',
            'date_from' => now()->addDays(25),
            'date_to' => now()->addDays(25),
            'time_from' => '13:00',
            'time_to' => '16:00',
            'expected_attendees' => 75,
            'event_name' => 'Training',
            'requested_by' => 'Admin',
            'contact_number' => '09171234567',
        ]);

        $response = $this->deleteJson("/api/rental/venues/{$rental->id}");

        $response->assertStatus(200);
        $this->assertNull(RentalVenue::find($rental->id));
    }

    public function test_get_venues_by_type()
    {
        RentalVenue::create([
            'venue_type' => 'plenary',
            'date_from' => now()->addDays(15),
            'date_to' => now()->addDays(15),
            'time_from' => '09:00',
            'time_to' => '17:00',
            'expected_attendees' => 200,
            'event_name' => 'Event 1',
            'requested_by' => 'John',
            'contact_number' => '09171234567',
        ]);

        RentalVenue::create([
            'venue_type' => 'training_room',
            'date_from' => now()->addDays(20),
            'date_to' => now()->addDays(20),
            'time_from' => '10:00',
            'time_to' => '14:00',
            'expected_attendees' => 50,
            'event_name' => 'Event 2',
            'requested_by' => 'Jane',
            'contact_number' => '09181234567',
        ]);

        $response = $this->getJson('/api/rental/venues/by-type/plenary');

        $response->assertStatus(200);
        $this->assertCount(1, $response['data']);
    }

    // ========================
    // MULTI-RESOURCE CONFLICT TESTS
    // ========================

    public function test_multiple_vehicles_can_be_booked_for_same_dates()
    {
        $dateFrom = now()->addDays(10);
        $dateTo = now()->addDays(12);

        RentalVehicle::create([
            'vehicle_type' => 'innova',
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'time_from' => '08:00:00',
            'time_to' => '17:00:00',
            'purpose' => 'Booking 1',
            'requested_by' => 'User 1',
            'contact_number' => '09171234567',
            'status' => 'approved',
        ]);

        // Same dates but different vehicle type should succeed
        $response = $this->postJson('/api/rental/vehicles', $this->vehiclePayload([
            'vehicle_type' => 'van',
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to' => $dateTo->format('Y-m-d'),
            'purpose' => 'Booking 2',
        ]));

        $response->assertStatus(201);
    }

    public function test_contact_number_validation()
    {
        $response = $this->postJson('/api/rental/vehicles', $this->vehiclePayload([
            'contact_number' => 'invalid_contact',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('contact_number');
    }

    public function test_time_range_validation()
    {
        $sameDay = now()->addDays(9)->toDateString();

        $response = $this->postJson('/api/rental/vehicles', $this->vehiclePayload([
            'date_from' => $sameDay,
            'date_to' => $sameDay,
            'time_from' => '17:00:00',
            'time_to' => '08:00:00', // End time before start time
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('time_to');
    }

    private function seedLocation(): void
    {
        DB::table('loc_cities')->insert([
            'id' => 1,
            'city' => 'Science City of Muñoz',
            'province' => 'Nueva Ecija',
            'region' => 'REGION III',
            'latitude' => 15.7161,
            'longitude' => 120.9031,
        ]);
    }

    private function seedVehicleOptions(): void
    {
        Option::query()->create([
            'key' => 'vehicles',
            'value' => json_encode([
                ['name' => 'innova', 'label' => 'Toyota Innova'],
                ['name' => 'pickup', 'label' => 'Pickup'],
                ['name' => 'van', 'label' => 'Van'],
                ['name' => 'suv', 'label' => 'SUV'],
            ]),
            'label' => 'Vehicles',
            'description' => 'Rental vehicles',
            'type' => 'json',
            'group' => 'rentals',
        ]);
    }

    private function seedVenueOptions(): void
    {
        Option::query()->create([
            'key' => 'event_halls',
            'value' => json_encode([
                ['name' => 'plenary', 'label' => 'Plenary Hall'],
                ['name' => 'training_room', 'label' => 'Training Room'],
                ['name' => 'mph', 'label' => 'Multi-Purpose Hall'],
            ]),
            'label' => 'Event Halls',
            'description' => 'Rental venues',
            'type' => 'json',
            'group' => 'rentals',
        ]);
    }

    private function vehiclePayload(array $overrides = []): array
    {
        return array_merge([
            'vehicle_type' => 'innova',
            'trip_type' => 'dedicated_trip',
            'date_from' => now()->addDays(5)->toDateString(),
            'date_to' => now()->addDays(6)->toDateString(),
            'time_from' => '08:00:00',
            'time_to' => '17:00:00',
            'purpose' => 'Field visit',
            'destination_location' => 'Main Office',
            'destination_city' => 'Science City of Muñoz',
            'destination_province' => 'Nueva Ecija',
            'destination_region' => 'REGION III',
            'destination_stops' => [],
            'requested_by' => 'Jane Doe',
            'members_of_party' => ['Jane Doe', 'John Doe'],
            'is_shared_ride' => false,
            'shared_ride_reference' => null,
            'contact_number' => '09171234567',
            'notes' => 'Needs driver',
        ], $overrides);
    }

    private function venuePayload(array $overrides = []): array
    {
        return array_merge([
            'venue_type' => 'plenary',
            'date_from' => now()->addDays(8)->toDateString(),
            'date_to' => now()->addDays(8)->toDateString(),
            'time_from' => '09:00:00',
            'time_to' => '16:00:00',
            'expected_attendees' => 120,
            'event_name' => 'Planning Workshop',
            'destination_location' => 'CBC Campus',
            'destination_city' => 'Science City of Muñoz',
            'destination_province' => 'Nueva Ecija',
            'destination_region' => 'REGION III',
            'requested_by' => 'Jane Doe',
            'contact_number' => '09171234567',
            'notes' => 'Need projector',
        ], $overrides);
    }
}
