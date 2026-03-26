<?php

namespace App\Http\Requests\Research;

use App\Models\Research\ResearchMonitoringRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateResearchMonitoringRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ResearchMonitoringRecord|null $record */
        $record = $this->route('record');

        return $record
            ? ($this->user()?->can('update', $record) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'stage' => ['required', Rule::in(array_keys(config('research.stages', [])))],
            'recorded_on' => ['required', 'date'],
            'parameter_set' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
            'selected_for_export' => ['sometimes', 'boolean'],
        ];
    }
}
