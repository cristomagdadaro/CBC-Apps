<?php

namespace Database\Factories;

use App\Models\FormTypeTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FormTypeTemplateFactory extends Factory
{
    protected $model = FormTypeTemplate::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);
        
        return [
            'id' => (string) Str::uuid(),
            'slug' => Str::slug($name),
            'name' => ucfirst($name),
            'description' => $this->faker->sentence(),
            'icon' => $this->faker->randomElement(['📝', '📋', '📊', '📑', '📄']),
            'is_system' => false,
            'created_by' => null,
        ];
    }

    /**
     * System template state
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => true,
            'created_by' => null,
        ]);
    }

    /**
     * Custom template created by user
     */
    public function custom(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_system' => false,
                'created_by' => User::factory(),
            ];
        });
    }

    /**
     * With specific creator
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => false,
            'created_by' => $user->id,
        ]);
    }
}
