<?php

namespace App\Http\Requests;

use App\Models\RentalVehicle;
use Illuminate\Foundation\Http\FormRequest;

class CreateRentalVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_type' => ['required', 'string', 'in:'.implode(',', array_column(config('system.vehicles'), 'name'))],
            'date_from' => ['required', 'date', 'after_or_equal:today'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'time_from' => ['required', 'date_format:H:i:s'],
            'time_to' => ['required', 'date_format:H:i:s', 'after:time_from'],
            'purpose' => ['required', 'string', 'max:500'],
            'requested_by' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'regex:/^[0-9\-\+\s\(\)]*$/'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_type.in' => 'The selected vehicle type is invalid.',
            'date_from.after_or_equal' => 'The rental date must be today or in the future.',
            'date_to.after_or_equal' => 'The end date must be after or equal to the start date.',
            'time_to.after' => 'The end time must be after the start time.',
        ];
    }
}
