<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequirementsRequest extends FormRequest
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
            'requirements' => ['array'],
            'requirements.*.form_type' => ['required', 'string'],
            'requirements.*.form_type_template_id' => ['nullable', 'uuid', 'exists:form_type_templates,id'],
            'requirements.*.step_type' => ['nullable', 'string'],
            'requirements.*.step_order' => ['nullable', 'integer', 'min:1'],
            'requirements.*.is_enabled' => ['boolean'],
            'requirements.*.open_from' => ['nullable', 'date'],
            'requirements.*.open_to' => ['nullable', 'date', 'after_or_equal:requirements.*.open_from'],
            'requirements.*.is_required' => ['boolean'],
            'requirements.*.max_slots' => ['nullable', 'integer', 'min:0'],
            'requirements.*.config' => ['nullable', 'array'],
            'requirements.*.config.limits' => ['nullable', 'array'],
            'requirements.*.config.limits.*.field' => ['required_with:requirements.*.config.limits.*.max', 'string'],
            'requirements.*.config.limits.*.max' => ['required_with:requirements.*.config.limits.*.field', 'integer', 'min:1'],
            'requirements.*.field_schema' => ['nullable', 'array'],
            'requirements.*.field_schema.*.field_key' => ['required_with:requirements.*.field_schema', 'string'],
            'requirements.*.field_schema.*.field_type' => ['required_with:requirements.*.field_schema', 'string'],
            'requirements.*.field_schema.*.label' => ['required_with:requirements.*.field_schema', 'string'],
            'requirements.*.field_schema.*.validation_rules' => ['nullable', 'array'],
            'requirements.*.field_schema.*.options' => ['nullable', 'array'],
            'requirements.*.field_schema.*.display_config' => ['nullable', 'array'],
            'requirements.*.field_schema.*.field_config' => ['nullable', 'array'],
            'requirements.*.visibility_rules' => ['nullable', 'array'],
            'requirements.*.completion_rules' => ['nullable', 'array'],
        ];
    }
}
