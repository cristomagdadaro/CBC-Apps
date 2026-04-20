<?php

namespace Database\Factories;

use App\Repositories\OptionRepo;
use App\Models\UseRequestForm;
use App\Models\Requester;
use App\Models\RequestFormPivot;
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
            'request_status' => $this->faker->randomElement([
                RequestFormPivot::STATUS_PENDING,
                RequestFormPivot::STATUS_APPROVED,
                RequestFormPivot::STATUS_RELEASED,
                RequestFormPivot::STATUS_RETURNED,
                RequestFormPivot::STATUS_REJECTED,
            ]),
            'agreed_clause_1' => $this->faker->boolean(),
            'agreed_clause_2' => $this->faker->boolean(),
            'agreed_clause_3' => $this->faker->boolean(),
            'approval_constraint' => $this->faker->sentence(),
            'disapproved_remarks' => $this->faker->sentence(),
            'approved_by' => $this->faker->name(),
            'approved_at' => now()->subDays(2),
            'released_by' => $this->faker->name(),
            'released_at' => now()->subDay(),
            'returned_by' => null,
            'returned_at' => null,
            'overdue_notified_at' => null,
        ];
    }
}
