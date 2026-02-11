<?php

namespace Database\Factories;

use App\Models\Option;
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
        $requester = Requester::all()->random()->id;
        $form =  UseRequestForm::all()->random()->id;

        return [
            'id' => $this->faker->uuid(),
            'requester_id' => $requester,
            'form_id' => $form,
            'request_status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'agreed_clause_1' => $this->faker->boolean(),
            'agreed_clause_2' => $this->faker->boolean(),
            'agreed_clause_3' => $this->faker->boolean(),
            'approval_constraint' => $this->faker->sentence(),
            'disapproved_remarks' => $this->faker->sentence(),
            'approved_by' => Option::getApprovingOfficers(),
        ];
    }
}
