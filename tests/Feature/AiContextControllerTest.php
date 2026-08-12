<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AiContextControllerTest extends TestCase
{
    /** @test */
    public function it_rejects_requests_without_valid_sync_token()
    {
        $response = $this->getJson('/api/internal/ai-context/inventory');
        $response->assertStatus(401)
                 ->assertJson(['error' => 'Unauthorized.']);
    }

    /** @test */
    public function it_returns_merged_inventory_and_equipment_when_authorized()
    {
        // Add token directly to request headers
        $token = env('SPROUTAI_INTERNAL_SYNC_TOKEN');
        if (!$token) {
            $this->markTestSkipped('SPROUTAI_INTERNAL_SYNC_TOKEN is not set.');
        }

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->getJson('/api/internal/ai-context/inventory');
                         
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'current_page',
                     'data' => [
                         '*' => [
                             'id',
                             'name',
                             'short_description',
                             'description',
                             'type',
                             'status',
                             'url',
                             'created_at',
                             'updated_at',
                         ]
                     ],
                     'last_page',
                 ]);
    }
}
