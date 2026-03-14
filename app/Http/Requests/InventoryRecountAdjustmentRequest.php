<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryRecountAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'barcode' => ['required', 'string', 'max:191'],
            'physical_count' => ['required', 'integer', 'min:0'],
            'location_code' => ['nullable', 'string', 'max:32', 'required_with:location_label'],
            'location_label' => ['nullable', 'string', 'max:120', 'required_with:location_code'],
        ];
    }
}
