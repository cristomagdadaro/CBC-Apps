<?php

namespace App\Pipelines\InventoryTransaction;

use App\Models\User;
use Closure;

class ResolveUserByEmployeeId
{
    public function handle(array $context, Closure $next): mixed
    {
        $payload = $context['payload'];

        if (empty($payload['user_id']) && !empty($payload['employee_id'])) {
            $user = User::where('employee_id', $payload['employee_id'])->first();
            if ($user) {
                $payload['user_id'] = $user->id;
                $payload['personnel_id'] = $user->id;
            }
        }

        $context['payload'] = $payload;

        return $next($context);
    }
}
