<?php

namespace Database\Factories;

use App\Models\Research\ResearchMonitoringRecord;
use App\Models\Research\ResearchSample;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResearchMonitoringRecord>
 */
class ResearchMonitoringRecordFactory extends Factory
{
    protected $model = ResearchMonitoringRecord::class;

    public function definition(): array
    {
        return [
            'sample_id' => ResearchSample::factory(),
            'stage' => $this->faker->randomElement(['germination', 'sowing', 'agromorphology', 'post_harvest']),
            'recorded_on' => $this->faker->date(),
            'parameter_set' => [
                'height_cm' => $this->faker->randomFloat(2, 5, 150),
                'vigor_score' => $this->faker->numberBetween(1, 9),
            ],
            'notes' => $this->faker->sentence(),
            'selected_for_export' => $this->faker->boolean(30),
        ];
    }
}
