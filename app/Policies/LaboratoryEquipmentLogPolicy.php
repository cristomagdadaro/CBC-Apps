<?php

namespace App\Policies;

use App\Models\LaboratoryEquipmentLog;
use App\Models\User;
use App\Policies\Concerns\AuthorizesByPermission;

class LaboratoryEquipmentLogPolicy
{
    use AuthorizesByPermission;

    public function viewAny(?User $user): bool
    {
        return $this->allowed($user, 'laboratory.logger.manage');
    }

    public function create(?User $user): bool
    {
        return $this->allowed($user, 'laboratory.logger.manage');
    }

    public function update(?User $user, LaboratoryEquipmentLog $log): bool
    {
        return $this->allowed($user, 'laboratory.logger.manage');
    }
}
