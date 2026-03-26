<?php

namespace App\Http\Requests\Research;

use App\Http\Requests\Research\Concerns\ValidatesResearchMemberSelections;
use App\Models\Research\ResearchProject;
use Illuminate\Foundation\Http\FormRequest;

class UpdateResearchProjectRequest extends FormRequest
{
    use ValidatesResearchMemberSelections;

    public function authorize(): bool
    {
        /** @var ResearchProject|null $project */
        $project = $this->route('project');

        return $project
            ? ($this->user()?->can('update', $project) ?? false)
            : false;
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
            'project_leader_id' => ['nullable', 'string', 'exists:users,id'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateResearchUserSelection(
                $validator,
                'project_leader_id',
                'The selected project leader must have a Researcher or Research Supervisor role.'
            );
        });
    }
}
