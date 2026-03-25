<?php

namespace Database\Factories\Research;

use App\Models\Research\ResearchExperiment;
use App\Models\Research\ResearchSample;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResearchSampleFactory extends Factory
{
    protected $model = ResearchSample::class;

    public function definition(): array
    {
        return [
            'experiment_id' => ResearchExperiment::factory(),
            'uid' => 'RI' . strtoupper($this->faker->unique()->bothify('######??')),
            'sequence_number' => $this->faker->unique()->numberBetween(1, 9999),
            'commodity' => $this->faker->randomElement(['Rice', 'Corn', 'Soybean']),
            'sample_type' => $this->faker->randomElement(['Seeds', 'Leaf', 'Stem']),
            'accession_name' => strtoupper($this->faker->bothify('ACC-####')),
            'pr_code' => strtoupper($this->faker->bothify('PR-###')),
            'field_label' => $this->faker->bothify('FL-##'),
            'line_label' => $this->faker->bothify('LN-##'),
            'plant_label' => $this->faker->bothify('PL-##'),
            'generation' => $this->faker->randomElement(['F1', 'F2', 'F3']),
            'plot_number' => $this->faker->numerify('P-##'),
            'field_number' => $this->faker->numerify('F-##'),
            'replication_number' => $this->faker->numberBetween(1, 4),
            'current_status' => $this->faker->randomElement(['Field', 'Lab', 'Storage']),
            'current_location' => $this->faker->city(),
            'storage_location' => 'Cold Room ' . $this->faker->randomElement(['A', 'B', 'C']),
            'germination_date' => $this->faker->date(),
            'sowing_date' => $this->faker->date(),
            'harvest_date' => $this->faker->date(),
            'is_priority' => $this->faker->boolean(20),
            'legacy_reference' => $this->faker->bothify('LEG-###-??'),
        ];
    }
}
