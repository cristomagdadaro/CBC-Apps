<?php

namespace Database\Factories;

use App\Enums\Subform;
use App\Models\EventRequirement;
use App\Models\Form;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EventRequirement>
 */
class EventRequirementFactory extends Factory
{
    protected $model = EventRequirement::class;

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
            'is_required' => true,
            'config' => [
                'open_from' => now()->subDay()->toDateTimeString(),
                'open_to' => now()->addDay()->toDateTimeString(),
            ],
        ];
    }
}
