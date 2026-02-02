<?php

namespace Database\Factories;

use App\Models\EventSubform;
use App\Models\Form;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Registration>
 */
class RegistrationFactory extends Factory
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
            'event_subform_id' => EventSubform::inRandomOrder()->first()->id,
            'participant_id' => Participant::inRandomOrder()->first()->id,
            'attendance_type' => $this->faker->randomElement(['Online', 'In-Person']),
        ];
    }
}
