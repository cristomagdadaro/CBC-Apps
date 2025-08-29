<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UseRequestForm>
 */
class UseRequestFormFactory extends Factory
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
            'request_type' => $this->faker->randomElement(['Supply', 'Equipment', 'Full Access']),
            'request_details' => $this->faker->sentence(),
            'request_purpose' => $this->faker->sentence(),
            'project_title' => $this->faker->sentence(),
            'date_of_use' => $this->faker->date(),
            'time_of_use' => $this->faker->time(),
            'labs_to_use' => json_encode([$this->faker->word(), $this->faker->word()]),
            'equipments_to_use' => json_encode([$this->faker->word(), $this->faker->word()]),
            'consumables_to_use' => json_encode([$this->faker->word(), $this->faker->word()]),
        ];
    }
}
