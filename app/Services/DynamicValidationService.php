<?php

namespace App\Services;

use App\Models\EventSubform;
use App\Models\FormFieldDefinition;

class DynamicValidationService
{
    /**
     * Build Laravel validation rules from a field schema array
     * Returns rules keyed by field_key directly (without response_data. prefix)
     */
    public function buildRulesFromSchema(array $fieldSchema): array
    {
        $rules = [];

        foreach ($fieldSchema as $field) {
            $fieldKey = $field['field_key'] ?? null;
            if (!$fieldKey) {
                continue;
            }

            $rules[$fieldKey] = $this->buildFieldRules($field);
        }

        return $rules;
    }

    /**
     * Build validation rules for a single field
     */
    public function buildFieldRules(array $field): array
    {
        $rules = [];
        $validationConfig = $field['validation_rules'] ?? [];
        $fieldType = $field['field_type'] ?? 'text';
        $fieldConfig = $field['field_config'] ?? [];

        // Required/nullable
        if (!empty($validationConfig['required'])) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        // Type-based rules
        switch ($fieldType) {
            case 'email':
                $rules[] = 'email';
                break;

            case 'number':
                $rules[] = 'numeric';
                break;

            case 'phone':
                $rules[] = 'string';
                // Optional phone pattern validation
                if (!empty($validationConfig['pattern'])) {
                    $rules[] = 'regex:' . $validationConfig['pattern'];
                }
                break;

            case 'date':
                $rules[] = 'date';
                if (!empty($validationConfig['date_format'])) {
                    $rules[] = 'date_format:' . $validationConfig['date_format'];
                }
                break;

            case 'time':
                $rules[] = 'date_format:H:i';
                break;

            case 'file':
                $rules[] = 'file';
                if (!empty($fieldConfig['max_size'])) {
                    $rules[] = 'max:' . $fieldConfig['max_size'];
                }
                if (!empty($fieldConfig['accept'])) {
                    $mimes = str_replace(['.', ' '], ['', ','], $fieldConfig['accept']);
                    $rules[] = 'mimes:' . $mimes;
                }
                break;

            case 'checkbox':
                $rules[] = 'boolean';
                break;

            case 'checkboxes':
                $rules[] = 'array';
                if (!empty($field['options'])) {
                    $values = collect($field['options'])->pluck('value')->implode(',');
                    // Each selected value must be in the options
                    $rules['.*'] = 'in:' . $values;
                }
                break;

            case 'select':
            case 'radio':
                if (!empty($field['options'])) {
                    $values = collect($field['options'])->pluck('value')->implode(',');
                    $rules[] = 'in:' . $values;
                } else {
                    $rules[] = 'string';
                }
                break;

            case 'likert':
            case 'linear_scale':
            case 'rating':
                $rules[] = 'integer';
                $min = $fieldConfig['min'] ?? 1;
                $max = $fieldConfig['max'] ?? 5;
                $rules[] = "between:{$min},{$max}";
                break;

            case 'multiple_choice_grid':
                $rules[] = 'array';
                break;

            case 'address':
                // Address field is handled as nested object or flat fields
                $rules[] = 'array';
                break;

            case 'textarea':
            case 'rich_text':
            case 'text':
            default:
                $rules[] = 'string';
                break;
        }

        // Generic constraints
        if (isset($validationConfig['min']) && !in_array($fieldType, ['likert', 'linear_scale', 'rating'])) {
            $rules[] = 'min:' . $validationConfig['min'];
        }
        if (isset($validationConfig['max']) && !in_array($fieldType, ['likert', 'linear_scale', 'rating'])) {
            $rules[] = 'max:' . $validationConfig['max'];
        }
        if (!empty($validationConfig['pattern']) && $fieldType !== 'phone') {
            $rules[] = 'regex:' . $validationConfig['pattern'];
        }
        if (!empty($validationConfig['accepted'])) {
            // Remove nullable/required, add accepted
            $rules = array_filter($rules, fn($r) => !in_array($r, ['nullable', 'required']));
            $rules[] = 'accepted';
        }

        return $rules;
    }

    /**
     * Build validation rules from an EventSubform model
     */
    public function buildRulesFromSubform(EventSubform $subform): array
    {
        $fieldSchema = $subform->resolved_field_schema;
        return $this->buildRulesFromSchema($fieldSchema);
    }

    /**
     * Build custom validation messages from field schema
     */
    public function buildMessagesFromSchema(array $fieldSchema): array
    {
        $messages = [];

        foreach ($fieldSchema as $field) {
            $fieldKey = $field['field_key'] ?? null;
            $label = $field['label'] ?? $fieldKey;
            $validationConfig = $field['validation_rules'] ?? [];

            if (!$fieldKey) {
                continue;
            }

            // Custom required message
            if (!empty($validationConfig['required'])) {
                $customMessage = $validationConfig['required_message'] ?? null;
                $messages[$fieldKey . '.required'] = $customMessage ?? "The {$label} field is required.";
            }

            // Custom format messages
            if (!empty($validationConfig['custom_message'])) {
                $messages[$fieldKey . '.*'] = $validationConfig['custom_message'];
            }
        }

        return $messages;
    }

    /**
     * Build attribute names from field schema for better error messages
     */
    public function buildAttributesFromSchema(array $fieldSchema): array
    {
        $attributes = [];

        foreach ($fieldSchema as $field) {
            $fieldKey = $field['field_key'] ?? null;
            $label = $field['label'] ?? null;

            if ($fieldKey && $label) {
                $attributes[$fieldKey] = $label;
            }
        }

        return $attributes;
    }

    /**
     * Get validation data for a subform (rules, messages, attributes)
     */
    public function getValidationData(EventSubform $subform): array
    {
        $schema = $subform->resolved_field_schema;

        return [
            'rules' => $this->buildRulesFromSchema($schema),
            'messages' => $this->buildMessagesFromSchema($schema),
            'attributes' => $this->buildAttributesFromSchema($schema),
        ];
    }

    /**
     * Get default values for a field schema (for form initialization)
     */
    public function getDefaultValues(array $fieldSchema): array
    {
        $defaults = [];

        foreach ($fieldSchema as $field) {
            $fieldKey = $field['field_key'] ?? null;
            $fieldType = $field['field_type'] ?? 'text';
            $fieldConfig = $field['field_config'] ?? [];

            if (!$fieldKey) {
                continue;
            }

            // Set type-appropriate defaults
            switch ($fieldType) {
                case 'checkbox':
                    $defaults[$fieldKey] = false;
                    break;
                case 'checkboxes':
                case 'multiple_choice_grid':
                    $defaults[$fieldKey] = [];
                    break;
                case 'number':
                case 'likert':
                case 'linear_scale':
                case 'rating':
                    $defaults[$fieldKey] = null;
                    break;
                case 'address':
                    $defaults[$fieldKey] = [
                        'region' => null,
                        'province' => null,
                        'city' => null,
                        'country' => 'Philippines',
                    ];
                    break;
                default:
                    $defaults[$fieldKey] = $fieldConfig['defaultValue'] ?? ($fieldConfig['default'] ?? null);
            }
        }

        return $defaults;
    }
}
