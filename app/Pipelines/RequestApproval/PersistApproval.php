<?php

namespace App\Pipelines\RequestApproval;

use App\Models\RequestFormPivot;
use Closure;

class PersistApproval
{
    public function handle(array $context, Closure $next): mixed
    {
        /** @var RequestFormPivot $model */
        $model = $context['model'];
        $validated = $context['validated'];

        $model->fill($validated);
        $model->save();

        $context['model'] = $model;

        return $next($context);
    }
}
