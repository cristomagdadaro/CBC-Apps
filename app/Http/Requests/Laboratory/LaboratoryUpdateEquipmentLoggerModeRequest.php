<?php

namespace App\Http\Requests\Laboratory;

use App\Repositories\OptionRepo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LaboratoryUpdateEquipmentLoggerModeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'equipment_logger_mode' => [
                'required',
                'string',
                Rule::in(app(OptionRepo::class)->getEquipmentLoggerModeValues()),
            ],
        ];
    }
}

