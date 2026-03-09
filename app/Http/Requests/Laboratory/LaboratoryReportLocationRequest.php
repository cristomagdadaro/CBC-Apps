<?php

namespace App\Http\Requests\Laboratory;

use Illuminate\Foundation\Http\FormRequest;

class LaboratoryReportLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'string', 'max:32'],
            'location_label' => ['required', 'string', 'max:120'],
            'location_code' => ['nullable', 'string', 'max:32'],
        ];
    }
}
