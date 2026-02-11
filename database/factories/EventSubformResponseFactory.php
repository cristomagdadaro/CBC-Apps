<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\Subform;
use App\Models\EventSubform;
use App\Models\LocCity;
use App\Models\Registration;
use App\Models\Option;

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
        'form_parent_id' => EventSubform::query()->inRandomOrder()->value('id'),
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
            'sex' => $this->faker->randomElement(Option::getSexOptions()),
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'is_ip' => $this->faker->boolean(),
            'phone' => $this->faker->phoneNumber(),
            'is_pwd' => $this->faker->boolean(),
            'agreed_tc' => true,
            'agreed_updates' => $this->faker->boolean(),
            'designation' => $this->faker->jobTitle(),
            'organization' => $this->faker->company(),
            'attendance_type' => $this->faker->randomElement(['Online', 'Onsite']),
            'country_address' => 'Philippines',
            'city_address' => LocCity::query()->inRandomOrder()->value('city'),
            'province_address' => LocCity::query()->inRandomOrder()->value('province'),
            'region_address' => LocCity::query()->inRandomOrder()->value('region'),
        ],
        ];
    }
}
