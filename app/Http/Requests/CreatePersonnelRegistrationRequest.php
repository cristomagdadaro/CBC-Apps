<?php

namespace App\Http\Requests;

use App\Models\PersonnelRegistration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePersonnelRegistrationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $isPhilRiceEmployee = filter_var($this->input('is_philrice_employee'), FILTER_VALIDATE_BOOLEAN);

        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
            'employee_id' => filled($this->input('employee_id'))
                ? trim((string) $this->input('employee_id'))
                : null,
            'registration_type' => $isPhilRiceEmployee
                ? PersonnelRegistration::TYPE_PHILRICE_EMPLOYEE
                : strtolower(trim((string) $this->input('registration_type'))),
            'course_program' => filled($this->input('course_program'))
                ? trim((string) $this->input('course_program'))
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
            'registration_type' => [
                'required',
                Rule::in($this->boolean('is_philrice_employee')
                    ? [PersonnelRegistration::TYPE_PHILRICE_EMPLOYEE]
                    : PersonnelRegistration::idCardTypes()),
            ],
            'course_program' => [
                Rule::requiredIf(fn () => ! $this->boolean('is_philrice_employee')),
                'nullable',
                'string',
                'max:191',
            ],
            'id_photo' => [
                Rule::requiredIf(fn () => ! $this->boolean('is_philrice_employee')),
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:4096',
                'dimensions:ratio=1/1,min_width=200,min_height=200',
            ],
        ];
    }
}
