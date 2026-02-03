<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\Subform;

class UpdateEventSubformResponseRequest extends FormRequest
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
        $subform = $this->input('subform_type') ?? optional($this->route('subform_response'))->subform_type;
        
        $formFields = config("subformtypes.$subform", []);
        if (!is_array($formFields)) {
            $formFields = [];
        }

        $rules = [
            'response_data' => ['required', 'array'],
        ];

        // For update, only validate the fields being updated
        // Make individual fields optional since it's a partial update
        foreach ($formFields as $field => $type) {
            // Start with nullable since it's an update
            $fieldRules = ['nullable'];
            
            // Remove 'required' from the rule string
            $ruleString = str_replace('required|', '', $type);
            $ruleString = str_replace('|required', '', $ruleString);
            $ruleString = trim($ruleString, '|');
            
            // Split remaining rules and add them to the array
            if (!empty($ruleString)) {
                $individualRules = explode('|', $ruleString);
                $fieldRules = array_merge($fieldRules, $individualRules);
            }
            
            $rules["response_data.$field"] = $fieldRules;
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'response_data.required' => 'Response data is required.',
            'response_data.array' => 'Response data must be a structured array.',
        ];

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
