<?php

namespace App\Http\Requests;

use App\Models\Form;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class CreateFormRequest extends FormRequest
{
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
            $temp = Str::uuid()->toString();
        } while (Form::where('id', $temp)->exists());

        do {
            $event = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (Form::where('event_id', $event)->exists());

        $this->merge([
            'id' => $temp,
            'event_id' => $event,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['required'],
            'event_id' => ['required', 'string', 'min:4', 'max:4'],
            'title' => ['required', 'string', 'min:10', 'max:510', 'unique:forms,title'],
            'description' => ['nullable', 'string'],
            'details' => ['nullable', 'string'],
            'date_from' => ['required', 'date', 'before_or_equal:date_to'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'time_from' => ['required', 'date_format:H:i', 'before_or_equal:time_to'],
            'time_to' => ['required', 'date_format:H:i', 'after_or_equal:time_from'],
            'venue' => ['nullable', 'string'],
            'has_pretest' => 'nullable|boolean',
            'has_posttest' => 'nullable|boolean',
            'has_preregistration' => 'nullable|boolean',
            'is_suspended' => 'nullable|boolean',
        ];
    }
}
