<?php

namespace Database\Factories;

use App\Enums\Subform;
use App\Models\EventSubform;
use App\Models\Form;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EventSubform>
 */
class EventSubformFactory extends Factory
{
    protected $model = EventSubform::class;

    public function definition(): array
    {
        $eventId = Form::query()->inRandomOrder()->value('event_id');

        return [
            'id' => (string) Str::uuid(),
            'event_id' => $eventId ?? str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT),
            'form_type' => $this->faker->randomElement([
                Subform::PREREGISTRATION_BIOTECH->value,
                Subform::PREREGISTRATION->value,
                Subform::REGISTRATION->value,
                Subform::PRETEST->value,
                Subform::POSTTEST->value,
                Subform::FEEDBACK->value,
            ]),
            'step_order' => $this->faker->numberBetween(1, 10),
            'is_enabled' => true,
            'open_from' => now()->subDay()->toDateTimeString(),
            'open_to' => now()->addDay()->toDateTimeString(),
            'is_required' => true,
            'max_slots' => $this->faker->numberBetween(0, 50),
            'visibility_rules' => [
                'requires_steps' => [Subform::PREREGISTRATION->value],
            ],
            'completion_rules' => [
                'min_score' => $this->faker->numberBetween(1, 5),
                'required_fields' => ['agreed_tc'],
            ],
        ];
    }
}
