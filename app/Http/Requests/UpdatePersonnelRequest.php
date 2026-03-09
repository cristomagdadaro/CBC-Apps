<?php

namespace App\Http\Requests;

use App\Rules\UniqueFullName;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonnelRequest extends FormRequest
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
            'fname' => ['required' , 'string', new UniqueFullName($this->id)],
            'mname' => ['string', 'nullable', new UniqueFullName($this->id)],
            'lname' =>  ['required', 'string', new UniqueFullName($this->id)],
            'suffix' => ['string', 'nullable', new UniqueFullName($this->id)],
            'position' => 'required|string',
            'phone' => 'string|nullable',
            'address' => 'string|nullable',
            'email' => 'required|email|unique:personnels,email,' . $this->id,
            'employee_id' => 'required|string|max:32|unique:personnels,employee_id,' . $this->id,
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
            'employee_id.required' => 'Required field',
            'employee_id.unique' => 'Already been taken',
        ];
    }
}
