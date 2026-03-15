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
        $response = $this->postJson(route('api.guest.rental.vehicles.store'), $this->vehiclePayload());

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('rental_vehicles', [
            'vehicle_type' => 'innova',
            'status' => 'pending',
        ]);
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

    private function seedLocation(): void
    {
        DB::table('loc_cities')->insert([
            'city' => 'Science City of Muñoz',
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
            'date_from' => now()->addDays(5)->toDateString(),
            'date_to' => now()->addDays(6)->toDateString(),
            'time_from' => '08:00:00',
            'time_to' => '17:00:00',
            'purpose' => 'Field visit',
            'destination_location' => 'Main Office',
            'destination_city' => 'Science City of Muñoz',
            'destination_province' => 'Nueva Ecija',
            'destination_region' => 'REGION III',
            'requested_by' => 'Jane Doe',
            'members_of_party' => ['Jane Doe', 'John Doe'],
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
