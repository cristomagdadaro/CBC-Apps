<?php

namespace App\Http\Requests;

use App\Models\PersonnelRegistration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePersonnelRegistrationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('inventory.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                PersonnelRegistration::STATUS_APPROVED,
                PersonnelRegistration::STATUS_REJECTED,
            ])],
            'rejection_remarks' => [
                Rule::requiredIf(fn () => $this->input('status') === PersonnelRegistration::STATUS_REJECTED),
                'nullable',
                'string',
                'max:1000',
            ],
            'bypass_email_verification' => ['nullable', 'boolean'],
        ];
    }
}
