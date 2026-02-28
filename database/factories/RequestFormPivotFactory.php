<?php

namespace Database\Factories;

use App\Repositories\OptionRepo;
use App\Models\UseRequestForm;
use App\Models\Requester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RequestFormPivot>
 */
class RequestFormPivotFactory extends Factory
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
            'requester_id' => Requester::factory(),
            'form_id' => UseRequestForm::factory(),
            'request_status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'agreed_clause_1' => $this->faker->boolean(),
            'agreed_clause_2' => $this->faker->boolean(),
            'agreed_clause_3' => $this->faker->boolean(),
            'approval_constraint' => $this->faker->sentence(),
            'disapproved_remarks' => $this->faker->sentence(),
            'approved_by' => $this->faker->name(),
        ];
    }
}
