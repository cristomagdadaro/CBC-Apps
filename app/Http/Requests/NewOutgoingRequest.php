<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class NewOutgoingRequest extends FormRequest
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
       $maxQuantity = Transaction::selectRaw('SUM(transactions.quantity) as remaining_quantity')
         ->join('items', 'transactions.item_id', '=', 'items.id')
            ->groupBy('items.id', 'items.name', 'items.brand', 'transactions.unit')
            ->where('items.id', $this->item_id)
            ->where('items.name', $this->name)
            ->where('items.brand', $this->brand)
            ->where('transactions.unit', $this->unit)
            ->get('remaining_quantity')->first()->remaining_quantity;

        return [
            'item_id' => 'required|exists:items,id',
            'barcode' => 'nullable|string',
            'transac_type' => 'required|string|in:out',
            'quantity' => 'required|numeric|min:1|max:'.$maxQuantity."|min:1",
            'unit_price' => 'nullable|numeric|min:0',
            'unit' => 'required|string',
            'total_cost' => 'nullable|numeric',
            'personnel_id' => 'required|exists:personnels,id',
            'user_id' => 'required|exists:users,id',
            'expiration' => 'date|nullable',
            'remarks' => 'string|nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'item_id.required' => 'Required field',
            'item_id.exists' => 'Item does not exist',
            'transac_type.required' => 'Required field',
            'quantity.required' => 'Required field',
            'quantity.numeric' => 'Invalid quantity',
            'quantity.min' => 'Quantity must be at least 1',
            'unit.required' => 'Required field',
            'personnel_id.required' => 'Required field',
            'personnel_id.exists' => 'Personnel does not exist',
            'user_id.required' => 'Required field',
            'user_id.exists' => 'User does not exist',
            'expiration.date' => 'Invalid date',
        ];
    }
}
