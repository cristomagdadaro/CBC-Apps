<?php

namespace App\Http\Requests\Research;

use App\Http\Requests\Research\Concerns\ValidatesResearchMemberSelections;
use App\Models\Research\ResearchProject;
use Illuminate\Foundation\Http\FormRequest;

class StoreResearchStudyRequest extends FormRequest
{
    use ValidatesResearchMemberSelections;

    public function authorize(): bool
    {
        $projectId = $this->input('project_id');

        if (! $projectId) {
            return false;
        }

        /** @var ResearchProject|null $project */
        $project = ResearchProject::query()->find($projectId);

        return $project
            ? ($this->user()?->can('update', $project) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:' . (new ResearchProject())->getTable() . ',id'],
            'code' => ['nullable', 'string', 'max:40'],
            'title' => ['required', 'string', 'max:255'],
            'objective' => ['nullable', 'string'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'study_leader_id' => ['nullable', 'string', 'exists:users,id'],
            'staff_member_ids' => ['nullable', 'array'],
            'staff_member_ids.*' => ['string', 'distinct', 'exists:users,id'],
            'supervisor_id' => ['nullable', 'string', 'exists:users,id'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateResearchUserSelection(
                $validator,
                'study_leader_id',
                'The selected study leader must have a Researcher or Research Supervisor role.'
            );
            $this->validateResearchUserSelection(
                $validator,
                'supervisor_id',
                'The selected supervisor must have a Researcher or Research Supervisor role.'
            );
            $this->validateResearchUserSelections(
                $validator,
                'staff_member_ids',
                'Each study staff member must have a Researcher or Research Supervisor role.'
            );
        });
    }
}
