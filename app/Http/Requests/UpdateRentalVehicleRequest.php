<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRentalVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_type' => ['sometimes', 'string', 'in:innova,pickup,van,suv'],
            'date_from' => ['sometimes', 'date', 'after_or_equal:today'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'time_from' => ['sometimes', 'date_format:H:i'],
            'time_to' => ['sometimes', 'date_format:H:i', 'after:time_from'],
            'purpose' => ['sometimes', 'string', 'max:500'],
            'requested_by' => ['sometimes', 'string', 'max:255'],
            'contact_number' => ['sometimes', 'string', 'regex:/^[0-9\-\+\s\(\)]*$/'],
            'status' => ['sometimes', 'in:pending,approved,rejected,completed,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
