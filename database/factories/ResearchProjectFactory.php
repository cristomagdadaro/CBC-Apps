<?php

namespace Database\Factories;

use App\Models\Research\ResearchProject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResearchProject>
 */
class ResearchProjectFactory extends Factory
{
    protected $model = ResearchProject::class;

    public function definition(): array
    {
        return [
            'code' => 'RP-' . $this->faker->unique()->numerify('####'),
            'title' => $this->faker->sentence(6),
            'commodity' => $this->faker->randomElement(['Rice', 'Corn', 'Soybean']),
            'duration_start' => $this->faker->date(),
            'duration_end' => $this->faker->date(),
            'overall_budget' => $this->faker->randomFloat(2, 100000, 1500000),
            'objective' => $this->faker->paragraph(),
            'funding_agency' => $this->faker->company(),
            'funding_code' => strtoupper($this->faker->bothify('FG-###-??')),
            'project_leader' => [
                'name' => $this->faker->name(),
                'position' => $this->faker->jobTitle(),
            ],
        ];
    }
}
