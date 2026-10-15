<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetFormsRequest extends FormRequest
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
        return array_merge([
            'event_id' => 'sometimes|nullable|string|exists:forms,event_id',
        ], config('searching', []));
    }

    protected function prepareForValidation(): void
    {
        if ($this->route('event_id')) {
            $this->merge([
                'event_id' => $this->route('event_id'),
            ]);
        }
    }
}
