<?php

namespace App\Policies;

use App\Models\SuppEquipReport;
use App\Models\User;
use App\Policies\Concerns\AuthorizesByPermission;

class SuppEquipReportPolicy
{
    use AuthorizesByPermission;

    public function viewAny(?User $user): bool
    {
        return $this->allowed($user, 'equipment.report.manage');
    }

    public function create(?User $user): bool
    {
        return $this->allowed($user, 'equipment.report.manage');
    }

    public function update(?User $user, SuppEquipReport $suppEquipReport): bool
    {
        return $this->allowed($user, 'equipment.report.manage');
    }

    public function delete(?User $user, SuppEquipReport $suppEquipReport): bool
    {
        return $this->allowed($user, 'equipment.report.manage');
    }
}
