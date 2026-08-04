<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGuestPersonnelEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'string', 'max:32', Rule::exists('personnels', 'employee_id')],
            'email' => ['required', 'email', 'max:255', Rule::unique('personnels', 'email')->ignore($this->input('employee_id'), 'employee_id')],
        ];
    }
}
