<?php

namespace App\Http\Requests;

use App\Enums\RentalTripType;
use App\Repositories\OptionRepo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
                'nullable',
                'string',
                !empty($vehicleTypes) ? Rule::in($vehicleTypes) : null,
            ])),
            'trip_type' => ['required', Rule::in(RentalTripType::values())],
            'date_from' => ['required', 'date', 'after_or_equal:today'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'time_from' => ['required', 'date_format:H:i:s'],
            'time_to' => ['required', 'date_format:H:i:s'],
            'purpose' => ['required', 'string', 'max:500'],
            'destination_location' => ['required', 'string', 'max:255'],
            'destination_city' => ['required', 'string', 'exists:loc_cities,city'],
            'destination_province' => ['required', 'string', 'exists:loc_cities,province'],
            'destination_region' => ['required', 'string', 'exists:loc_cities,region'],
            'destination_stops' => ['nullable', 'array', 'max:20'],
            'destination_stops.*' => ['required', 'string', 'max:255'],
            'requested_by' => ['required', 'string', 'max:255'],
            'members_of_party' => ['nullable', 'array', 'max:30'],
            'members_of_party.*' => ['required', 'string', 'max:255'],
            'is_shared_ride' => ['nullable', 'boolean'],
            'shared_ride_reference' => ['nullable', 'string', 'max:255', 'required_if:is_shared_ride,1'],
            'contact_number' => ['required', 'string', 'regex:/^[0-9\-\+\s\(\)]*$/'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $dateFrom = $this->input('date_from');
                $dateTo = $this->input('date_to');
                $timeFrom = $this->input('time_from');
                $timeTo = $this->input('time_to');

                // Only validate datetime combination if all fields are present and valid so far
                if ($validator->errors()->hasAny(['date_from', 'date_to', 'time_from', 'time_to'])) {
                    return;
                }

                // Create datetime strings for comparison
                $startDateTime = "{$dateFrom} {$timeFrom}";
                $endDateTime = "{$dateTo} {$timeTo}";

                // If dates are the same, time_to must be after time_from
                // If dates are different, any time is valid (as long as dates are valid)
                if ($dateFrom === $dateTo && strtotime($timeTo) <= strtotime($timeFrom)) {
                    $validator->errors()->add('time_to', 'The end time must be after the start time when renting on the same day.');
                }

                // Additional check: end datetime must be after start datetime
                if (strtotime($endDateTime) <= strtotime($startDateTime)) {
                    $validator->errors()->add('date_to', 'The rental end must be after the rental start.');
                }
            }
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_type.in' => 'The selected vehicle type is invalid.',
            'date_from.after_or_equal' => 'The rental date must be today or in the future.',
            'date_to.after_or_equal' => 'The end date must be after or equal to the start date.',
        ];
    }
}