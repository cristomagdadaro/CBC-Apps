<?php

namespace App\Http\Requests\Laboratory;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class LaboratoryCheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => [
                'required',
                'string',
                'max:32',
                function ($attribute, $value, $fail) {
                    $personnel = \App\Models\Personnel::where('employee_id', $value)->first();
                    if ($personnel && strtolower($personnel->status ?? '') === strtolower(config('system.statuses.suspended', 'Suspended'))) {
                        $fail('This personnel ID is suspended and cannot be used for equipment logger services.');
                    }
                }
            ],
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
            }
        });
    }
}
