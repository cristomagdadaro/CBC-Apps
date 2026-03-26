<?php

namespace App\Policies;

use App\Models\Research\ResearchSample;
use App\Models\User;
use App\Policies\Concerns\AuthorizesByPermission;
use App\Services\Research\ResearchAccessService;

class ResearchSamplePolicy
{
    use AuthorizesByPermission;

    public function update(?User $user, ResearchSample $sample): bool
    {
        return $this->allowed($user, 'research.samples.manage')
            && app(ResearchAccessService::class)->canAccessSample($user, $sample);
    }

    public function delete(?User $user, ResearchSample $sample): bool
    {
        return $this->allowed($user, 'research.samples.manage')
            && app(ResearchAccessService::class)->canAccessSample($user, $sample);
    }
}
