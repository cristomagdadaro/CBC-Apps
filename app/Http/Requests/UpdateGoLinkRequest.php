<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGoLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer'],
            'slug' => ['nullable', 'string', 'max:191', 'regex:/^[A-Za-z0-9_-]+$/', 'unique:corpowp.wp_brm_redirects,slug,' . $this->id],
            'target_url' => ['required', 'url', 'max:65535'],
            'expires' => ['nullable', 'date'],
            'status' => ['nullable', 'boolean'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string'],
            'og_image' => ['nullable', 'url', 'max:65535'],
            'is_public' => ['nullable', 'boolean'],
        ];
    }
}
