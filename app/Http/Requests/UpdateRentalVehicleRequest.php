<?php

namespace App\Http\Requests;

use App\Repositories\OptionRepo;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRentalVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('rental.vehicle.manage') ?? false;
    }

    public function rules(): array
    {
        $optionRepo = app(OptionRepo::class);

        return [
            'vehicle_type' => ['sometimes', 'string', 'in:'.implode(',', array_column($optionRepo->getVehicles()->toArray(), 'name'))],
            'date_from' => ['sometimes', 'date', 'after_or_equal:today'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'time_from' => ['sometimes', 'date_format:H:i:s'],
            'time_to' => ['sometimes', 'date_format:H:i:s', 'after:time_from'],
            'purpose' => ['sometimes', 'string', 'max:500'],
            'requested_by' => ['sometimes', 'string', 'max:255'],
            'contact_number' => ['sometimes', 'string', 'regex:/^[0-9\-\+\s\(\)]*$/'],
            'status' => ['sometimes', 'in:pending,approved,rejected,completed,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
