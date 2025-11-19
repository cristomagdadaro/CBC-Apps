<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:suppliers,name,'. $this->id],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:255', 'unique:suppliers,phone,'. $this->id],
            'email' => ['required', 'email', 'max:255', 'unique:suppliers,email,'. $this->id],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Required field',
            'name.unique' => 'Already been taken',
            'phone.required' => 'Required field',
            'phone.unique' => 'Already been taken',
            'email.required' => 'Required field',
            'email.email' => 'Invalid email format',
            'email.unique' => 'Already been taken',
        ];
    }
}
