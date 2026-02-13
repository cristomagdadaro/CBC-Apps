<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Repositories\OptionRepo;

class CreateRequestFormPivot extends FormRequest
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
        $requestTypes = app(OptionRepo::class)->getRequestTypes()->pluck('label')->toArray();
        
        return [
            'name' => 'required|string',
            'affiliation' => 'required|string',
            'email' => 'required|string|email',
            'position' => 'nullable|string',
            'phone' => 'required|string',

            'request_type' => 'required|array|min:1',
            'request_type.*' => 'string|in:'.implode(',', $requestTypes),
            'request_details' => 'nullable|string',
            'request_purpose' => 'required|string',
            'project_title' => 'nullable|string',
            'date_of_use' => 'required|date',
            'time_of_use' => 'required|date_format:H:i:s',
            'date_of_use_end' => [
                Rule::requiredIf(fn () => $this->requiresEndTime()),
                'nullable',
                'date',
                'after_or_equal:date_of_use',
            ],
            'time_of_use_end' => [
                Rule::requiredIf(fn () => $this->requiresEndTime()),
                'nullable',
                'date_format:H:i:s',
            ],
            'labs_to_use' => 'nullable|array',
            'equipments_to_use' => 'nullable|array',
            'consumables_to_use' => 'nullable|array',

            'requester_id' => 'sometimes|exists:requesters,id',
            'form_id' => 'sometimes|exists:use_request_forms,id',
            'request_status' => 'nullable|in:pending,approved,rejected',
            'agreed_clause_1' => 'accepted',
            'agreed_clause_2'  => 'accepted',
            'agreed_clause_3' => 'accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'agreed_clause_1' => 'Must be accepted!',
            'agreed_clause_2'  => 'Must be accepted!',
            'agreed_clause_3'  => 'Must be accepted!',
        ];
    }

    private function requiresEndTime(): bool
    {
        $types = $this->input('request_type', []);

        if (!is_array($types)) {
            $types = [$types];
        }

        return in_array('Equipments', $types, true) || in_array('Laboratory Access', $types, true);
    }
}
