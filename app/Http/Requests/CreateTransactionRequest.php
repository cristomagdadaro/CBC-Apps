<?php

namespace App\Http\Requests;

use App\Enums\Inventory;
use App\Models\Transaction;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CreateTransactionRequest extends FormRequest
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
        $quantity = $this->input('quantity');
        $transacType = $this->input('transac_type');

        if (
            $transacType === Inventory::OUTGOING->value &&
            is_numeric($quantity) &&
            $quantity > 0
        ) {

            $this->merge([
                'quantity' => $quantity * -1,
            ]);
        }
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
            'barcode' => [
                'required', 'string',
                Rule::when(
                    fn ($input) => $input->transac_type === Inventory::INCOMING->value,
                    ['unique:transactions,barcode']
                ),
                Rule::when(
                    fn ($input) => $input->transac_type === Inventory::OUTGOING->value,
                    ['exists:transactions,barcode']
                ),
            ],
            'transac_type' => [
                'required',
                'string',
                Rule::in([Inventory::INCOMING->value, Inventory::OUTGOING->value]),
            ],
            'quantity' => [
                'required',
                'numeric',
                Rule::when(
                    fn ($input) => $input->transac_type === Inventory::INCOMING->value,
                    ['min:1']
                ),
                Rule::when(
                    fn ($input) => $input->transac_type === Inventory::OUTGOING->value,
                    ['max:-1']
                ),
            ],
            'unit_price' => 'nullable|numeric|min:0',
            'unit' => 'required|string',
            'total_cost' => 'nullable|numeric',
            'user_id' => 'required|exists:users,id',
            'expiration' => 'date|nullable',
            'remarks' => 'string|nullable',
            'personnel_id' => 'required|exists:personnels,id',
        ];
    }
}
