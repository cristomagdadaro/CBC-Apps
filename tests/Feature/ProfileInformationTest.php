<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_information_can_be_updated(): void
    {
        $this->actingAs($user = User::factory()->create());

        $response = $this->put('/user/profile-information', [
            'name' => 'DA-CBC Administration',
            'email' => 'dacropbiotechcenter@gmail.com',
        ]);

        $this->assertEquals('DA-CBC Administration', $user->fresh()->name);
        $this->assertEquals('dacropbiotechcenter@gmail.com', $user->fresh()->email);
    }
}
