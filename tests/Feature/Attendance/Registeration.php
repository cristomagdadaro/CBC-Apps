<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class Registeration extends TestCase
{
    /**
     * @test
     */
    public function register_participant(): void
    {
        $response = $this->post('/api/guest/forms/registration', [
            "name" => "Cristo Rey Magdadaro",
            "email" => "cris@ai4gov.net",
            "phone" => "+639127092422",
            "sex" => "Male",
            "age" => "21",
            "organization" => "DA-Crop Biotechnology Center",
            "is_ip" => true,
            "is_pwd" => true,
            "city_address" => null,
            "province_address" => null,
            "country_address" => null,
            "agreed_tc" => false,
            "event_id" => "9053"
        ]);

        print_r($response->collect());

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function create_form(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/api/forms/create', [
            'event_id' => '4751',
            'title' => 'National Biotechnology Week',
            'date_from' => '2025-01-01',
            'date_to' => '2025-01-02',
            'time_from' => '09:00:00',
            'time_to' => '10:00:00',
            'venue' => 'Plenary Hall',
            'has_pretest' => true,
        ]);

        print_r($response->collect());

        $response->assertStatus(201);
    }


    /**
     * @test
     */
    public function get_update_form(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/forms/update/1234');

        print_r($response->collect());

        $response->assertStatus(201);
    }

    /**
     * @test
     */
    public function search_form(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/api/forms?search=Multimedia&is_exact=false');

        print_r($response->collect());

        $response->assertStatus(201);
    }
}
