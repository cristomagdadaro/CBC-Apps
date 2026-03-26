<?php

namespace App\Http\Requests\Research;

use App\Models\Research\ResearchProject;
use App\Models\Research\ResearchStudy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GetResearchExperimentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge([
            'project_id' => ['nullable', 'integer', 'exists:' . (new ResearchProject())->getTable() . ',id'],
            'study_id' => ['nullable', 'integer', 'exists:' . (new ResearchStudy())->getTable() . ',id'],
        ], config('searching'));
    }
}