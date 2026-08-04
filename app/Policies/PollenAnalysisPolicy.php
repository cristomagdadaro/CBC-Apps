<?php

namespace App\Policies;

use App\Models\PollenAnalysis;
use App\Models\User;
use App\Policies\Concerns\AuthorizesByPermission;

class PollenAnalysisPolicy
{
    use AuthorizesByPermission;

    public function viewAny(User $user): bool
    {
        return $this->allowed($user, 'pollen.manage');
    }

    public function view(User $user, PollenAnalysis $model): bool
    {
        return $user->id === $model->user_id || $this->allowed($user, 'pollen.manage');
    }

    public function create(User $user): bool
    {
        return $this->allowed($user, 'pollen.manage');
    }

    public function update(User $user, PollenAnalysis $model): bool
    {
        return false; // Analyses are immutable
    }

    public function delete(User $user, PollenAnalysis $model): bool
    {
        return $user->hasRole('admin');
    }
}
