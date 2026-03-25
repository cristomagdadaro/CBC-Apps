<?php

namespace App\Http\Requests\Research;

use App\Models\Research\ResearchStudy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResearchExperimentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('research.experiments.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'study_id' => ['required', 'integer', 'exists:' . (new ResearchStudy())->getTable() . ',id'],
            'code' => ['nullable', 'string', 'max:40'],
            'title' => ['required', 'string', 'max:255'],
            'geographic_location' => ['nullable', 'string', 'max:255'],
            'season' => ['nullable', Rule::in(array_column(config('research.seasons', []), 'value'))],
            'commodity' => ['nullable', 'string', 'max:100'],
            'sample_type' => ['nullable', 'string', 'max:100'],
            'sample_descriptor' => ['nullable', 'string', 'max:255'],
            'pr_code' => ['nullable', 'string', 'max:120'],
            'cross_combination' => ['nullable', 'string'],
            'parental_background' => ['nullable', 'string'],
            'filial_generation' => ['nullable', 'string', 'max:80'],
            'generation' => ['nullable', 'string', 'max:80'],
            'plot_number' => ['nullable', 'string', 'max:80'],
            'field_number' => ['nullable', 'string', 'max:80'],
            'replication_number' => ['nullable', 'integer', 'min:1'],
            'planned_plant_count' => ['nullable', 'integer', 'min:0'],
            'background_notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
