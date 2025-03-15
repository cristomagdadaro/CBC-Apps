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
        $pretest = $this->faker->boolean();
        $timeFrom = $this->faker->time();
        return [
            'id' => $this->faker->uuid(),
            'event_id' => $this->faker->numerify('####'),
            'title' => ucwords($this->faker->catchPhrase .' '.$this->faker->bs),
            'description' => ucwords($this->faker->catchPhrase .' '.$this->faker->bs),
            'details' => $this->faker->paragraph(),

            // Generate a future start date
            'date_from' => $dateFrom = $this->faker->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            // Ensure date_to is the same or later than date_from
            'date_to' => $this->faker->dateTimeBetween($dateFrom, '+1 year')->format('Y-m-d'),

            // Generate a start time
            'time_from' => $timeFrom,
            // Ensure time_to is the same or later than time_from
            'time_to' => $this->faker->time('H:i:s', $timeFrom),

            'venue' => $this->faker->address(),
            'has_pretest' => $pretest,
            'has_posttest' => $pretest,
            'has_preregistration' => $this->faker->boolean(),
            'is_suspended' => $this->faker->boolean(),
            'max_slots' => $maxSlots = $this->faker->randomDigit(),
        ];

    }
}
