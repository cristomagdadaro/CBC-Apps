<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateEventSubformRequest extends FormRequest
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
        return [
            'form_parent_id' => ['required', 'string', 'exists:event_requirements,id'],
            'participant_id' => ['required', 'string', 'exists:participants,id'],
            'response_data' => ['required', 'array'],
        ];
    }
}
