<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'brand' => $this->faker->company,
            'description' => $this->faker->randomElement([null, $this->faker->text]),
            'specifications' => $this->faker->randomElement([null, $this->faker->paragraph]),
            'category_id' => $this->faker->randomElement(Category::all()->pluck('id')->toArray()),
            'supplier_id' => $this->faker->randomElement(Supplier::all()->pluck('id')->toArray()),
            'image' => null,
        ];
    }
}
