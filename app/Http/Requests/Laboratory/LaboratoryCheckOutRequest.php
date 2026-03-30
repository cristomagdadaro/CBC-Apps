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
            'employee_id' => ['nullable', 'string', 'max:32'],
            'admin_override' => ['nullable', 'boolean'],
        ];
    }
}
