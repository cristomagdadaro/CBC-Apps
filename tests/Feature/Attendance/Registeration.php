<?php

namespace Tests\Feature\Attendance;

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
            "event_id" => "4751"
        ]);

        print_r($response->collect());

        $response->assertStatus(200);
    }
}
