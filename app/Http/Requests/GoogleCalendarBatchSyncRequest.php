<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoogleCalendarBatchSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'events' => ['required', 'array', 'min:1', 'max:50'],
            'events.*.id' => ['required', 'string', 'max:191'],
            'events.*.label' => ['required', 'string', 'max:255'],
            'events.*.subtitle' => ['nullable', 'string', 'max:500'],
            'events.*.type' => ['nullable', 'string', 'max:64'],
            'events.*.status' => ['nullable', 'string', 'max:64'],
            'events.*.date_from' => ['required', 'date'],
            'events.*.date_to' => ['nullable', 'date'],
            'events.*.time_from' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'events.*.time_to' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'events.*.description' => ['nullable', 'string', 'max:2000'],
            'events.*.location' => ['nullable', 'string', 'max:255'],
            'events.*.portal_url' => ['nullable', 'url', 'max:2048'],
            'events.*.checkoutPage' => ['nullable', 'string', 'max:100'],
            'events.*.checkoutPageId' => ['nullable'],
        ];
    }
}