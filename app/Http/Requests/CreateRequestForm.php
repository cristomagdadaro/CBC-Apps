<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRequestForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'request_type' => 'nullable|array',
            'request_type.*' => 'string|in:Supplies,Equipments,Laboratory Access',
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
