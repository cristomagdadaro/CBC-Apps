<?php

namespace App\Pipelines\InventoryTransaction;

use App\Models\User;
use App\Models\Personnel;
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
                if (empty($payload['personnel_id'])) {
                    $personnel = Personnel::where('employee_id', $payload['employee_id'])->first();
                    if ($personnel) {
                        $payload['personnel_id'] = $personnel->id;
                    }
                }
            }
        }

        $context['payload'] = $payload;

        return $next($context);
    }
}
