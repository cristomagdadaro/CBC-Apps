<?php

namespace Tests\Feature\Location;

use App\Enums\Role as RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\WithTestRoles;

class LocationControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithTestRoles;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('loc_cities')->insert([
            [
                'city' => 'Science City of Muñoz',
                'province' => 'Nueva Ecija',
                'region' => 'REGION III',
                'latitude' => 15.7161,
                'longitude' => 120.9031,
            ],
            [
                'city' => 'Cabanatuan City',
                'province' => 'Nueva Ecija',
                'region' => 'REGION III',
                'latitude' => 15.4859,
                'longitude' => 120.9665,
            ],
            [
                'city' => 'Baguio City',
                'province' => 'Benguet',
                'region' => 'CAR',
                'latitude' => 16.4023,
                'longitude' => 120.5960,
            ],
        ]);
    }

    public function test_guest_can_list_regions_from_public_endpoint(): void
    {
        $this->getJson(route('api.locations.regions'))
            ->assertOk()
            ->assertJson([
                'data' => ['CAR', 'REGION III'],
            ]);
    }

    public function test_guest_can_filter_provinces_by_region(): void
    {
        $this->getJson(route('api.locations.provinces', ['region' => 'REGION III']))
            ->assertOk()
            ->assertJson([
                'data' => ['Nueva Ecija'],
            ]);
    }

    public function test_guest_can_filter_cities_by_region_and_province(): void
    {
        $this->getJson(route('api.locations.cities', [
            'region' => 'REGION III',
            'province' => 'Nueva Ecija',
        ]))
            ->assertOk()
            ->assertJson([
                'data' => ['Cabanatuan City', 'Science City of Muñoz'],
            ]);
    }

    public function test_authenticated_authorized_user_can_access_protected_locations_route(): void
    {
        $user = $this->createUserWithRole(RoleEnum::ICT_MANAGER->value);
        Sanctum::actingAs($user);

        $this->getJson(route('api.locations.regions.auth'))
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_protected_locations_route_requires_authentication(): void
    {
        $this->getJson(route('api.locations.regions.auth'))
            ->assertUnauthorized();
    }

    public function test_protected_locations_route_rejects_users_without_required_role(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_admin' => false]));

        $this->getJson(route('api.locations.regions.auth'))
            ->assertForbidden();
    }
}
