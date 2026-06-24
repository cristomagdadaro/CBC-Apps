<?php

namespace App\Http\Requests\Laboratory;

use Illuminate\Foundation\Http\FormRequest;

class LaboratoryCheckOutRequest extends FormRequest
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
                    if ($personnel && strtolower($personnel->status) === strtolower(config('system.statuses.suspended'))) {
                        $fail('This personnel ID is suspended and cannot be used for equipment logger services.');
                    }
                }
            ],
            'admin_override' => ['nullable', 'boolean'],
        ];
    }
}
