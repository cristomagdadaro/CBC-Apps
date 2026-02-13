<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use App\Policies\Concerns\AuthorizesByPermission;

class TransactionPolicy
{
    use AuthorizesByPermission;

    public function viewAny(?User $user): bool
    {
        return $this->allowed($user, 'inventory.manage');
    }

    public function create(?User $user): bool
    {
        return $this->allowed($user, 'inventory.manage');
    }

    public function update(?User $user, Transaction $transaction): bool
    {
        return $this->allowed($user, 'inventory.manage');
    }

    public function delete(?User $user, Transaction $transaction): bool
    {
        return $this->allowed($user, 'inventory.manage');
    }
}
