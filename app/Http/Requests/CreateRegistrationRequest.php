<?php

namespace App\Http\Requests;

use App\Models\Participant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class CreateRegistrationRequest extends FormRequest
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
            'name' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'sex' => ['nullable', 'string'],
            'age' => ['nullable', 'numeric'],
            'organization' => ['nullable', 'string'],
            'is_ip' => ['nullable', 'boolean'],
            'is_pwd' => ['nullable', 'boolean'],
            'city_address' => ['nullable', 'string'],
            'province_address' => ['nullable', 'string'],
            'country_address' => ['nullable', 'string'],
            'agreed_tc' => ['nullable', 'boolean'],
            'event_id' => ['required', 'string', 'exists:forms,event_id'],
        ];
    }
}
