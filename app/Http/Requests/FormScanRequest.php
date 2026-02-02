<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FormScanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()?->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payload' => ['required', 'string', 'min:5'],
            'scan_type' => ['required', 'string', 'in:checkin,certificate,meal,quiz,workshop'],
            'terminal_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}
