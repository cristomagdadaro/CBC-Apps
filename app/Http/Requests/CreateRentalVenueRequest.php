<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRentalVenueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'venue_type' => ['required', 'string', 'in:plenary,training_room,mph'],
            'date_from' => ['required', 'date', 'after_or_equal:today'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'time_from' => ['required', 'date_format:H:i'],
            'time_to' => ['required', 'date_format:H:i', 'after:time_from'],
            'expected_attendees' => ['required', 'integer', 'min:1', 'max:5000'],
            'event_name' => ['required', 'string', 'max:255'],
            'requested_by' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'regex:/^[0-9\-\+\s\(\)]*$/'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'venue_type.in' => 'The selected venue type is invalid.',
            'date_from.after_or_equal' => 'The rental date must be today or in the future.',
            'date_to.after_or_equal' => 'The end date must be after or equal to the start date.',
            'time_to.after' => 'The end time must be after the start time.',
            'expected_attendees.min' => 'Expected attendees must be at least 1.',
        ];
    }
}
