<?php

namespace App\Http\Requests;

use App\Repositories\OptionRepo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRentalVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('rental.vehicle.manage') ?? false;
    }

    public function rules(): array
    {
        $optionRepo = app(OptionRepo::class);
        $vehicleTypes = collect($optionRepo->getVehicles())
            ->pluck('name')
            ->filter()
            ->values()
            ->all();

        return [
            'vehicle_type' => array_values(array_filter([
                'sometimes',
                'string',
                !empty($vehicleTypes) ? Rule::in($vehicleTypes) : null,
            ])),
            'date_from' => ['sometimes', 'date', 'after_or_equal:today'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'time_from' => ['sometimes', 'date_format:H:i:s'],
            'time_to' => ['sometimes', 'date_format:H:i:s', 'after:time_from'],
            'purpose' => ['sometimes', 'string', 'max:500'],
            'destination_location' => ['sometimes', 'string', 'max:255'],
            'destination_city' => ['sometimes', 'string', 'exists:loc_cities,city'],
            'destination_province' => ['sometimes', 'string', 'exists:loc_cities,province'],
            'destination_region' => ['sometimes', 'string', 'exists:loc_cities,region'],
            'requested_by' => ['sometimes', 'string', 'max:255'],
            'members_of_party' => ['sometimes', 'nullable', 'array', 'max:30'],
            'members_of_party.*' => ['required_with:members_of_party', 'string', 'max:255'],
            'contact_number' => ['sometimes', 'string', 'regex:/^[0-9\-\+\s\(\)]*$/'],
            'status' => ['sometimes', 'in:pending,approved,rejected'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
