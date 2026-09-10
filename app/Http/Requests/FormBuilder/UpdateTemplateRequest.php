<?php

namespace App\Http\Requests\FormBuilder;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:50',
            'form_config' => 'nullable|array',
            'fields' => 'sometimes|array|min:1',
            'fields.*.id' => 'nullable|uuid',
            'fields.*.field_key' => 'required|string|max:100|distinct',
            'fields.*.field_type' => 'required|string|in:' . implode(',', array_keys(\App\Models\FormFieldDefinition::FIELD_TYPES)),
            'fields.*.label' => 'required|string|max:1024',
            'fields.*.placeholder' => 'nullable|string|max:255',
            'fields.*.description' => 'nullable|string|max:500',
            'fields.*.validation_rules' => 'nullable|array',
            'fields.*.options' => 'nullable|array',
            'fields.*.display_config' => 'nullable|array',
            'fields.*.field_config' => 'nullable|array',
        ];
    }
}
