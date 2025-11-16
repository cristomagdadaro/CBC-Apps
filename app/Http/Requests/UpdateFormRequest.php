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
            'time_from' => ['required', 'date_format:H:i:s'],
            'time_to' => ['required', 'date_format:H:i:s'],
            'venue' => ['nullable', 'string'],
            'is_suspended' => 'nullable|boolean',
            'max_slots' => 'nullable|integer',
            'requirements' => 'nullable|array|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'date_from.before_or_equal' => 'Must be before or equal to end date',
            'date_to.after_or_equal' => 'Must be after or equal to start date',
            'time_from.before_or_equal' => 'Must be before or equal to start time',
            'time_to.after_or_equal' => 'Must be after or equal to start time',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $dateTimeFrom = strtotime($this->input('date_from') . ' ' . $this->input('time_from'));
            $dateTimeTo = strtotime($this->input('date_to') . ' ' . $this->input('time_to'));

            if ($dateTimeFrom > $dateTimeTo) {
                $validator->errors()->add('time_to', 'Must be later than or equal to the start time.');
            }
        });
    }

}
