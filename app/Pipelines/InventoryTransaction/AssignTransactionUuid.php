<?php

namespace App\Pipelines\InventoryTransaction;

use Closure;
use Illuminate\Support\Str;

class AssignTransactionUuid
{
    public function handle(array $context, Closure $next): mixed
    {
        $payload = $context['payload'];

        if (empty($payload['id'])) {
            $payload['id'] = (string) Str::uuid();
        }

        $context['payload'] = $payload;

        return $next($context);
    }
}
