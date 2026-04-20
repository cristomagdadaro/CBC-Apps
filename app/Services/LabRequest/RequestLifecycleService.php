<?php

namespace App\Services\LabRequest;

use App\Mail\UseRequestLifecycleMail;
use App\Models\Personnel;
use App\Models\RequestFormPivot;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class RequestLifecycleService
{
    public function dispatchScheduledOverdueNotifications(): int
    {
        $sentCount = 0;

        RequestFormPivot::query()
            ->with(['requester', 'request_form'])
            ->where('request_status', RequestFormPivot::STATUS_RELEASED)
            ->whereNull('returned_at')
            ->whereNull('overdue_notified_at')
            ->whereHas('request_form', function ($query) {
                $query->whereNotNull('date_of_use_end')
                    ->whereNotNull('time_of_use_end')
                    ->whereRaw('TIMESTAMP(date_of_use_end, time_of_use_end) < ?', [now()->toDateTimeString()]);
            })
            ->chunkById(100, function (Collection $requests) use (&$sentCount) {
                $requests->each(function (RequestFormPivot $record) use (&$sentCount) {
                    if (!$record->is_overdue) {
                        return;
                    }

                    $this->sendLifecycleMail($record, 'overdue');
                    $record->forceFill([
                        'overdue_notified_at' => now(),
                    ])->save();

                    $sentCount++;
                });
            }, 'id');

        return $sentCount;
    }

    public function prepareTransition(RequestFormPivot $model, array $validated, ?string $actorName): array
    {
        $requestedStatus = (string) ($validated['request_status'] ?? $model->request_status);
        $currentStatus = (string) $model->request_status;

        if ($requestedStatus !== $currentStatus) {
            $this->ensureValidTransition($model, $requestedStatus);
        }

        $payload = [
            'request_status' => $requestedStatus,
            'approval_constraint' => $this->normalizeNullableString($validated['approval_constraint'] ?? null),
            'disapproved_remarks' => $this->normalizeNullableString($validated['disapproved_remarks'] ?? null),
        ];

        if (in_array($requestedStatus, [RequestFormPivot::STATUS_APPROVED, RequestFormPivot::STATUS_RELEASED, RequestFormPivot::STATUS_RETURNED], true)) {
            $payload['disapproved_remarks'] = null;
        }

        if ($requestedStatus === RequestFormPivot::STATUS_REJECTED) {
            $payload['approval_constraint'] = null;
        }

        if ($requestedStatus === RequestFormPivot::STATUS_APPROVED && $requestedStatus !== $currentStatus) {
            $payload['approved_by'] = $actorName;
            $payload['approved_at'] = now();
            $payload['released_by'] = null;
            $payload['released_at'] = null;
            $payload['returned_by'] = null;
            $payload['returned_at'] = null;
            $payload['overdue_notified_at'] = null;
        }

        if ($requestedStatus === RequestFormPivot::STATUS_RELEASED && $requestedStatus !== $currentStatus) {
            $payload['released_by'] = $actorName;
            $payload['released_at'] = now();
            $payload['returned_by'] = null;
            $payload['returned_at'] = null;
            $payload['overdue_notified_at'] = null;
        }

        if ($requestedStatus === RequestFormPivot::STATUS_RETURNED && $requestedStatus !== $currentStatus) {
            $payload['returned_by'] = $actorName;
            $payload['returned_at'] = now();
        }

        if ($requestedStatus === RequestFormPivot::STATUS_REJECTED && $requestedStatus !== $currentStatus) {
            $payload['approved_by'] = $actorName;
            $payload['approved_at'] = $model->approved_at;
            $payload['released_by'] = null;
            $payload['released_at'] = null;
            $payload['returned_by'] = null;
            $payload['returned_at'] = null;
            $payload['overdue_notified_at'] = null;
        }

        return [
            'payload' => $payload,
            'previous_status' => $currentStatus,
            'requested_status' => $requestedStatus,
        ];
    }

    public function sendTransitionNotification(RequestFormPivot $model, ?string $previousStatus, ?string $requestedStatus): void
    {
        if (!$requestedStatus || $requestedStatus === $previousStatus) {
            return;
        }

        if (!in_array($requestedStatus, [
            RequestFormPivot::STATUS_APPROVED,
            RequestFormPivot::STATUS_RELEASED,
            RequestFormPivot::STATUS_RETURNED,
            RequestFormPivot::STATUS_REJECTED,
        ], true)) {
            return;
        }

        $this->sendLifecycleMail($model, $requestedStatus);
    }

    public function syncReturningPersonnel(?string $philriceId, array $requesterData): void
    {
        $philriceId = trim((string) $philriceId);

        if ($philriceId === '') {
            return;
        }

        $personnel = Personnel::query()->preferredEmployeeId($philriceId)->first();

        if (!$personnel) {
            [$fname, $mname, $lname] = $this->splitName((string) ($requesterData['name'] ?? ''));

            Personnel::query()->create([
                'employee_id' => $philriceId,
                'fname' => $fname,
                'mname' => $mname,
                'lname' => $lname,
                'position' => $requesterData['position'] ?? null,
                'phone' => $requesterData['phone'] ?? null,
                'email' => $requesterData['email'] ?? null,
            ]);

            return;
        }

        $personnel->fill([
            'position' => $personnel->position ?: ($requesterData['position'] ?? null),
            'phone' => $personnel->phone ?: ($requesterData['phone'] ?? null),
            'email' => $personnel->email ?: ($requesterData['email'] ?? null),
        ]);

        if ($personnel->isDirty()) {
            $personnel->save();
        }
    }

    private function ensureValidTransition(RequestFormPivot $model, string $requestedStatus): void
    {
        $allowedTransitions = [
            RequestFormPivot::STATUS_PENDING => [
                RequestFormPivot::STATUS_APPROVED,
                RequestFormPivot::STATUS_REJECTED,
            ],
            RequestFormPivot::STATUS_APPROVED => [
                RequestFormPivot::STATUS_RELEASED,
                RequestFormPivot::STATUS_REJECTED,
            ],
            RequestFormPivot::STATUS_RELEASED => [
                RequestFormPivot::STATUS_RETURNED,
            ],
            RequestFormPivot::STATUS_RETURNED => [],
            RequestFormPivot::STATUS_REJECTED => [],
        ];

        $allowed = $allowedTransitions[$model->request_status] ?? [];

        if (!in_array($requestedStatus, $allowed, true)) {
            throw ValidationException::withMessages([
                'request_status' => "Cannot change request status from {$model->request_status} to {$requestedStatus}.",
            ]);
        }
    }

    private function sendLifecycleMail(RequestFormPivot $model, string $event): void
    {
        $email = trim((string) $model->requester?->email);

        if ($email === '') {
            return;
        }

        Mail::to($email)->queue(new UseRequestLifecycleMail($model, $event));
    }

    private function splitName(string $name): array
    {
        $parts = collect(preg_split('/\s+/', trim($name)) ?: [])->filter()->values();

        if ($parts->isEmpty()) {
            return ['', null, ''];
        }

        if ($parts->count() === 1) {
            return [$parts[0], null, $parts[0]];
        }

        $fname = (string) $parts->shift();
        $lname = (string) $parts->pop();
        $mname = $parts->isNotEmpty() ? $parts->implode(' ') : null;

        return [$fname, $mname, $lname];
    }

    private function normalizeNullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
