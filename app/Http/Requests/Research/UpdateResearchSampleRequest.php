<?php

namespace App\Http\Requests\Research;

use App\Models\Research\ResearchSample;
use Illuminate\Foundation\Http\FormRequest;

class UpdateResearchSampleRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ResearchSample|null $sample */
        $sample = $this->route('sample');

        return $sample
            ? ($this->user()?->can('update', $sample) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'commodity' => ['nullable', 'string', 'max:100'],
            'sample_type' => ['nullable', 'string', 'max:100'],
            'accession_name' => ['required', 'string', 'max:255'],
            'pr_code' => ['nullable', 'string', 'max:120'],
            'field_label' => ['nullable', 'string', 'max:120'],
            'line_label' => ['nullable', 'string', 'max:120'],
            'plant_label' => ['nullable', 'string', 'max:120'],
            'generation' => ['nullable', 'string', 'max:80'],
            'plot_number' => ['nullable', 'string', 'max:80'],
            'field_number' => ['nullable', 'string', 'max:80'],
            'replication_number' => ['nullable', 'integer', 'min:0'],
            'current_status' => ['nullable', 'string', 'max:120'],
            'current_location' => ['nullable', 'string', 'max:255'],
            'storage_location' => ['nullable', 'string', 'max:255'],
            'germination_date' => ['nullable', 'date'],
            'sowing_date' => ['nullable', 'date'],
            'harvest_date' => ['nullable', 'date'],
            'is_priority' => ['sometimes', 'boolean'],
            'legacy_reference' => ['nullable', 'string', 'max:120'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
