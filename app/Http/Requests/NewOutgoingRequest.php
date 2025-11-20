<?php

namespace App\Http\Requests;

use App\Enums\Inventory;
use App\Models\Personnel;
use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class NewOutgoingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $employee = $this->input('employee_id');

        if ($employee) {
            $personnel = Personnel::where('employee_id', $employee)->first();
            if ($personnel) {
                $this->merge([
                    'personnel_id' => $personnel->id,
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'item_id' => 'required|exists:items,id',
            'barcode' => [
                'required', 'string',
                Rule::when(
                    fn($input) => $input->transac_type === Inventory::INCOMING->value,
                    ['unique:transactions,barcode']
                ),
                Rule::when(
                    fn($input) => $input->transac_type === Inventory::OUTGOING->value,
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
                'min:1',
            ],
            'unit' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
            'employee_id' => 'required|string|exists:personnels,employee_id',
            'expiration' => 'date|nullable',
            'remarks' => 'string|nullable',
            'personnel_id' => 'required|exists:personnels,id',
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
            $requestedQty = $this->input('quantity'); // positive

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

            if ((float) $requestedQty > $remaining) {
                $validator->errors()->add('quantity', 'Requested quantity (' . $requestedQty . ') exceeds remaining stock (' . $remaining . ').');
            }
        });
    }
}
