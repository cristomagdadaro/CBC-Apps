<?php

namespace App\Policies;

use App\Models\Research\ResearchExperiment;
use App\Models\User;
use App\Policies\Concerns\AuthorizesByPermission;
use App\Services\Research\ResearchAccessService;

class ResearchExperimentPolicy
{
    use AuthorizesByPermission;

    public function view(?User $user, ResearchExperiment $experiment): bool
    {
        return $this->allowed($user, 'research.projects.view')
            && app(ResearchAccessService::class)->canAccessExperiment($user, $experiment);
    }

    public function update(?User $user, ResearchExperiment $experiment): bool
    {
        return $this->allowed($user, 'research.experiments.manage')
            && app(ResearchAccessService::class)->canAccessExperiment($user, $experiment);
    }

    public function delete(?User $user, ResearchExperiment $experiment): bool
    {
        return $this->allowed($user, 'research.experiments.manage')
            && app(ResearchAccessService::class)->canAccessExperiment($user, $experiment);
    }
}
