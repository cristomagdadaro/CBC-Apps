<?php

namespace App\Http\Requests\Laboratory;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class LaboratoryUpdateEndUseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['nullable', 'string', 'max:32'],
            'end_use_at' => ['required', 'date'],
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
