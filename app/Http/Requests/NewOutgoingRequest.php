<?php

namespace App\Http\Requests;

use App\Enums\Inventory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NewOutgoingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $quantity = $this->input('quantity');
        $transacType = $this->input('transac_type');

        if (auth()->check()) {
            $this->merge([
                'user_id' => auth()->user()->id,
            ]);
        }

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
                Rule::in(['outgoing']),
            ],
            'quantity' => [
                'required',
                'numeric',
                'max:-1',
            ],
            'unit' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
            'employee_id' => 'required|string|exists:personnels,employee_id',
            'expiration' => 'date|nullable',
            'remarks' => 'string|nullable',
        ];
    }
}

