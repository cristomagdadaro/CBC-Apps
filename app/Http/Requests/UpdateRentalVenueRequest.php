<?php

namespace App\Http\Requests;

use App\Repositories\OptionRepo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRentalVenueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('rental.venue.manage') ?? false;
    }

    public function rules(): array
    {
        $optionRepo = app(OptionRepo::class);

        return [
            'venue_type' => ['sometimes', 'string', 'in:'.implode(',', array_column($optionRepo->getEventHalls()->toArray(), 'name'))],
            'date_from' => ['sometimes', 'date', 'after_or_equal:today'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'time_from' => ['sometimes', 'date_format:H:i:s'],
            'time_to' => ['sometimes', 'date_format:H:i:s', 'after:time_from'],
            'expected_attendees' => ['sometimes', 'integer', 'min:1', 'max:5000'],
            'event_name' => ['sometimes', 'string', 'max:255'],
            'destination_location' => ['sometimes', 'string', 'max:255'],
            'destination_city' => ['sometimes', 'string', 'exists:loc_cities,city'],
            'destination_province' => ['sometimes', 'string', 'exists:loc_cities,province'],
            'destination_region' => ['sometimes', 'string', 'exists:loc_cities,region'],
            'requested_by' => ['sometimes', 'string', 'max:255'],
            'contact_number' => ['sometimes', 'string', 'regex:/^[0-9\-\+\s\(\)]*$/'],
            'status' => ['sometimes', Rule::in(['pending', 'approved', 'in_progress', 'rejected', 'cancelled', 'completed'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
