<?php

namespace App\Pipelines\InventoryTransaction;

use Closure;

class PersistTransaction
{
    public function handle(array $context, Closure $next): mixed
    {
        $repo = $context['repo'];
        $payload = $context['payload'];

        $context['model'] = $repo->create($payload);

        return $next($context);
    }
}
