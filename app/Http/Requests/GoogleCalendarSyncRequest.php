<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoogleCalendarSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event' => ['required', 'array'],
            'event.id' => ['required', 'string', 'max:191'],
            'event.label' => ['required', 'string', 'max:255'],
            'event.subtitle' => ['nullable', 'string', 'max:500'],
            'event.type' => ['nullable', 'string', 'max:64'],
            'event.status' => ['nullable', 'string', 'max:64'],
            'event.date_from' => ['required', 'date'],
            'event.date_to' => ['nullable', 'date', 'after_or_equal:event.date_from'],
            'event.time_from' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'event.time_to' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'event.description' => ['nullable', 'string', 'max:2000'],
            'event.location' => ['nullable', 'string', 'max:255'],
            'event.portal_url' => ['nullable', 'url', 'max:2048'],
            'event.checkoutPage' => ['nullable', 'string', 'max:100'],
            'event.checkoutPageId' => ['nullable'],
        ];
    }
}