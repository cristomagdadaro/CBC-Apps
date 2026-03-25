<?php

namespace Database\Factories;

use App\Models\Research\ResearchExperiment;
use App\Models\Research\ResearchStudy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResearchExperiment>
 */
class ResearchExperimentFactory extends Factory
{
    protected $model = ResearchExperiment::class;

    public function definition(): array
    {
        return [
            'study_id' => ResearchStudy::factory(),
            'code' => 'RE-' . $this->faker->unique()->numerify('####'),
            'title' => $this->faker->sentence(4),
            'geographic_location' => $this->faker->city(),
            'season' => $this->faker->randomElement(['wet', 'dry']),
            'commodity' => $this->faker->randomElement(['Rice', 'Corn', 'Soybean']),
            'sample_type' => $this->faker->randomElement(['Seeds', 'Leaf', 'Stem']),
            'sample_descriptor' => $this->faker->word(),
            'pr_code' => strtoupper($this->faker->bothify('PR-###')),
            'cross_combination' => $this->faker->bothify('PARENT-## x LINE-##'),
            'parental_background' => $this->faker->sentence(),
            'filial_generation' => $this->faker->randomElement(['F1', 'F2', 'F3']),
            'generation' => $this->faker->randomElement(['BC1F1', 'BC2F2', 'F4']),
            'plot_number' => $this->faker->numerify('P-##'),
            'field_number' => $this->faker->numerify('F-##'),
            'replication_number' => $this->faker->numberBetween(1, 4),
            'planned_plant_count' => $this->faker->numberBetween(20, 300),
            'background_notes' => $this->faker->sentence(),
        ];
    }
}
