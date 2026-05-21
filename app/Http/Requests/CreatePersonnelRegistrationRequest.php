<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePersonnelRegistrationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
            'employee_id' => filled($this->input('employee_id'))
                ? trim((string) $this->input('employee_id'))
                : null,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_philrice_employee' => ['required', 'boolean'],
            'fname' => ['required', 'string', 'max:191'],
            'mname' => ['nullable', 'string', 'max:191'],
            'lname' => ['required', 'string', 'max:191'],
            'suffix' => ['nullable', 'string', 'max:191'],
            'position' => ['required', 'string', 'max:191'],
            'phone' => ['nullable', 'string', 'max:64'],
            'address' => ['nullable', 'string', 'max:500'],
            'email' => ['required', 'email', 'max:191'],
            'employee_id' => [
                Rule::requiredIf(fn () => $this->boolean('is_philrice_employee')),
                'nullable',
                'string',
                'max:64',
            ],
        ];
    }
}
