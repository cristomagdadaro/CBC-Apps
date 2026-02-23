<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\Inventory;
use Illuminate\Validation\Rule;
use App\Models\Transaction;
use Illuminate\Contracts\Validation\Validator;

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
            'barcode' => [
                'required',
                'string',
                Rule::when(
                    fn ($input) => $input->transac_type === Inventory::INCOMING->value,
                    ['unique:transactions,barcode,' . $this->id]
                ),
                Rule::when(
                    fn ($input) => $input->transac_type === Inventory::OUTGOING->value,
                    ['exists:transactions,barcode']
                ),
            ],
            'barcode_prri' => 'nullable|string|unique:transactions,barcode_prri,' . $this->id,
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
                    ['min:1'] // outgoing also positive
                ),
            ],
            'unit_price' => 'nullable|numeric|min:0',
            'unit' => 'required|string',
            'total_cost' => 'nullable|numeric',
            'user_id' => 'required|exists:users,id',
            'expiration' => 'date|nullable',
            'remarks' => 'string|nullable',
            'project_code' => 'nullable|string',
            'personnel_id' => 'nullable|exists:personnels,id',
            'par_no' => 'nullable|string|unique:transactions,par_no,' . $this->id,
            'condition' => 'nullable|string',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function($validator) {
            if ($this->input('transac_type') !== Inventory::OUTGOING->value) {
                return;
            }

            $itemId = $this->input('item_id');
            $barcode = $this->input('barcode');
            $unit = $this->input('unit');
            $requestedQty = $this->input('quantity');

            if (!($itemId && $barcode && $unit && is_numeric($requestedQty))) {
                return;
            }

            $incoming = Transaction::query()
                ->where('item_id', $itemId)
                ->where('barcode', $barcode)
                ->where('unit', $unit)
                ->where('transac_type', Inventory::INCOMING->value)
                ->sum('quantity');

            $outgoing = Transaction::query()
                ->selectRaw('COALESCE(SUM(ABS(quantity)),0) as total')
                ->where('item_id', $itemId)
                ->where('barcode', $barcode)
                ->where('unit', $unit)
                ->where('transac_type', Inventory::OUTGOING->value)
                ->value('total');

            $remaining = $incoming - $outgoing;

            if ($remaining <= 0) {
                $validator->errors()->add('quantity', 'No remaining stock available for this item.');
                return;
            }

            if ((float)$requestedQty > $remaining) {
                $validator->errors()->add('quantity', 'Requested quantity (' . $requestedQty . ') exceeds remaining stock (' . $remaining . ').');
            }
        });
    }
}
