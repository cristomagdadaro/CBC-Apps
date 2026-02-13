<?php

namespace App\Policies;

use App\Models\Form;
use App\Models\User;
use App\Policies\Concerns\AuthorizesByPermission;

class FormPolicy
{
    use AuthorizesByPermission;

    public function viewAny(?User $user): bool
    {
        return $this->allowed($user, 'event.forms.manage');
    }

    public function update(?User $user, Form $form): bool
    {
        return $this->allowed($user, 'event.forms.manage');
    }

    public function create(?User $user): bool
    {
        return $this->allowed($user, 'event.forms.manage');
    }

    public function delete(?User $user, Form $form): bool
    {
        return $this->allowed($user, 'event.forms.manage');
    }
}
