<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Repositories\OptionRepo;

class CreateRequestForm extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $equipmentsToUse = $this->mergeUniqueArrays(
            $this->input('equipments_to_use'),
            $this->input('ict_equipments'),
            $this->input('laboratory_equipments'),
            $this->input('biofreezers'),
            $this->input('medicool_units'),
            $this->input('plant_growth_chambers'),
        );

        $labsToUse = $this->mergeUniqueArrays(
            $this->input('labs_to_use'),
            $this->input('laboratory_access'),
            $this->input('field_spaces'),
            $this->input('screenhouse_spaces'),
            $this->input('office_spaces'),
            $this->input('storage_spaces'),
            $this->input('utility_spaces'),
            $this->input('parking_spaces'),
        );

        $consumablesToUse = $this->mergeUniqueArrays(
            $this->input('consumables_to_use'),
            $this->input('ict_supplies'),
            $this->input('laboratory_consumables'),
            $this->input('office_supplies'),
            $this->input('iec_materials'),
            $this->input('tokens'),
        );

        $this->merge([
            'equipments_to_use' => $equipmentsToUse,
            'labs_to_use' => $labsToUse,
            'consumables_to_use' => $consumablesToUse,
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $requestTypes = app(OptionRepo::class)->getRequestTypes()->pluck('label')->toArray();
        
        return [
            'request_type' => 'nullable|array',
            'request_type.*' => 'string|in:'.implode(',', $requestTypes),
            'request_details' => 'nullable|string',
            'request_purpose' => 'required|string',
            'project_title' => 'nullable|string',
            'date_of_use' => 'required|date',
            'time_of_use' => 'required|date_format:H:i:s',
            'date_of_use_end' => [
                Rule::requiredIf(fn () => $this->requiresEndTime()),
                'nullable',
                'date',
                'after_or_equal:date_of_use',
            ],
            'time_of_use_end' => [
                Rule::requiredIf(fn () => $this->requiresEndTime()),
                'nullable',
                'date_format:H:i:s',
            ],
            'labs_to_use' => 'nullable|array',
            'equipments_to_use' => 'nullable|array',
            'consumables_to_use' => 'nullable|array',
        ];
    }

    private function requiresEndTime(): bool
    {
        $types = $this->input('request_type', []);

        if (!is_array($types)) {
            $types = [$types];
        }

        return in_array('Equipments', $types, true) || in_array('Laboratory Access', $types, true);
    }

    private function mergeUniqueArrays(mixed ...$values): array
    {
        $items = collect($values)
            ->flatMap(function ($value) {
                if (is_array($value)) {
                    return $value;
                }

                return filled($value) ? [$value] : [];
            })
            ->filter(fn ($value) => filled($value))
            ->values()
            ->all();

        return array_values(array_unique($items));
    }
}
