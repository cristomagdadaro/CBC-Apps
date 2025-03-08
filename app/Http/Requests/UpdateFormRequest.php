<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormRequest extends FormRequest
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
            'event_id' => ['required', 'string', 'min:4', 'max:4', 'exists:forms,event_id'],
            'title' => ['required', 'string', 'min:10', 'max:510'],
            'description' => ['nullable', 'string'],
            'details' => ['nullable', 'string'],
            'date_from' => ['required', 'date_format:Y-m-d', 'before_or_equal:date_to'],
            'date_to' => ['required', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'time_from' => ['required', 'date_format:H:i:s', 'before_or_equal:time_to'],
            'time_to' => ['required', 'date_format:H:i:s', 'after_or_equal:time_from'],
            'venue' => ['nullable', 'string'],
            'has_pretest' => 'nullable|boolean',
            'has_posttest' => 'nullable|boolean',
            'has_preregistration' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'date_from.before_or_equal' => 'Start date must be before or equal to end date',
            'date_to.after_or_equal' => 'End date must be after or equal to start date',
            'time_from.before_or_equal' => 'Start time must be before or equal to start time',
            'time_to.after_or_equal' => 'End time must be after or equal to start time',
        ];
    }
}
