<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\Subform;
use App\Models\EventRequirement;
use App\Models\Registration;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventSubformResponse>
 */
class EventSubformResponseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
        'form_parent_id' => EventRequirement::query()->inRandomOrder()->value('id'),
        'participant_id' => Registration::query()->inRandomOrder()->value('id'),
        'subform_type' => $this->faker->randomElement([
                Subform::PREREGISTRATION_BIOTECH->value,
                Subform::PREREGISTRATION->value,
                Subform::REGISTRATION->value,
                Subform::PRETEST->value,
                Subform::POSTTEST->value,
                Subform::FEEDBACK->value,
            ]),
        'response_data' => [
            'age' => $this->faker->numberBetween(18, 70),
            'sex' => $this->faker->randomElement(['Male', 'Female', 'Prefer not to say']),
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'is_ip' => $this->faker->boolean(),
            'phone' => $this->faker->phoneNumber(),
            'is_pwd' => $this->faker->boolean(),
            'agreed_tc' => true,
            'designation' => $this->faker->jobTitle(),
            'city_address' => $this->faker->city(),
            'organization' => $this->faker->company(),
            'attendance_type' => $this->faker->randomElement(['Online', 'Onsite']),
            'country_address' => $this->faker->country(),
            'province_address' => $this->faker->state(),
        ],
        ];
    }
}
