<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateLabRequest extends FormRequest
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
            //requester
            'name' => ['required', 'string', 'max:255'],
            'affiliation' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],

            //lab request form
            'request_type' => ['required', 'string', 'max:255'],
            'request_details' => ['required', 'string', 'max:255'],
            'request_purpose' => ['required', 'string', 'max:255'],
            'project_title' => ['required', 'string', 'max:255'],
            'date_of_use' => ['required', 'date'],
            'time_of_use' => ['required', 'string', 'max:255'],
            'labs_to_use' => ['required', 'string', 'max:255'],
            'equipments_to_use' => ['required', 'string', 'max:255'],
            'consumables_to_use' => ['required', 'string', 'max:255'],

            //lab request
            'request_status' => ['required', 'string', 'max:255'],
            'agreed_clause_1' => ['required', 'boolean'],
            'agreed_clause_2' => ['required', 'boolean'],
            'agreed_clause_3' => ['required', 'boolean'],
            'disapproved_remarks' => ['nullable', 'string', 'max:255'],
            'approved_by' => ['nullable', 'string', 'max:255'],

            //requester_id and form_id will be generated automatically
            // 'requester_id' => ['required', 'string', 'max:255'],
            // 'request_id' => ['required', 'string', 'max:255'],
            // 'form_id' => ['required', 'string', 'max:255'],
        ];
    }
}
