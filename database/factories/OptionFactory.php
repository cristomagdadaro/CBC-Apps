<?php

namespace Database\Factories;

use App\Models\Option;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Option>
 */
class OptionFactory extends Factory
{
    protected $model = Option::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->slug(),
            'value' => $this->faker->word(),
            'label' => $this->faker->sentence(2),
            'description' => $this->faker->sentence(),
            'type' => 'text',
            'group' => $this->faker->word(),
        ];
    }

    /**
     * Indicate that the option is of type select.
     */
    public function selectType(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'select',
                'options' => json_encode([
                    ['value' => 'option1', 'label' => 'Option 1'],
                    ['value' => 'option2', 'label' => 'Option 2'],
                    ['value' => 'option3', 'label' => 'Option 3'],
                ]),
            ];
        });
    }

    /**
     * Indicate that the option is of type checkbox.
     */
    public function checkboxType(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'checkbox',
                'value' => 'true',
            ];
        });
    }

    /**
     * Indicate that the option is of type textarea.
     */
    public function textareaType(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'textarea',
                'value' => $this->faker->paragraph(),
            ];
        });
    }
}
