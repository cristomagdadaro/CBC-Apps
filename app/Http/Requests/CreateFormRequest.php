<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\InteractsWithFormStyles;
use App\Models\Form;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class CreateFormRequest extends FormRequest
{
    use InteractsWithFormStyles;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        do {
            $event = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (Form::where('event_id', $event)->exists());

        $this->merge([
            'event_id' => $event,
            'style_tokens' => $this->normalizeStyleTokens($this->input('style_tokens')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'event_id' => ['required', 'string', 'min:4', 'max:4'],
            'title' => ['required', 'string', 'min:10', 'max:256', 'unique:forms,title'],
            'description' => ['nullable', 'string', 'max:512'],
            'details' => ['nullable', 'string'],
            'date_from' => ['required', 'date_format:Y-m-d', 'before_or_equal:date_to'],
            'date_to' => ['required', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'time_from' => ['required', 'date_format:H:i:s', 'before_or_equal:time_to'],
            'time_to' => ['required', 'date_format:H:i:s', 'after_or_equal:time_from'],
            'venue' => ['nullable', 'string'],
            'is_suspended' => 'nullable|boolean',
            'requirements' => 'nullable|array',
        ];

        return array_merge($rules, $this->styleTokenRules());
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
