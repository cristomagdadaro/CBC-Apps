<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DeleteOptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        //return $this->user()?->can('event.forms.manage') ?? false;
        return false; // Disallow deletion of options for now, as it can cause issues. This can be re-enabled in the future with proper safeguards.
    }

    protected function failedAuthorization(): void
    {
        throw new AuthorizationException('Option deletion is disabled.');
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id') ?? $this->query('id') ?? $this->input('id'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|exists:options,id',
        ];
    }
}
