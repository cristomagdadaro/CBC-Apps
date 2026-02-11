<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOptionRequest extends FormRequest
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
            'key' => ['required', 'string', Rule::unique('options', 'key')->ignore($this->id)],
            'value' => ['nullable', 'string'],
            'label' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'in:text,select,checkbox,textarea,json,boolean,number'],
            'group' => ['nullable', 'string'],
            'options' => ['nullable', 'json'],
        ];
    }

    public function messages(): array
    {
        return [
            'key.required' => 'The option key is required.',
            'key.unique' => 'This option key already exists.',
            'label.required' => 'The option label is required.',
            'type.required' => 'The option type is required.',
            'type.in' => 'The option type must be one of: text, select, checkbox, textarea, json, boolean, number.',
        ];
    }
}
