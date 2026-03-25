<?php

namespace App\Http\Requests\Research;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResearchStudyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('research.studies.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'objective' => ['nullable', 'string'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'study_leader' => ['nullable', 'array'],
            'study_leader.name' => ['nullable', 'string', 'max:255'],
            'study_leader.position' => ['nullable', 'string', 'max:255'],
            'staff_members' => ['nullable', 'array'],
            'staff_members.*.name' => ['nullable', 'string', 'max:255'],
            'staff_members.*.position' => ['nullable', 'string', 'max:255'],
            'supervisor' => ['nullable', 'array'],
            'supervisor.name' => ['nullable', 'string', 'max:255'],
            'supervisor.position' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
