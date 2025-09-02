<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'name' => 'required|string',
            'affiliation' => 'required|string',
            'email' => 'required|string|email',
            'position' => 'nullable|string',
            'phone' => 'required|string',

            'request_type' => 'nullable|string',
            'request_details' => 'nullable|string',
            'request_purpose' => 'required|string',
            'project_title' => 'nullable|string',
            'date_of_use' => 'required|string',
            'time_of_use' => 'required|string',
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
}
