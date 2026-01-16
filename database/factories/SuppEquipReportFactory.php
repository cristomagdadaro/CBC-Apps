<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\SuppEquipReport;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SuppEquipReport>
 */
class SuppEquipReportFactory extends Factory
{
    protected $model = SuppEquipReport::class;

    public function definition(): array
    {
        return [
            'transaction_id' => Transaction::factory(),
            'item_id' => Item::factory(),
            'user_id' => User::factory(),
            'report_type' => $this->faker->randomElement(['incident', 'maintenance', 'stock_count']),
            'report_data' => [
                'details' => $this->faker->sentence,
                'next_steps' => $this->faker->words(3, true),
            ],
            'notes' => $this->faker->optional()->paragraph,
            'reported_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
