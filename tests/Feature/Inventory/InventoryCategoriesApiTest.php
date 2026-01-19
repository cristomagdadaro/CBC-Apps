<?php

namespace Tests\Feature\Inventory;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InventoryCategoriesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_index_returns_data(): void
    {
        Sanctum::actingAs(User::factory()->create());

        Category::factory()->count(3)->create();

        $response = $this->getJson(route('api.inventory.categories.index'));

        $response->assertOk()
            ->assertJsonStructure(['data']);

        $this->assertCount(3, $response->json('data'));
    }
}
