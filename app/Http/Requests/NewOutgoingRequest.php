<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NewOutgoingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_id' => 'required|exists:items,id',
            'barcode' => 'nullable|string',
            'transac_type' => [
                'required',
                'string',
                Rule::in(['outgoing']),
            ],
            'quantity' => [
                'required',
                'numeric',
                'max:-1',
            ],
            'unit' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
            'employee_id' => 'required|string|exists:users,employee_id',
            'expiration' => 'date|nullable',
            'remarks' => 'string|nullable',
        ];
    }
}

