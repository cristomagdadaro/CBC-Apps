<?php

namespace App\Pipelines\EventSubform;

use App\Models\EventSubformResponse;
use Closure;

class CreateSubformResponse
{
    public function handle(array $context, Closure $next): mixed
    {
        $validated = $context['validated'];

        $context['subformResponse'] = EventSubformResponse::create([
            'form_parent_id' => $validated['form_parent_id'],
            'participant_id' => $validated['participant_id'] ?? null,
            'subform_type' => $validated['subform_type'],
            'response_data' => $validated['response_data'],
        ]);

        return $next($context);
    }
}
