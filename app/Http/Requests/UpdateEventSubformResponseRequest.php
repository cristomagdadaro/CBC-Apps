<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\Subform;
use App\Models\EventSubform;
use App\Models\EventSubformResponse;
use App\Services\DynamicValidationService;

class UpdateEventSubformResponseRequest extends FormRequest
{
    /**
     * Cached event subform model
     */
    protected ?EventSubform $eventSubform = null;

    protected function prepareForValidation(): void
    {
        $this->applyFieldDefaults();
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the EventSubform model for the current request
     */
    protected function getEventSubform(): ?EventSubform
    {
        if ($this->eventSubform === null) {
            // Try to get from input first
            $formParentId = $this->input('form_parent_id');
            if ($formParentId) {
                $this->eventSubform = EventSubform::find($formParentId);
            }
            
            // Fall back to route model binding (subform_response -> form_parent_id)
            if ($this->eventSubform === null) {
                $response = $this->route('subform_response');
                if ($response instanceof EventSubformResponse) {
                    $this->eventSubform = $response->eventSubform;
                } elseif (is_string($response)) {
                    $responseModel = EventSubformResponse::find($response);
                    if ($responseModel) {
                        $this->eventSubform = $responseModel->eventSubform;
                    }
                }
            }
        }
        return $this->eventSubform;
    }

    /**
     * Check if this request uses dynamic field schema
     * Only returns true for actual dynamic schemas (custom templates or field_schema column)
     * NOT for legacy config-derived schemas
     */
    protected function usesDynamicSchema(): bool
    {
        $subform = $this->getEventSubform();
        if (!$subform) {
            return false;
        }
        
        if (!empty($subform->field_schema)) {
            return true;
        }
        
        if (!empty($subform->form_type_template_id)) {
            return true;
        }
        
        return false;
    }

    protected function applyFieldDefaults(): void
    {
        if (!$this->usesDynamicSchema()) {
            return;
        }

        $eventSubform = $this->getEventSubform();
        if (!$eventSubform) {
            return;
        }

        $responseData = $this->input('response_data', []);
        if (!is_array($responseData)) {
            $responseData = [];
        }

        foreach ($eventSubform->resolved_field_schema as $field) {
            $fieldKey = $field['field_key'] ?? null;
            $fieldType = $field['field_type'] ?? null;
            $fieldConfig = $field['field_config'] ?? [];

            if (!$fieldKey || in_array($fieldType, ['section_header', 'paragraph'], true)) {
                continue;
            }

            $changeable = $fieldConfig['changeable'] ?? true;
            $defaultExists = array_key_exists('defaultValue', $fieldConfig) || array_key_exists('default', $fieldConfig);
            if (!$defaultExists) {
                continue;
            }

            $defaultValue = $fieldConfig['defaultValue'] ?? $fieldConfig['default'];
            $hasSubmittedValue = array_key_exists($fieldKey, $responseData);
            $submittedValue = $responseData[$fieldKey] ?? null;

            if ($changeable === false) {
                $responseData[$fieldKey] = $defaultValue;
                continue;
            }

            if (!$hasSubmittedValue || $submittedValue === null || $submittedValue === '') {
                $responseData[$fieldKey] = $defaultValue;
            }
        }

        $this->merge([
            'response_data' => $responseData,
        ]);
    }

    /**
     * Make rules nullable for partial updates
     */
    protected function makeRulesNullable(array $rules): array
    {
        $nullableRules = [];
        
        foreach ($rules as $field => $fieldRules) {
            if (is_string($fieldRules)) {
                // Remove 'required' from the rule string
                $ruleString = str_replace('required|', '', $fieldRules);
                $ruleString = str_replace('|required', '', $ruleString);
                $ruleString = preg_replace('/^required$/', '', $ruleString);
                $ruleString = trim($ruleString, '|');
                
                $nullableRules[$field] = 'nullable' . ($ruleString ? '|' . $ruleString : '');
            } elseif (is_array($fieldRules)) {
                // Remove 'required' from the array and add 'nullable'
                $filtered = array_filter($fieldRules, fn($rule) => $rule !== 'required');
                array_unshift($filtered, 'nullable');
                $nullableRules[$field] = array_values($filtered);
            } else {
                $nullableRules[$field] = $fieldRules;
            }
        }
        
        return $nullableRules;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $subform = $this->input('subform_type') ?? optional($this->route('subform_response'))->subform_type;

        $rules = [
            'response_data' => ['required', 'array'],
            'skipped_field_keys' => ['nullable', 'array'],
            'skipped_field_keys.*' => ['string'],
        ];

        // Check for dynamic field schema first
        if ($this->usesDynamicSchema()) {
            $eventSubform = $this->getEventSubform();
            $dynamicService = app(DynamicValidationService::class);
            $dynamicRules = $dynamicService->buildRulesFromSchema($eventSubform->resolved_field_schema);
            $skippedFieldKeys = collect($this->input('skipped_field_keys', []))
                ->filter(fn ($value) => is_string($value) && trim($value) !== '')
                ->values()
                ->all();
            
            // Make rules nullable for partial updates
            $dynamicRules = $this->makeRulesNullable($dynamicRules);
            
            foreach ($dynamicRules as $field => $fieldRules) {
                if (in_array($field, $skippedFieldKeys, true)) {
                    continue;
                }
                $rules["response_data.$field"] = $fieldRules;
            }
        } else {
            // Fall back to legacy config-based validation
            $formFields = config("subformtypes.$subform", []);
            if (!is_array($formFields)) {
                $formFields = [];
            }

            // Make rules nullable for partial updates
            $formFields = $this->makeRulesNullable($formFields);
            
            foreach ($formFields as $field => $type) {
                $rules["response_data.$field"] = $type;
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'response_data.required' => 'Response data is required.',
            'response_data.array' => 'Response data must be a structured array.',
        ];

        // Add dynamic messages if using dynamic schema
        if ($this->usesDynamicSchema()) {
            $eventSubform = $this->getEventSubform();
            $dynamicService = app(DynamicValidationService::class);
            $dynamicMessages = $dynamicService->buildMessagesFromSchema($eventSubform->resolved_field_schema);
            
            foreach ($dynamicMessages as $key => $message) {
                $messages["response_data.$key"] = $message;
            }
            
            return $messages;
        }

        // Legacy messages for feedback fields
        $subform = $this->input('subform_type') ?? optional($this->route('subform_response'))->subform_type;
        $feedbackFields = config('subformtypes.' . $subform, []);
        
        $friendly = [
            'clarity_objective' => 'Please rate the clarity of the objective.',
            'time_allotment' => 'Please rate the time allotment balance.',
            'attainment_objective' => 'Please rate attainment of objectives.',
            'relevance_usefulness' => 'Please rate relevance and usefulness to your role.',
            'overall_quality_content' => 'Please rate overall quality of content.',
            'overall_quality_resource_persons' => 'Please rate quality of resource persons.',
            'time_management_organization' => 'Please rate time management and organization.',
            'support_staff' => 'Please rate the support staff.',
            'overall_quality_activity_admin' => 'Please rate overall activity administration quality.',
            'knowledge_gain' => 'Please rate your knowledge gain (1–5).',
            'comments_event_coordination' => 'Enter comments or suggestions about event coordination.',
            'other_topics' => 'List other topics you wish to be included.',
            'agreed_tc' => 'You must agree to the terms and conditions to submit feedback.',
            'agreed_updates' => 'You must indicate your consent regarding updates.',
        ];

        foreach ($feedbackFields as $field => $ruleString) {
            $readable = ucwords(str_replace('_', ' ', $field));
            $rules = explode('|', $ruleString);
            foreach ($rules as $rule) {
                $baseRule = $rule;
                $args = null;
                if (str_contains($rule, ':')) {
                    [$baseRule, $args] = explode(':', $rule, 2);
                }
                $key = "response_data.$field.$baseRule";
                if (isset($messages[$key])) {
                    continue;
                }
                switch ($baseRule) {
                    case 'required':
                        $messages[$key] = $friendly[$field] ?? "Please provide $readable.";
                        break;
                    case 'integer':
                        $messages[$key] = "$readable must be a number.";
                        break;
                    case 'string':
                        $messages[$key] = "$readable must be text.";
                        break;
                    case 'in':
                        $messages[$key] = "$readable must be one of: " . ($args ? str_replace(',', ', ', $args) : 'the allowed values') . ".";
                        break;
                    case 'accepted':
                        $messages[$key] = $friendly[$field];
                        break;
                    default:
                        $messages[$key] = "$readable is invalid.";
                        break;
                }
            }
        }

        return $messages;
    }
}
