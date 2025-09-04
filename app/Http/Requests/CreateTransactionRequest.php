<?php

namespace App\Http\Requests;

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
        do {
            $temp = Str::uuid()->toString();
        } while (Transaction::where('id', $temp)->exists());

        $this->merge([
            'id' => $temp,
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
            'item_id' => 'required|exists:items,id',
            'barcode' => 'nullable|string|unique:transactions,barcode',
            'transac_type' => [
                'required',
                'string',
                Rule::in(['incoming', 'outgoing']),
            ],
            'quantity' => [
                'required',
                'numeric',
                Rule::when(
                    fn ($input) => $input->transac_type === 'incoming',
                    ['min:1']
                ),
                Rule::when(
                    fn ($input) => $input->transac_type === 'outgoing',
                    ['max:-1']
                ),
            ],
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
