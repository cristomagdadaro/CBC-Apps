<?php

namespace App\Http\Requests\Research;

use App\Models\Research\ResearchSample;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResearchMonitoringRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('research.monitoring.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'sample_id' => ['required', 'integer', 'exists:' . (new ResearchSample())->getTable() . ',id'],
            'stage' => ['required', Rule::in(array_keys(config('research.stages', [])))],
            'recorded_on' => ['required', 'date'],
            'parameter_set' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
            'selected_for_export' => ['sometimes', 'boolean'],
        ];
    }
}
