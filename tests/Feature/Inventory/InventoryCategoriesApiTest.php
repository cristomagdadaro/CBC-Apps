<?php

namespace Tests\Feature\Inventory;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\WithTestRoles;

class InventoryCategoriesApiTest extends TestCase
{
    use RefreshDatabase, WithTestRoles;

    public function test_categories_index_returns_data(): void
    {
        Sanctum::actingAs($this->createAdminUser());

        Category::factory()->count(3)->create();

        $response = $this->getJson(route('api.inventory.categories.index'));

        $response->assertOk()
            ->assertJsonStructure(['data']);

        $this->assertCount(3, $response->json('data'));
    }
}
