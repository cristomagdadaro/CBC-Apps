<?php

namespace App\Repositories;

use App\Models\EventScanLog;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class FormScanRepo extends AbstractRepoService
{
    protected RegistrationRepo $registrationRepo;
    protected EventSubformRepo $eventSubformRepo;
    protected ParticipantStepStateRepo $stepStateRepo;
    protected EventSubformResponseRepo $responseRepo;

    public function __construct(
        EventScanLog $model,
        RegistrationRepo $registrationRepo,
        EventSubformRepo $eventSubformRepo,
        ParticipantStepStateRepo $stepStateRepo,
        EventSubformResponseRepo $responseRepo
    ) {
        parent::__construct($model);
        $this->registrationRepo = $registrationRepo;
        $this->eventSubformRepo = $eventSubformRepo;
        $this->stepStateRepo = $stepStateRepo;
        $this->responseRepo = $responseRepo;
    }
    /**
     * Define the model associated with this repository.
     */
    protected function model(): string
    {
        return EventScanLog::class;
    }

    public function processScan(string $eventId, array $validated): JsonResponse
    {
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
            $eventId,
            $scanType,
            $terminalId,
            $payloadHash,
            $signature,
            $payloadVersion,
            $payload,
            $parsed,
            $registrationId,
            $payloadEventId,
            $now
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
                return $this->buildResponse($eventId, $registrationId, $scanType, $status, $message, $payloadHash, $signature, $terminalId, $reason, $checks);
            }

            if ($payloadVersion === 'signed' && !$parsed['signature_valid']) {
                $reason = 'Signature mismatch.';
                return $this->buildResponse($eventId, $registrationId, $scanType, $status, $message, $payloadHash, $signature, $terminalId, $reason, $checks);
            }

            if (!$registrationId) {
                $reason = 'Missing registration id.';
                return $this->buildResponse($eventId, null, $scanType, $status, $message, $payloadHash, $signature, $terminalId, $reason, $checks);
            }

            $registration = $this->registrationRepo->findWithLock($registrationId);

            if (!$registration) {
                $reason = 'Registration not found.';
                return $this->buildResponse($eventId, $registrationId, $scanType, $status, $message, $payloadHash, $signature, $terminalId, $reason, $checks);
            }

            if ($payloadEventId && $payloadEventId !== $eventId) {
                $status = 'wrong_event';
                $message = 'QR code is for a different event.';
                $reason = 'Payload event mismatch.';
                return $this->buildResponse($eventId, $registrationId, $scanType, $status, $message, $payloadHash, $signature, $terminalId, $reason, $checks, $registration);
            }

            $registrationEventId = $this->resolveRegistrationEventId($registration);

            if ($registrationEventId !== null && $registrationEventId !== $eventId) {
                $status = 'wrong_event';
                $message = 'Registration does not belong to this event.';
                $reason = 'Registration event mismatch.';
                return $this->buildResponse($eventId, $registrationId, $scanType, $status, $message, $payloadHash, $signature, $terminalId, $reason, $checks, $registration);
            }

            $capacity = $this->capacityStatus($eventId, $scanType);
            $checks['capacity'] = $capacity;
            if ($capacity['status'] === 'full') {
                $status = 'full';
                $message = 'Event capacity reached.';
                $reason = 'Max slots reached.';
                return $this->buildResponse($eventId, $registrationId, $scanType, $status, $message, $payloadHash, $signature, $terminalId, $reason, $checks, $registration);
            }

            $eligibility = $this->eligibilityStatus($eventId, $registrationId, $scanType);
            $checks['eligibility'] = $eligibility;
            if ($eligibility['status'] === 'ineligible') {
                $status = 'ineligible';
                $message = 'Participant has missing required responses.';
                $reason = 'Missing requirements: ' . implode(', ', $eligibility['missing']);
                return $this->buildResponse($eventId, $registrationId, $scanType, $status, $message, $payloadHash, $signature, $terminalId, $reason, $checks, $registration);
            }

            $existingSuccess = $this->model->where('event_id', $eventId)
                ->where('registration_id', $registrationId)
                ->where('scan_type', $scanType)
                ->where('status', 'success')
                ->lockForUpdate()
                ->first();

            if ($scanType === 'checkin' && $registration->checked_in_at) {
                $status = 'already_scanned';
                $message = 'Already checked in.';
                $reason = 'Check-in already recorded.';
                return $this->buildResponse($eventId, $registrationId, $scanType, $status, $message, $payloadHash, $signature, $terminalId, $reason, $checks, $registration);
            }

            if ($scanType !== 'checkin' && $existingSuccess) {
                $status = 'already_scanned';
                $message = 'Already scanned for this action.';
                $reason = 'Duplicate scan.';
                return $this->buildResponse($eventId, $registrationId, $scanType, $status, $message, $payloadHash, $signature, $terminalId, $reason, $checks, $registration);
            }

            if ($scanType === 'checkin') {
                $registration->checked_in_at = $now;
                $registration->checked_in_by = Auth::user()->id();
                $registration->checkin_source = 'qr-scan';
                $registration->save();
            }

            $status = 'success';
            $message = $scanType === 'checkin' ? 'Check-in recorded.' : 'Scan accepted.';
            $reason = null;

            return $this->buildResponse($eventId, $registrationId, $scanType, $status, $message, $payloadHash, $signature, $terminalId, $reason, $checks, $registration);
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
        $log = $this->model->create([
            'event_id' => $eventId,
            'registration_id' => $registrationId,
            'scan_type' => $scanType,
            'status' => $status,
            'scanned_by' => Auth::user()->id(),
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
                'event_id' => $this->resolveRegistrationEventId($registration),
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
                'version' => 'invalid',
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

        $requirement = $this->eventSubformRepo->getRegistrationRequirement($eventId);

        $maxSlots = $requirement?->max_slots;

        if (!$maxSlots || $maxSlots <= 0) {
            return [
                'status' => 'ok',
                'max_slots' => null,
                'used' => null,
                'remaining' => null,
            ];
        }

        $used = $this->model->where('event_id', $eventId)
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

        $required = $this->eventSubformRepo->getRequiredSubforms($eventId);

        $missing = [];

        foreach ($required as $requirement) {
            $completed = $this->stepStateRepo->hasCompletedStep($registrationId, $requirement->id);

            if (!$completed) {
                $legacy = $this->responseRepo->hasSubmittedLegacyResponse($registrationId, $requirement->id);

                if ($legacy) {
                    $this->stepStateRepo->markLegacyAsCompleted($eventId, $registrationId, $requirement->id);
                    continue;
                }

                $missing[] = $requirement->form_type;
            }
        }

        return [
            'status' => empty($missing) ? 'ok' : 'ineligible',
            'missing' => $missing,
        ];
    }

    private function resolveRegistrationEventId(Registration $registration): ?string
    {
        $eventSubformId = $registration->event_subform_id;

        if (!$eventSubformId) {
            return null;
        }

        $event_id = $this->eventSubformRepo->findEventId($eventSubformId);

        if ($event_id) {
            return $event_id;
        }

        return (string) $eventSubformId;
    }
}
