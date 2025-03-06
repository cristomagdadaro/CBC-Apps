<?php

namespace Database\Factories;

use App\Enums\Sex;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Participant>
 */
class ParticipantFactory extends Factory
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
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'sex' => $this->faker->randomElement([Sex::Male->value, Sex::Female->value, Sex::PreferNotToSay->value]),
            'age' => $this->faker->numberBetween(1, 100),
            'organization' => $this->faker->company(),
            'is_ip' => $this->faker->boolean(),
            'is_pwd' => $this->faker->boolean(),
            'city_address' => $this->faker->city(),
            'province_address' => $this->faker->city(),
            'country_address' => $this->faker->country(),
            'agreed_tc' => $this->faker->boolean(),
        ];
    }
}
