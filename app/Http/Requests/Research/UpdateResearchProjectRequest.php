<?php

namespace App\Http\Requests\Research;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResearchProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('research.projects.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'commodity' => ['nullable', 'string', 'max:100'],
            'duration_start' => ['nullable', 'date'],
            'duration_end' => ['nullable', 'date', 'after_or_equal:duration_start'],
            'overall_budget' => ['nullable', 'numeric', 'min:0'],
            'objective' => ['nullable', 'string'],
            'funding_agency' => ['nullable', 'string', 'max:255'],
            'funding_code' => ['nullable', 'string', 'max:120'],
            'project_leader' => ['nullable', 'array'],
            'project_leader.name' => ['nullable', 'string', 'max:255'],
            'project_leader.position' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
