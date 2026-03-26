<?php

namespace App\Policies;

use App\Models\Research\ResearchProject;
use App\Models\User;
use App\Policies\Concerns\AuthorizesByPermission;
use App\Services\Research\ResearchAccessService;

class ResearchProjectPolicy
{
    use AuthorizesByPermission;

    public function viewAny(?User $user): bool
    {
        return $this->allowed($user, 'research.projects.view');
    }

    public function create(?User $user): bool
    {
        return $this->allowed($user, 'research.projects.create');
    }

    public function view(?User $user, ResearchProject $project): bool
    {
        return $this->allowed($user, 'research.projects.view')
            && app(ResearchAccessService::class)->canAccessProject($user, $project);
    }

    public function update(?User $user, ResearchProject $project): bool
    {
        return $this->allowed($user, 'research.projects.update')
            && app(ResearchAccessService::class)->canAccessProject($user, $project);
    }

    public function delete(?User $user, ResearchProject $project): bool
    {
        return $this->allowed($user, 'research.projects.delete')
            && app(ResearchAccessService::class)->canAccessProject($user, $project);
    }
}
