<?php

namespace Tests\Feature\Inventory;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\WithTestRoles;

class CategoriesTest extends TestCase
{
    use RefreshDatabase, WithTestRoles;

    protected $seeder = \Database\Seeders\DatabaseSeeder::class;

    public function test_categories_index_returns_data(): void
    {
        Sanctum::actingAs($this->createAdminUser());

        $initialCount = Category::count();
        Category::factory()->count(3)->create();

        // Request all items with per_page=*
        $response = $this->getJson(route('api.inventory.categories.index', ['per_page' => '*']));

        $response->assertOk()
            ->assertJsonStructure(['data']);

        $this->assertCount($initialCount + 3, $response->json('data'));
    }
}
