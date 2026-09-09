<?php

namespace App\Http\Requests;

use App\Enums\RentalTripType;
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
                'nullable',
                'string',
                !empty($vehicleTypes) ? Rule::in($vehicleTypes) : null,
            ])),
            'trip_type' => ['sometimes', Rule::in(RentalTripType::values())],
            'date_from' => ['sometimes', 'date', 'after_or_equal:today'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'time_from' => ['sometimes', 'date_format:H:i:s'],
            'time_to' => ['sometimes', 'date_format:H:i:s', 'after:time_from'],
            'purpose' => ['sometimes', 'string', 'max:500'],
            'destination_location' => ['sometimes', 'string', 'max:255'],
            'destination_city' => ['sometimes', 'string', 'exists:loc_cities,city'],
            'destination_province' => ['sometimes', 'string', 'exists:loc_cities,province'],
            'destination_region' => ['sometimes', 'string', 'exists:loc_cities,region'],
            'destination_stops' => ['sometimes', 'nullable', 'array', 'max:20'],
            'destination_stops.*' => ['required_with:destination_stops', 'string', 'max:255'],
            'requested_by' => ['sometimes', 'string', 'max:255'],
            'members_of_party' => ['sometimes', 'nullable', 'array', 'max:30'],
            'members_of_party.*' => ['required_with:members_of_party', 'string', 'max:255'],
            'is_shared_ride' => ['sometimes', 'boolean'],
            'shared_ride_reference' => ['sometimes', 'nullable', 'string', 'max:255'],
            'contact_number' => ['sometimes', 'string', 'regex:/^[0-9\-\+\s\(\)]*$/'],
            'status' => ['sometimes', Rule::in(['pending', 'approved', 'in_progress', 'rejected', 'cancelled', 'completed'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'travel_details' => ['nullable', 'array'],
            'travel_details.transport_mode' => ['nullable', 'string', 'in:vehicle,commute'],
            'travel_details.requires_flight' => ['nullable', 'boolean'],
            'travel_details.itinerary' => ['nullable', 'string', 'max:5000'],
            'travel_details.charging_project' => ['nullable', 'string', 'max:255'],
            'travel_details.tracking_number' => ['nullable', 'string', 'max:255'],
            'travel_details.preparer_id' => ['nullable', 'exists:personnels,id'],
            'travel_details.pickup_point' => ['nullable', 'string', 'max:255'],
            'travel_details.flights' => ['nullable', 'array'],
            'travel_details.flights.*.airline' => ['required_with:travel_details.flights', 'string', 'max:255'],
            'travel_details.flights.*.departure_airport' => ['required_with:travel_details.flights', 'string', 'max:255'],
            'travel_details.flights.*.arrival_airport' => ['required_with:travel_details.flights', 'string', 'max:255'],
            'travel_details.flights.*.flight_date' => ['required_with:travel_details.flights', 'date'],
            'travel_details.flights.*.flight_etd' => ['required_with:travel_details.flights', 'date_format:H:i:s'],
            'travel_details.flights.*.flight_eta' => ['required_with:travel_details.flights', 'date_format:H:i:s'],
            'travel_details.flights.*.is_return' => ['nullable', 'boolean'],
        ];
    }
}
