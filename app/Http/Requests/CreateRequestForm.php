<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'date_of_use' => 'required|string',
            'time_of_use' => 'required|string',
            'labs_to_use' => 'nullable|array',
            'equipments_to_use' => 'nullable|array',
            'consumables_to_use' => 'nullable|array',
        ];
    }
}
