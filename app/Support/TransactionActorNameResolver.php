<?php

namespace App\Support;

use App\Models\Personnel;
use App\Models\User;

class TransactionActorNameResolver
{
    public function resolve(?Personnel $personnel = null, ?User $user = null): ?string
    {
        return $this->resolvePersonnelName($personnel)
            ?? $this->resolveUserName($user);
    }

    private function resolvePersonnelName(?Personnel $personnel): ?string
    {
        if (!$personnel) {
            return null;
        }

        $name = trim(implode(' ', array_filter([
            $personnel->fname,
            $personnel->mname ? mb_substr(trim((string) $personnel->mname), 0, 1) . '.' : null,
            $personnel->lname,
            $personnel->suffix,
        ])));

        return $name !== '' ? $name : null;
    }

    private function resolveUserName(?User $user): ?string
    {
        if (!$user) {
            return null;
        }

        foreach ([$user->name, $user->email, $user->employee_id] as $candidate) {
            $value = trim((string) $candidate);

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }
}

