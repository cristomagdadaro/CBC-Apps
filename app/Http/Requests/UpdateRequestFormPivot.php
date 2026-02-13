<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequestFormPivot extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {        return $this->user()?->can('fes.request.approve') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'requester_id' => 'required|exists:requesters,id',
            'form_id' => 'required|exists:use_request_forms,id',
            'request_status' => 'required|in:pending,approved,rejected',
            'agreed_clause_1' => 'required|boolean',
            'agreed_clause_2'  => 'required|boolean',
            'agreed_clause_3' => 'required|boolean',
            'disapproved_remarks' => 'nullable|string',
            'approved_by' => 'nullable|string',
            'approval_constraint' => 'nullable|string',
        ];
    }
}
