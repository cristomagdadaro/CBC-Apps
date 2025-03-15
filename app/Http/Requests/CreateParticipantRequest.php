<?php

namespace App\Http\Requests;

use App\Enums\Sex;
use App\Models\Participant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CreateParticipantRequest extends FormRequest
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
        } while (Participant::where('id', $temp)->exists());

        $this->merge([
            'id' => $temp,
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
            'id' => ['required', 'string'],
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string'],
            'sex' => ['nullable', 'string', $this->sexRule()],
            'age' => ['nullable', 'numeric'],
            'organization' => ['required', 'string'],
            'is_ip' => ['nullable', 'boolean'],
            'is_pwd' => ['nullable', 'boolean'],
            'city_address' => ['nullable', 'string'],
            'province_address' => ['nullable', 'string'],
            'country_address' => ['nullable', 'string'],
            'agreed_tc' => ['required', 'accepted'],
            'event_id' => ['required', 'string', 'exists:forms,event_id'],
        ];
    }

    public function messages(): array
    {
        return [
            'agreed_tc.accepted' => 'Must agree to the terms and conditions to proceed.',
        ];
    }

    private function sexRule()
    {
        return Rule::in([Sex::Male->value, Sex::Female->value, Sex::PreferNotToSay->value]);
    }
}
