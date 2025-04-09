<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateTransactionRequest extends FormRequest
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
            'barcode' => 'required|string|unique:transactions,barcode',
            'transac_type' => 'required|string|in:in,out',
            'quantity' => 'required|numeric|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'unit' => 'required|string',
            'total_cost' => 'nullable|numeric',
            'project_code' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'expiration' => 'date|nullable',
            'remarks' => 'string|nullable',
        ];
    }
}
