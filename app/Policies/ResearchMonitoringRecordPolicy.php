<?php

namespace App\Policies;

use App\Models\Research\ResearchMonitoringRecord;
use App\Models\User;
use App\Policies\Concerns\AuthorizesByPermission;
use App\Services\Research\ResearchAccessService;

class ResearchMonitoringRecordPolicy
{
    use AuthorizesByPermission;

    public function update(?User $user, ResearchMonitoringRecord $record): bool
    {
        return $this->allowed($user, 'research.monitoring.manage')
            && app(ResearchAccessService::class)->canAccessRecord($user, $record);
    }

    public function delete(?User $user, ResearchMonitoringRecord $record): bool
    {
        return $this->allowed($user, 'research.monitoring.manage')
            && app(ResearchAccessService::class)->canAccessRecord($user, $record);
    }
}
