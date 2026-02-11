<?php

namespace App\Http\Requests\Laboratory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class LaboratoryCheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required_unless:is_guest,1', 'string', 'max:32'],
            'is_guest' => ['nullable', 'boolean'],
            'guest_name' => ['required_if:is_guest,1', 'string', 'max:150'],
            'guest_position' => ['required_if:is_guest,1', 'string', 'max:150'],
            'guest_affiliation' => ['required_if:is_guest,1', 'string', 'max:150'],
            'guest_email' => ['required_if:is_guest,1', 'email', 'max:150'],
            'guest_phone' => ['required_if:is_guest,1', 'string', 'max:50'],
            'end_use_at' => ['required', 'date'],
            'purpose' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $endUseAt = $this->input('end_use_at');

            if (!$endUseAt) {
                return;
            }

            $parsed = Carbon::parse($endUseAt);
            if ($parsed->isPast()) {
                $validator->errors()->add('end_use_at', 'End of use must be in the future.');
                return;
            }

            $maxHours = (int) config('laboratory.max_end_use_hours', 24);
            $maxAllowed = Carbon::now()->addHours($maxHours);
            if ($parsed->greaterThan($maxAllowed)) {
                $validator->errors()->add('end_use_at', "End of use must be within {$maxHours} hours.");
            }
        });
    }
}
