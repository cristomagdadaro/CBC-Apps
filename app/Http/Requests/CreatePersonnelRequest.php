<?php

namespace App\Http\Requests;

use App\Rules\UniqueFullName;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreatePersonnelRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'is_philrice_employee' => ['required', 'boolean'],
            'fname' => ['required', 'string', new UniqueFullName()],
            'mname' => ['string', 'nullable', new UniqueFullName()],
            'lname' =>  ['required', 'string', new UniqueFullName()],
            'suffix' => ['string', 'nullable', new UniqueFullName()],
            'position' => 'required|string',
            'phone' => 'string|nullable',
            'address' => 'string|nullable',
            'email' => 'nullable|email|unique:personnels,email',
            'employee_id' => 'nullable|string|max:32|unique:personnels,employee_id|required_if:is_philrice_employee,true',
        ];
    }

    public function messages(): array
    {
        return [
            'fname.required' => 'Required field',
            'fname.unique' => 'Already been taken',
            'lname.required' => 'Required field',
            'lname.unique' => 'Already been taken',
            'position.required' => 'Required field',
            'email.email' => 'Invalid email format',
            'email.unique' => 'Already been taken',
            'employee_id.required_if' => 'Required field',
            'employee_id.unique' => 'Already been taken',
        ];
    }
}
