<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEventSubformRequest extends FormRequest
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
        $subform = $this->subform_type;
        $formFields = config("subforms.$subform", []);

        $rules = [
            'subform_type' => ['required', 'string', Rule::in(array_keys(config('subformtypes')))],
            'form_parent_id' => ['required', 'string', 'exists:event_requirements,event_id'],
            'participant_id' => [
                'required',
                'uuid',
                'exists:registrations,id',
                Rule::unique('event_subform_responses')
                    ->where(fn($q) => $q->where('form_parent_id', $this->form_parent_id)
                        ->where('subform_type', $subform)),
            ],
            'response_data' => ['required', 'array'],
        ];

        foreach ($formFields as $field => $type) {
            $rules["response_data.$field"] = $type;
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'form_parent_id.required' => 'The form parent ID is required.',
            'form_parent_id.exists' => 'The specified form parent does not exist.',
            'participant_id.required' => 'The participant is required.',
            'participant_id.exists' => 'The specified participant does not exist or is not registered to this event.',
            'response_data.required' => 'The response data is required.',
            'response_data.array' => 'The response data must be an array.',
            'unique' => 'You have already submitted a response.',
        ];
    }
}
