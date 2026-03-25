<?php

namespace App\Pipelines\RequestApproval;

use Closure;
use Illuminate\Support\Facades\Auth;

class PrepareApprovalPayload
{
    public function handle(array $context, Closure $next): mixed
    {
        $validated = $context['validated'];
        $status = $validated['request_status'] ?? $context['model']->request_status;

        $context['validated'] = [
            'request_status' => $status,
            'approval_constraint' => $status === 'approved'
                ? ($validated['approval_constraint'] ?? null)
                : null,
            'disapproved_remarks' => $status === 'rejected'
                ? ($validated['disapproved_remarks'] ?? null)
                : null,
            'approved_by' => Auth::user()?->name,
        ];

        return $next($context);
    }
}
