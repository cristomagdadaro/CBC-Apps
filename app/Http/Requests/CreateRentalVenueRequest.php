<?php

namespace App\Http\Requests;

use App\Repositories\OptionRepo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreateRentalVenueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $optionRepo = app(OptionRepo::class);
        return [
            'venue_type' => ['required', 'string', 'in:'.implode(',', array_column($optionRepo->getEventHalls()->toArray(), 'name'))],
            'date_from' => ['required', 'date', 'after_or_equal:today'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'time_from' => ['required', 'date_format:H:i:s'],
            'time_to' => ['required', 'date_format:H:i:s'],
            'expected_attendees' => ['required', 'integer', 'min:1', 'max:5000'],
            'event_name' => ['required', 'string', 'max:255'],
            'destination_location' => ['required', 'string', 'max:255'],
            'destination_city' => ['required', 'string', 'exists:loc_cities,city'],
            'destination_province' => ['required', 'string', 'exists:loc_cities,province'],
            'destination_region' => ['required', 'string', 'exists:loc_cities,region'],
            'requested_by' => ['required', 'string', 'max:255'],
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

                // Skip if basic validation already failed
                if ($validator->errors()->hasAny(['date_from', 'date_to', 'time_from', 'time_to'])) {
                    return;
                }

                // Same-day event: time_to must be after time_from
                if ($dateFrom === $dateTo && strtotime($timeTo) <= strtotime($timeFrom)) {
                    $validator->errors()->add(
                        'time_to', 
                        'The end time must be after the start time for same-day events.'
                    );
                }

                // Ensure complete end datetime is after start datetime
                $startDateTime = strtotime("{$dateFrom} {$timeFrom}");
                $endDateTime = strtotime("{$dateTo} {$timeTo}");

                if ($endDateTime <= $startDateTime) {
                    $validator->errors()->add(
                        'date_to', 
                        'The event end datetime must be after the event start datetime.'
                    );
                }
            }
        ];
    }

    public function messages(): array
    {
        return [
            'venue_type.in' => 'The selected venue type is invalid.',
            'date_from.after_or_equal' => 'The rental date must be today or in the future.',
            'date_to.after_or_equal' => 'The end date must be after or equal to the start date.',
            'expected_attendees.min' => 'Expected attendees must be at least 1.',
        ];
    }
}