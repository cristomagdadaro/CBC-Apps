<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Form>
 */
class FormFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'event_id' => $this->faker->numerify('####'),
            'title' => $this->faker->title(),
            'description' => $this->faker->paragraph(),
            'details' => $this->faker->paragraph(),
            'date_from' => $this->faker->date(),
            'date_to' => $this->faker->date(),
            'time_from' => $this->faker->time(),
            'time_to' => $this->faker->time(),
            'venue' => $this->faker->city(),
            'has_pretest' => $this->faker->boolean(),
            'has_posttest' => $this->faker->boolean(),
        ];
    }
}
