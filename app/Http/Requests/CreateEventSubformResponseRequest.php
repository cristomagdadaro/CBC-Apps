<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\Subform;
use App\Models\EventSubform;
use App\Services\DynamicValidationService;

class CreateEventSubformResponseRequest extends FormRequest
{
    /**
     * Cached event subform model
     */
    protected ?EventSubform $eventSubform = null;

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
            $formParentId = $this->input('form_parent_id');
            if ($formParentId) {
                $this->eventSubform = EventSubform::find($formParentId);
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $subform = $this->input('subform_type');

        $participantExempt = [
            Subform::PREREGISTRATION->value,
            Subform::PREREGISTRATION_BIOTECH->value,
            Subform::PREREGISTRATION_QUIZBEE->value,
        ];
        
        $rules = [
            'subform_type' => ['required', 'string'],
            'form_parent_id' => ['required', 'string', 'exists:event_subforms,id'],
            'participant_id' => [
                'required_unless:subform_type,'.implode(',', $participantExempt),
                'nullable',
                'uuid',
                'exists:registrations,id',
            ],
            'response_data' => ['required', 'array'],
        ];

        // Check for dynamic field schema first
        if ($this->usesDynamicSchema()) {
            $eventSubform = $this->getEventSubform();
            $dynamicService = app(DynamicValidationService::class);
            $dynamicRules = $dynamicService->buildRulesFromSchema($eventSubform->resolved_field_schema);
            
            foreach ($dynamicRules as $field => $fieldRules) {
                $rules["response_data.$field"] = $fieldRules;
            }
        } else {
            // Fall back to legacy config-based validation
            $formFields = config("subformtypes.$subform", []);
            if (!is_array($formFields)) {
                $formFields = [];
            }

            foreach ($formFields as $field => $type) {
                $rules["response_data.$field"] = $type;
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'form_parent_id.required' => 'Missing event reference.',
            'form_parent_id.exists' => 'Event reference is invalid.',
            'participant_id.required' => 'A participant must be specified.',
            'participant_id.exists' => 'Participant record not found for this event.',
            'participant_id.unique' => 'This participant already submitted feedback for this event.',
            'response_data.required' => 'Feedback content is required.',
            'response_data.array' => 'Feedback payload must be a structured array.',
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
        $feedbackFields = config('subformtypes.' . Subform::FEEDBACK->value, []);
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
