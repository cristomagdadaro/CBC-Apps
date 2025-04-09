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
            'fname' => ['required', 'string', new UniqueFullName()],
            'mname' => 'string|nullable',
            'lname' =>  ['required', 'string', new UniqueFullName()],
            'suffix' => 'string|nullable',
            'position' => 'required|string',
            'phone' => 'string|nullable',
            'address' => 'string|nullable',
            'email' => 'email|nullable',
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
        ];
    }
}
