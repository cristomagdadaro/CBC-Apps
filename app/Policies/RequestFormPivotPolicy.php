<?php

namespace App\Policies;

use App\Models\RequestFormPivot;
use App\Models\User;
use App\Policies\Concerns\AuthorizesByPermission;

class RequestFormPivotPolicy
{
    use AuthorizesByPermission;

    public function view(?User $user, RequestFormPivot $requestFormPivot): bool
    {
        return $this->allowed($user, 'fes.request.approve');
    }

    public function viewAny(?User $user): bool
    {
        return $this->allowed($user, 'fes.request.approve');
    }

    public function update(?User $user, RequestFormPivot $requestFormPivot): bool
    {
        return $this->allowed($user, 'fes.request.approve');
    }
}
