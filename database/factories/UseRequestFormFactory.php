<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Repositories\OptionRepo;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UseRequestForm>
 */
class UseRequestFormFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = app(OptionRepo::class)->getRequestTypes()->pluck('label')->toArray();
        
        // Use default request types if options table is empty
        if (empty($types)) {
            $types = ['Office Supplies', 'Laboratory Access', 'ICT Equipment'];
        }

        return [
            'id' => $this->faker->uuid(),
            'request_type' => $this->faker->randomElements($types, rand(1, min(2, count($types)))),
            'request_details' => $this->faker->sentence(),
            'request_purpose' => $this->faker->sentence(),
            'project_title' => $this->faker->sentence(),
            'date_of_use' => $this->faker->date(),
            'date_of_use_end' => $this->faker->date(),
            'time_of_use' => $this->faker->time(),
            'time_of_use_end' => $this->faker->time(),
            'labs_to_use' => [$this->faker->word(), $this->faker->word()],
            'equipments_to_use' => [$this->faker->word(), $this->faker->word()],
            'consumables_to_use' => [$this->faker->word(), $this->faker->word()],
        ];
    }
}
