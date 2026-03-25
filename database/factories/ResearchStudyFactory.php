<?php

namespace Database\Factories;

use App\Models\Research\ResearchProject;
use App\Models\Research\ResearchStudy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResearchStudy>
 */
class ResearchStudyFactory extends Factory
{
    protected $model = ResearchStudy::class;

    public function definition(): array
    {
        return [
            'project_id' => ResearchProject::factory(),
            'code' => 'RS-' . $this->faker->unique()->numerify('####'),
            'title' => $this->faker->sentence(5),
            'objective' => $this->faker->paragraph(),
            'budget' => $this->faker->randomFloat(2, 50000, 500000),
            'study_leader' => [
                'name' => $this->faker->name(),
                'position' => 'Research Lead',
            ],
            'staff_members' => [
                ['name' => $this->faker->name(), 'position' => 'Research Assistant'],
                ['name' => $this->faker->name(), 'position' => 'Field Technician'],
            ],
            'supervisor' => [
                'name' => $this->faker->name(),
                'position' => 'Research Supervisor',
            ],
        ];
    }
}
