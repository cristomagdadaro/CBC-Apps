<?php

namespace Database\Factories;

use App\Models\FormFieldDefinition;
use App\Models\FormTypeTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FormFieldDefinitionFactory extends Factory
{
    protected $model = FormFieldDefinition::class;

    public function definition(): array
    {
        $fieldTypes = array_keys(FormFieldDefinition::FIELD_TYPES);
        $fieldType = $this->faker->randomElement($fieldTypes);
        
        return [
            'id' => (string) Str::uuid(),
            'form_type_template_id' => FormTypeTemplate::factory(),
            'field_key' => Str::snake($this->faker->unique()->words(2, true)),
            'field_type' => $fieldType,
            'label' => $this->faker->words(3, true),
            'placeholder' => $this->faker->optional()->sentence(3),
            'description' => $this->faker->optional()->sentence(),
            'validation_rules' => ['required' => true],
            'options' => $this->getOptionsForType($fieldType),
            'display_config' => [],
            'field_config' => [],
            'sort_order' => $this->faker->numberBetween(1, 100),
            'is_system' => false,
        ];
    }

    /**
     * Generate options for field types that need them
     */
    protected function getOptionsForType(string $type): ?array
    {
        $typesWithOptions = ['select', 'radio', 'checkboxes', 'likert', 'multiple_choice_grid'];
        
        if (!in_array($type, $typesWithOptions)) {
            return null;
        }

        return collect(range(1, $this->faker->numberBetween(2, 5)))
            ->map(fn ($i) => [
                'value' => "option_{$i}",
                'label' => "Option {$i}",
            ])
            ->toArray();
    }

    /**
     * Text field type
     */
    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'field_type' => 'text',
            'options' => null,
        ]);
    }

    /**
     * Email field type
     */
    public function email(): static
    {
        return $this->state(fn (array $attributes) => [
            'field_type' => 'email',
            'options' => null,
        ]);
    }

    /**
     * Select field type with options
     */
    public function select(): static
    {
        return $this->state(fn (array $attributes) => [
            'field_type' => 'select',
            'options' => [
                ['value' => 'opt1', 'label' => 'Option 1'],
                ['value' => 'opt2', 'label' => 'Option 2'],
                ['value' => 'opt3', 'label' => 'Option 3'],
            ],
        ]);
    }

    /**
     * Radio field type with options
     */
    public function radio(): static
    {
        return $this->state(fn (array $attributes) => [
            'field_type' => 'radio',
            'options' => [
                ['value' => 'yes', 'label' => 'Yes'],
                ['value' => 'no', 'label' => 'No'],
            ],
        ]);
    }

    /**
     * Required field
     */
    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'validation_rules' => ['required' => true],
        ]);
    }

    /**
     * Optional field
     */
    public function optional(): static
    {
        return $this->state(fn (array $attributes) => [
            'validation_rules' => ['required' => false],
        ]);
    }

    /**
     * System field
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => true,
        ]);
    }

    /**
     * Attach to specific template
     */
    public function forTemplate(FormTypeTemplate $template): static
    {
        return $this->state(fn (array $attributes) => [
            'form_type_template_id' => $template->id,
        ]);
    }
}
