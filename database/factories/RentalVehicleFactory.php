<?php

namespace Database\Factories;

use App\Models\RentalVehicle;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class RentalVehicleFactory extends Factory
{
    protected $model = RentalVehicle::class;

    public function definition(): array
    {
        $dateFrom = Carbon::now()->addDays($this->faker->numberBetween(1, 10));
        $dateTo = $dateFrom->copy()->addDays($this->faker->numberBetween(1, 5));

        return [
            'vehicle_type' => $this->faker->randomElement(['innova', 'pickup', 'van', 'suv']),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'time_from' => $this->faker->randomElement(['08:00', '09:00', '10:00']),
            'time_to' => $this->faker->randomElement(['17:00', '18:00', '19:00']),
            'purpose' => $this->faker->sentence(),
            'requested_by' => $this->faker->name(),
            'contact_number' => $this->faker->phoneNumber(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected', 'completed', 'cancelled']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
