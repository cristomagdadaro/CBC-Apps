<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

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
        $timeFrom = $this->faker->time('H:i:s');

        // Randomly add days, months, or years to generate start date
        $dateFrom = now();
            /*->addDays($this->faker->numberBetween(0, 30)) // Up to 30 days ahead
            ->addMonths($this->faker->numberBetween(0, 6)) // Up to 6 months ahead
            ->addYears($this->faker->numberBetween(0, 1)) // Up to 1 year ahead
            ->format('Y-m-d');*/

        // Generate date_to ensuring it is after date_from
        $dateTo = Carbon::parse($dateFrom)
            ->addDays($this->faker->numberBetween(0, 10)) // Up to 10 days later
            ->addMonths($this->faker->numberBetween(0, 2)) // Up to 2 months later
            ->format('Y-m-d');

        // Randomly add hours and minutes for time_from and time_to
        $timeTo = Carbon::parse($timeFrom)
            ->addHours($this->faker->numberBetween(1, 5)) // 1 to 5 hours later
            ->addMinutes($this->faker->numberBetween(0, 59)) // Random minutes
            ->format('H:i:s');

        return [
            'id' => $this->faker->uuid(),
            'event_id' => $this->faker->numerify('####'),
            'title' => ucwords($this->faker->catchPhrase . ' ' . $this->faker->bs),
            'description' => ucwords($this->faker->catchPhrase . ' ' . $this->faker->bs),
            'details' => $this->faker->paragraph(),

            // Randomized dates
            'date_from' => $dateFrom,
            'date_to' => $dateTo,

            // Randomized times
            'time_from' => $timeFrom,
            'time_to' => $timeTo,

            'venue' => $this->faker->address(),
            'is_suspended' => $this->faker->boolean(),
            'is_expired' => $this->faker->boolean(),
            'style_tokens' => null,
        ];
    }

}
