<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'item_id' => 'required|exists:items,id',
            'barcode' => 'required|string|unique:transactions,id,' . $this->id,
            'transac_type' => 'required|string|in:incoming,outgoing',
            'quantity' => 'required|numeric',
            'unit_price' => 'nullable|numeric',
            'unit' => 'required|string',
            'total_cost' => 'nullable|numeric',
            'user_id' => 'required|exists:users,id',
            'expiration' => 'date|nullable',
            'remarks' => 'string|nullable',
            'personnel_id' => 'required|exists:personnels,id'
        ];
    }
}
