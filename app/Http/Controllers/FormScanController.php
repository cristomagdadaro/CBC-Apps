<?php

namespace App\Http\Controllers;

use App\Http\Requests\FormScanRequest;
use App\Models\EventSubform;
use App\Models\EventScanLog;
use App\Models\EventSubformResponse;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FormScanController extends Controller
{
    public function scan(FormScanRequest $request, string $event_id): JsonResponse
    {
        $validated = $request->validated();
        $payload = trim($validated['payload']);
        $scanType = $validated['scan_type'];
        $terminalId = $validated['terminal_id'] ?? null;
        $payloadHash = hash('sha256', $payload);
        $now = now();

        $parsed = $this->parsePayload($payload);
        $registrationId = $parsed['rid'] ?? null;
        $payloadEventId = $parsed['eid'] ?? null;
        $signature = $parsed['sig'] ?? null;
        $payloadVersion = $parsed['version'];

        return DB::transaction(function () use (
            $event_id,
            $scanType,
            $terminalId,
            $payloadHash,
            $now,
            $parsed,
            $registrationId,
            $payloadEventId,
            $signature,
            $payloadVersion,
            $payload
        ) {
            $status = 'invalid';
            $message = 'Invalid QR payload.';
            $reason = null;
            $checks = [
                'payload_version' => $payloadVersion,
                'signature_valid' => $parsed['signature_valid'] ?? false,
            ];

            if ($payloadVersion === 'invalid') {
                $reason = 'Payload parse failed.';
                return $this->buildResponse($event_id, $registrationId, $scanType, $status, $message, $payloadHash, $signature, $terminalId, $reason, $checks);
            }

            if ($payloadVersion === 'signed' && !$parsed['signature_valid']) {
                $reason = 'Signature mismatch.';
                return $this->buildResponse($event_id, $registrationId, $scanType, $status, $message, $payloadHash, $signature, $terminalId, $reason, $checks);
            }

            if (!$registrationId) {
                $reason = 'Missing registration id.';
                return $this->buildResponse($event_id, null, $scanType, $status, $message, $payloadHash, $signature, $terminalId, $reason, $checks);
            }

            $registration = Registration::where('id', $registrationId)->lockForUpdate()->first();

            if (!$registration) {
                $reason = 'Registration not found.';
                return $this->buildResponse($event_id, $registrationId, $scanType, $status, $message, $payloadHash, $signature, $terminalId, $reason, $checks);
            }

            if ($payloadEventId && $payloadEventId !== $event_id) {
                $status = 'wrong_event';
                $message = 'QR code is for a different event.';
                $reason = 'Payload event mismatch.';
                return $this->buildResponse($event_id, $registrationId, $scanType, $status, $message, $payloadHash, $signature, $terminalId, $reason, $checks, $registration);
            }

            if ($registration->event_id !== $event_id) {
                $status = 'wrong_event';
                $message = 'Registration does not belong to this event.';
                $reason = 'Registration event mismatch.';
                return $this->buildResponse($event_id, $registrationId, $scanType, $status, $message, $payloadHash, $signature, $terminalId, $reason, $checks, $registration);
            }

            $capacity = $this->capacityStatus($event_id, $scanType);
            $checks['capacity'] = $capacity;
            if ($capacity['status'] === 'full') {
                $status = 'full';
                $message = 'Event capacity reached.';
                $reason = 'Max slots reached.';
                return $this->buildResponse($event_id, $registrationId, $scanType, $status, $message, $payloadHash, $signature, $terminalId, $reason, $checks, $registration);
            }

            $eligibility = $this->eligibilityStatus($event_id, $registrationId, $scanType);
            $checks['eligibility'] = $eligibility;
            if ($eligibility['status'] === 'ineligible') {
                $status = 'ineligible';
                $message = 'Participant has missing required responses.';
                $reason = 'Missing requirements: ' . implode(', ', $eligibility['missing']);
                return $this->buildResponse($event_id, $registrationId, $scanType, $status, $message, $payloadHash, $signature, $terminalId, $reason, $checks, $registration);
            }

            $existingSuccess = EventScanLog::where('event_id', $event_id)
                ->where('registration_id', $registrationId)
                ->where('scan_type', $scanType)
                ->where('status', 'success')
                ->lockForUpdate()
                ->first();

            if ($scanType === 'checkin' && $registration->checked_in_at) {
                $status = 'already_scanned';
                $message = 'Already checked in.';
                $reason = 'Check-in already recorded.';
                return $this->buildResponse($event_id, $registrationId, $scanType, $status, $message, $payloadHash, $signature, $terminalId, $reason, $checks, $registration);
            }

            if ($scanType !== 'checkin' && $existingSuccess) {
                $status = 'already_scanned';
                $message = 'Already scanned for this action.';
                $reason = 'Duplicate scan.';
                return $this->buildResponse($event_id, $registrationId, $scanType, $status, $message, $payloadHash, $signature, $terminalId, $reason, $checks, $registration);
            }

            if ($scanType === 'checkin') {
                $registration->checked_in_at = $now;
                $registration->checked_in_by = auth()->id();
                $registration->checkin_source = 'qr-scan';
                $registration->save();
            }

            $status = 'success';
            $message = $scanType === 'checkin' ? 'Check-in recorded.' : 'Scan accepted.';
            $reason = null;

            return $this->buildResponse($event_id, $registrationId, $scanType, $status, $message, $payloadHash, $signature, $terminalId, $reason, $checks, $registration);
        });
    }

    private function buildResponse(
        string $eventId,
        ?string $registrationId,
        string $scanType,
        string $status,
        string $message,
        string $payloadHash,
        ?string $signature,
        ?string $terminalId,
        ?string $reason,
        array $checks,
        ?Registration $registration = null
    ): JsonResponse {
        $log = EventScanLog::create([
            'event_id' => $eventId,
            'registration_id' => $registrationId,
            'scan_type' => $scanType,
            'status' => $status,
            'scanned_by' => auth()->id(),
            'scanned_at' => now(),
            'payload_hash' => $payloadHash,
            'signature' => $signature,
            'terminal_id' => $terminalId,
            'reason' => $reason,
            'meta' => $checks,
        ]);

        return response()->json([
            'status' => $status,
            'message' => $message,
            'scan_type' => $scanType,
            'event_id' => $eventId,
            'scanned_at' => $log->scanned_at,
            'registration' => $registration ? [
                'id' => $registration->id,
                'participant_id' => $registration->participant_id,
                'name' => $registration->participant?->name,
                'email' => $registration->participant?->email,
                'organization' => $registration->participant?->organization,
                'checked_in_at' => $registration->checked_in_at,
            ] : null,
            'checks' => $checks,
        ]);
    }

    private function parsePayload(string $payload): array
    {
        $raw = trim($payload);
        $decoded = null;
        $data = null;

        if (Str::startsWith($raw, '{')) {
            $data = json_decode($raw, true);
        } else {
            $decoded = base64_decode($raw, true);
            if ($decoded !== false && Str::startsWith(ltrim($decoded), '{')) {
                $data = json_decode($decoded, true);
            }
        }

        if (is_array($data) && isset($data['rid'])) {
            $signatureValid = $this->verifySignature($data);

            return [
                'version' => 'signed',
                'rid' => $data['rid'] ?? null,
                'eid' => $data['eid'] ?? null,
                'sig' => $data['sig'] ?? null,
                'signature_valid' => $signatureValid,
            ];
        }

        if (Str::isUuid($raw)) {
            return [
                'version' => 'legacy',
                'rid' => $raw,
                'eid' => null,
                'sig' => null,
                'signature_valid' => false,
            ];
        }

        return [
            'version' => 'invalid',
            'signature_valid' => false,
        ];
    }

    private function verifySignature(array $data): bool
    {
        $rid = $data['rid'] ?? null;
        $eid = $data['eid'] ?? null;
        $iat = $data['iat'] ?? null;
        $nonce = $data['nonce'] ?? null;
        $sig = $data['sig'] ?? null;

        if (!$rid || !$eid || !$iat || !$nonce || !$sig) {
            return false;
        }

        $payload = $rid . '|' . $eid . '|' . $iat . '|' . $nonce;
        $key = $this->scanKey();
        $expected = hash_hmac('sha256', $payload, $key);

        return hash_equals($expected, $sig);
    }

    private function scanKey(): string
    {
        $key = config('app.key');

        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        return $key;
    }

    private function capacityStatus(string $eventId, string $scanType): array
    {
        if ($scanType !== 'checkin') {
            return [
                'status' => 'ok',
                'max_slots' => null,
                'used' => null,
                'remaining' => null,
            ];
        }

        $requirement = EventSubform::where('event_id', $eventId)
            ->where('form_type', 'registration')
            ->first();

        $maxSlots = $requirement?->max_slots;

        if (!$maxSlots || $maxSlots <= 0) {
            return [
                'status' => 'ok',
                'max_slots' => null,
                'used' => null,
                'remaining' => null,
            ];
        }

        $used = EventScanLog::where('event_id', $eventId)
            ->where('scan_type', 'checkin')
            ->where('status', 'success')
            ->count();

        $remaining = max($maxSlots - $used, 0);

        return [
            'status' => $used >= $maxSlots ? 'full' : 'ok',
            'max_slots' => $maxSlots,
            'used' => $used,
            'remaining' => $remaining,
        ];
    }

    private function eligibilityStatus(string $eventId, string $registrationId, string $scanType): array
    {
        if ($scanType === 'checkin') {
            return [
                'status' => 'ok',
                'missing' => [],
            ];
        }

        $required = EventSubform::where('event_id', $eventId)
            ->where('is_required', true)
            ->whereNotIn('form_type', ['registration'])
            ->get(['id', 'form_type']);

        $missing = [];

        foreach ($required as $requirement) {
            $exists = EventSubformResponse::where('form_parent_id', $requirement->id)
                ->where('participant_id', $registrationId)
                ->exists();

            if (!$exists) {
                $missing[] = $requirement->form_type;
            }
        }

        return [
            'status' => empty($missing) ? 'ok' : 'ineligible',
            'missing' => $missing,
        ];
    }
}
