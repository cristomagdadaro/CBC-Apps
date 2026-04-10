<?php

namespace Tests\Feature\Rental;

use App\Enums\Role as RoleEnum;
use App\Models\Option;
use App\Models\RentalVehicle;
use App\Models\RentalVenue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\WithTestRoles;

class RentalControllersTest extends TestCase
{
    use RefreshDatabase;
    use WithTestRoles;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedLocation();
        $this->seedRentalOptions();
    }

    public function test_guest_can_create_vehicle_rental_through_public_endpoint(): void
    {
        $response = $this->postJson(route('api.guest.rental.vehicles.store'), $this->vehiclePayload([
            'vehicle_type' => null,
            'trip_type' => 'shuttle_service',
            'destination_stops' => ['Science Hub', 'Extension Office'],
        ]));

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.vehicle_type', null)
            ->assertJsonPath('data.trip_type', 'shuttle_service');

        $this->assertDatabaseHas('rental_vehicles', [
            'vehicle_type' => null,
            'trip_type' => 'shuttle_service',
            'status' => 'pending',
        ]);
    }

    public function test_guest_vehicle_rental_allows_same_day_non_overlapping_time_window(): void
    {
        $date = now()->addDays(7)->toDateString();

        RentalVehicle::query()->create(array_merge($this->vehiclePayload([
            'vehicle_type' => 'innova',
            'date_from' => $date,
            'date_to' => $date,
            'time_from' => '08:00:00',
            'time_to' => '10:00:00',
            'status' => 'approved',
        ]), [
            'status' => 'approved',
        ]));

        $this->postJson(route('api.guest.rental.vehicles.store'), $this->vehiclePayload([
            'vehicle_type' => 'innova',
            'date_from' => $date,
            'date_to' => $date,
            'time_from' => '10:00:00',
            'time_to' => '12:00:00',
        ]))
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_user_with_vehicle_manage_permission_can_update_vehicle_rental(): void
    {
        $user = $this->createUserWithRole(RoleEnum::ADMINISTRATIVE_ASSISTANT->value);
        Sanctum::actingAs($user);

        $rental = RentalVehicle::query()->create(array_merge($this->vehiclePayload(), [
            'status' => 'pending',
        ]));

        $response = $this->putJson(route('api.rental.vehicles.update', $rental->id), [
            'notes' => 'Approved by assistant',
            'status' => 'approved',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.notes', 'Approved by assistant');
    }

    public function test_vehicle_status_update_requires_approval_permission(): void
    {
        $user = $this->createUserWithRole(RoleEnum::ICT_MANAGER->value);
        Sanctum::actingAs($user);

        $rental = RentalVehicle::query()->create(array_merge($this->vehiclePayload(), [
            'status' => 'pending',
        ]));

        $this->putJson(route('api.rental.vehicles.update-status', $rental->id), [
            'status' => 'approved',
        ])->assertForbidden();
    }

    public function test_vehicle_approval_requires_vehicle_assignment(): void
    {
        $user = $this->createUserWithRole(RoleEnum::ADMINISTRATIVE_ASSISTANT->value);
        Sanctum::actingAs($user);

        $rental = RentalVehicle::query()->create(array_merge($this->vehiclePayload([
            'vehicle_type' => null,
        ]), [
            'status' => 'pending',
        ]));

        $this->putJson(route('api.rental.vehicles.update-status', $rental->id), [
            'status' => 'approved',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('vehicle_type');
    }

    public function test_vehicle_approver_can_assign_vehicle_on_approval(): void
    {
        $user = $this->createUserWithRole(RoleEnum::ADMINISTRATIVE_ASSISTANT->value);
        Sanctum::actingAs($user);

        $rental = RentalVehicle::query()->create(array_merge($this->vehiclePayload([
            'vehicle_type' => null,
        ]), [
            'status' => 'pending',
        ]));

        $this->putJson(route('api.rental.vehicles.update-status', $rental->id), [
            'status' => 'approved',
            'vehicle_type' => 'pickup',
            'notes' => 'Assigned during approval',
        ])->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.vehicle_type', 'pickup')
            ->assertJsonPath('data.notes', 'Assigned during approval');
    }

    public function test_guest_public_vehicle_rental_list_is_sanitized(): void
    {
        $rental = RentalVehicle::query()->create(array_merge($this->vehiclePayload(), [
            'status' => 'pending',
        ]));

        $this->getJson(route('api.guest.rental.vehicles.index'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $rental->id)
            ->assertJsonPath('data.0.requested_by', $rental->requested_by)
            ->assertJsonPath('data.0.vehicle_type', 'innova')
            ->assertJsonMissingPath('data.0.contact_number')
            ->assertJsonMissingPath('data.0.destination_location')
            ->assertJsonMissingPath('data.0.notes');
    }

    public function test_guest_public_vehicle_rental_detail_is_sanitized(): void
    {
        $rental = RentalVehicle::query()->create(array_merge($this->vehiclePayload(), [
            'status' => 'pending',
        ]));

        $this->getJson(route('api.guest.rental.vehicles.show', $rental->id))
            ->assertOk()
            ->assertJsonPath('data.id', $rental->id)
            ->assertJsonPath('data.requested_by', $rental->requested_by)
            ->assertJsonMissingPath('data.contact_number')
            ->assertJsonMissingPath('data.destination_location')
            ->assertJsonMissingPath('data.notes');
    }

    public function test_authenticated_callers_still_receive_sanitized_public_vehicle_detail(): void
    {
        Sanctum::actingAs($this->createUserWithRole(RoleEnum::ADMINISTRATIVE_ASSISTANT->value));

        $rental = RentalVehicle::query()->create(array_merge($this->vehiclePayload(), [
            'status' => 'pending',
        ]));

        $this->getJson(route('api.guest.rental.vehicles.show', $rental->id))
            ->assertOk()
            ->assertJsonPath('data.requested_by', $rental->requested_by)
            ->assertJsonMissingPath('data.contact_number')
            ->assertJsonMissingPath('data.notes');
    }

    public function test_guest_public_vehicle_availability_hides_conflict_identities(): void
    {
        $dateFrom = now()->addDays(9)->toDateString();
        $dateTo = now()->addDays(10)->toDateString();

        RentalVehicle::query()->create(array_merge($this->vehiclePayload([
            'vehicle_type' => 'van',
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'time_from' => '09:00:00',
            'time_to' => '17:00:00',
            'status' => 'approved',
        ]), [
            'status' => 'approved',
        ]));

        $this->getJson(route('api.guest.rental.vehicles.check-availability', [
            'vehicleType' => 'van',
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]))
            ->assertOk()
            ->assertJsonPath('available', false)
            ->assertJsonStructure([
                'available',
                'vehicle_type',
                'date_from',
                'date_to',
                'message',
                'conflicts' => [[
                    'starts_at',
                    'ends_at',
                    'status',
                ]],
            ])
            ->assertJsonMissingPath('conflicts.0.requested_by')
            ->assertJsonMissingPath('conflicts.0.contact_number')
            ->assertJsonMissingPath('conflicts.0.event_name');
    }

    public function test_guest_can_create_venue_rental_through_public_endpoint(): void
    {
        $response = $this->postJson(route('api.guest.rental.venues.store'), $this->venuePayload());

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('rental_venues', [
            'venue_type' => 'plenary',
            'status' => 'pending',
        ]);
    }

    public function test_guest_venue_rental_allows_same_day_non_overlapping_time_window(): void
    {
        $date = now()->addDays(12)->toDateString();

        RentalVenue::query()->create(array_merge($this->venuePayload([
            'date_from' => $date,
            'date_to' => $date,
            'time_from' => '08:00:00',
            'time_to' => '10:00:00',
            'status' => 'approved',
        ]), [
            'status' => 'approved',
        ]));

        $this->postJson(route('api.guest.rental.venues.store'), $this->venuePayload([
            'date_from' => $date,
            'date_to' => $date,
            'time_from' => '10:00:00',
            'time_to' => '12:00:00',
        ]))
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_user_with_venue_manage_permission_can_update_venue_rental(): void
    {
        $user = $this->createUserWithRole(RoleEnum::ADMINISTRATIVE_ASSISTANT->value);
        Sanctum::actingAs($user);

        $rental = RentalVenue::query()->create(array_merge($this->venuePayload(), [
            'status' => 'pending',
        ]));

        $response = $this->putJson(route('api.rental.venues.update', $rental->id), [
            'status' => 'approved',
            'expected_attendees' => 240,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.expected_attendees', 240);
    }

    public function test_venue_status_update_requires_approval_permission(): void
    {
        $user = $this->createUserWithRole(RoleEnum::ICT_MANAGER->value);
        Sanctum::actingAs($user);

        $rental = RentalVenue::query()->create(array_merge($this->venuePayload(), [
            'status' => 'pending',
        ]));

        $this->putJson(route('api.rental.venues.update-status', $rental->id), [
            'status' => 'approved',
        ])->assertForbidden();
    }

    public function test_guest_public_venue_rental_list_is_sanitized(): void
    {
        $rental = RentalVenue::query()->create(array_merge($this->venuePayload(), [
            'status' => 'pending',
        ]));

        $this->getJson(route('api.guest.rental.venues.index'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $rental->id)
            ->assertJsonPath('data.0.venue_type', 'plenary')
            ->assertJsonMissingPath('data.0.event_name')
            ->assertJsonPath('data.0.requested_by', $rental->requested_by)
            ->assertJsonMissingPath('data.0.contact_number')
            ->assertJsonMissingPath('data.0.notes');
    }

    public function test_guest_public_venue_rental_detail_is_sanitized(): void
    {
        $rental = RentalVenue::query()->create(array_merge($this->venuePayload(), [
            'status' => 'pending',
        ]));

        $this->getJson(route('api.guest.rental.venues.show', $rental->id))
            ->assertOk()
            ->assertJsonPath('data.id', $rental->id)
            ->assertJsonMissingPath('data.event_name')
            ->assertJsonPath('data.requested_by', $rental->requested_by)
            ->assertJsonMissingPath('data.contact_number')
            ->assertJsonMissingPath('data.notes');
    }

    public function test_authenticated_callers_still_receive_sanitized_public_venue_detail(): void
    {
        Sanctum::actingAs($this->createUserWithRole(RoleEnum::ADMINISTRATIVE_ASSISTANT->value));

        $rental = RentalVenue::query()->create(array_merge($this->venuePayload(), [
            'status' => 'pending',
        ]));

        $this->getJson(route('api.guest.rental.venues.show', $rental->id))
            ->assertOk()
            ->assertJsonMissingPath('data.event_name')
            ->assertJsonPath('data.requested_by', $rental->requested_by)
            ->assertJsonMissingPath('data.contact_number')
            ->assertJsonMissingPath('data.notes');
    }

    public function test_guest_public_venue_availability_hides_conflict_identities(): void
    {
        $date = now()->addDays(14)->toDateString();

        RentalVenue::query()->create(array_merge($this->venuePayload([
            'venue_type' => 'plenary',
            'date_from' => $date,
            'date_to' => $date,
            'time_from' => '09:00:00',
            'time_to' => '16:00:00',
            'status' => 'approved',
        ]), [
            'status' => 'approved',
        ]));

        $this->getJson(route('api.guest.rental.venues.check-availability', [
            'venueType' => 'plenary',
            'dateFrom' => $date,
            'dateTo' => $date,
        ]))
            ->assertOk()
            ->assertJsonPath('available', false)
            ->assertJsonStructure([
                'available',
                'venue_type',
                'date_from',
                'date_to',
                'message',
                'conflicts' => [[
                    'starts_at',
                    'ends_at',
                    'status',
                ]],
            ])
            ->assertJsonMissingPath('conflicts.0.requested_by')
            ->assertJsonMissingPath('conflicts.0.contact_number')
            ->assertJsonMissingPath('conflicts.0.event_name');
    }

    private function seedLocation(): void
    {
        DB::table('loc_cities')->insert([
            'id' => 1,
            'city' => 'Science City of Mu?oz',
            'province' => 'Nueva Ecija',
            'region' => 'REGION III',
            'latitude' => 15.7161,
            'longitude' => 120.9031,
        ]);
    }

    private function seedRentalOptions(): void
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
            'destination_city' => 'Science City of Mu?oz',
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
            'destination_city' => 'Science City of Mu?oz',
            'destination_province' => 'Nueva Ecija',
            'destination_region' => 'REGION III',
            'requested_by' => 'Jane Doe',
            'contact_number' => '09171234567',
            'notes' => 'Need projector',
        ], $overrides);
    }
}
