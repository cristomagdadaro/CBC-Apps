<?php

namespace Database\Factories;

use App\Models\Personnel;
use App\Models\RentalVenue;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class RentalVenueFactory extends Factory
{
    protected $model = RentalVenue::class;

    public function definition(): array
    {
        $dateFrom = Carbon::now()->addDays($this->faker->numberBetween(1, 10));
        $dateTo = $dateFrom->copy()->addDays($this->faker->numberBetween(1, 5));
        $personnel = Personnel::query()->inRandomOrder()->first();

        $requestedBy = $personnel
            ? trim(implode(' ', array_filter([
                $personnel->fname,
                $personnel->mname,
                $personnel->lname,
                $personnel->suffix,
            ])))
            : $this->faker->name();

        return [
            'venue_type' => $this->faker->randomElement(['plenary', 'training_room', 'mph']),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'time_from' => $this->faker->randomElement(['08:00', '09:00', '10:00']),
            'time_to' => $this->faker->randomElement(['17:00', '18:00', '19:00']),
            'expected_attendees' => $this->faker->numberBetween(10, 500),
            'event_name' => $this->faker->sentence(3),
            'requested_by' => $requestedBy,
            'contact_number' => $personnel?->phone ?: $this->faker->phoneNumber(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected', 'completed', 'cancelled']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
