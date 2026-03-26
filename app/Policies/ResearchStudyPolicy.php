<?php

namespace App\Policies;

use App\Models\Research\ResearchStudy;
use App\Models\User;
use App\Policies\Concerns\AuthorizesByPermission;
use App\Services\Research\ResearchAccessService;

class ResearchStudyPolicy
{
    use AuthorizesByPermission;

    public function update(?User $user, ResearchStudy $study): bool
    {
        return $this->allowed($user, 'research.studies.manage')
            && app(ResearchAccessService::class)->canAccessStudy($user, $study);
    }

    public function delete(?User $user, ResearchStudy $study): bool
    {
        return $this->allowed($user, 'research.studies.manage')
            && app(ResearchAccessService::class)->canAccessStudy($user, $study);
    }
}
