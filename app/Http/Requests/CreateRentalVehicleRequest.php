<?php

namespace App\Http\Requests;

use App\Repositories\OptionRepo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRentalVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
                'required',
                'string',
                !empty($vehicleTypes) ? Rule::in($vehicleTypes) : null,
            ])),
            'date_from' => ['required', 'date', 'after_or_equal:today'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'time_from' => ['required', 'date_format:H:i:s'],
            'time_to' => ['required', 'date_format:H:i:s', 'after:time_from'],
            'purpose' => ['required', 'string', 'max:500'],
            'destination_location' => ['required', 'string', 'max:255'],
            'destination_city' => ['required', 'string', 'exists:loc_cities,city'],
            'destination_province' => ['required', 'string', 'exists:loc_cities,province'],
            'destination_region' => ['required', 'string', 'exists:loc_cities,region'],
            'requested_by' => ['required', 'string', 'max:255'],
            'members_of_party' => ['nullable', 'array', 'max:30'],
            'members_of_party.*' => ['required', 'string', 'max:255'],
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
