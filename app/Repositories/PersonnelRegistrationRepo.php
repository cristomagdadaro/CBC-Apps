<?php

namespace App\Repositories;

use App\Mail\PersonnelRegistrationVerificationMail;
use App\Models\Personnel;
use App\Models\PersonnelRegistration;
use App\Services\Personnel\PersonnelIdService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class PersonnelRegistrationRepo extends AbstractRepoService
{
    public function __construct(
        PersonnelRegistration $model,
        private readonly PersonnelIdService $personnelIdService,
    ) {
        parent::__construct($model);
    }

    public function createGuestRegistration(array $data): PersonnelRegistration
    {
        $this->ensureNoDuplicateActiveRegistration($data);

        return DB::transaction(function () use ($data) {
            /** @var PersonnelRegistration $registration */
            $registration = $this->model->newQuery()->create([
                ...$data,
                'status' => PersonnelRegistration::STATUS_PENDING,
                'verification_sent_at' => now(),
            ]);

            Mail::to($registration->email)
                ->queue((new PersonnelRegistrationVerificationMail($registration))->afterCommit());

            return $registration;
        });
    }

    public function verifyEmail(PersonnelRegistration $registration): PersonnelRegistration
    {
        if ($registration->email_verified_at === null) {
            $registration->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }

        return $registration;
    }

    public function updateStatus(string $id, array $data, ?string $reviewerId): PersonnelRegistration
    {
        return DB::transaction(function () use ($id, $data, $reviewerId) {
            /** @var PersonnelRegistration $registration */
            $registration = $this->model->newQuery()->findOrFail($id);
            $status = $data['status'];

            if ($registration->status !== PersonnelRegistration::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'status' => ['This registration has already been reviewed.'],
                ]);
            }

            if ($status === PersonnelRegistration::STATUS_APPROVED && $registration->email_verified_at === null) {
                throw ValidationException::withMessages([
                    'status' => ['Email must be verified before approval.'],
                ]);
            }

            $payload = [
                'status' => $status,
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
            ];

            if ($status === PersonnelRegistration::STATUS_REJECTED) {
                $payload['rejection_remarks'] = $data['rejection_remarks'] ?? null;
            }

            if ($status === PersonnelRegistration::STATUS_APPROVED) {
                $personnel = $this->createPersonnelFromRegistration($registration);
                $payload['personnel_id'] = $personnel->id;
            }

            $registration->forceFill($payload)->save();

            return $registration->fresh(['personnel']);
        });
    }

    private function createPersonnelFromRegistration(PersonnelRegistration $registration): Personnel
    {
        $employeeId = $registration->is_philrice_employee
            ? $registration->employee_id
            : $this->personnelIdService->consumeNextExternalEmployeeId();

        if (!$employeeId) {
            throw ValidationException::withMessages([
                'employee_id' => ['Employee ID is required before approval.'],
            ]);
        }

        if (Personnel::query()->where('employee_id', $employeeId)->exists()) {
            throw ValidationException::withMessages([
                'employee_id' => ['Employee ID is already registered.'],
            ]);
        }

        if (Personnel::query()->where('email', $registration->email)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['Email is already registered.'],
            ]);
        }

        return Personnel::query()->create([
            'fname' => $registration->fname,
            'mname' => $registration->mname,
            'lname' => $registration->lname,
            'suffix' => $registration->suffix,
            'position' => $registration->position,
            'phone' => $registration->phone,
            'address' => $registration->address,
            'email' => $registration->email,
            'email_verified_at' => $registration->email_verified_at,
            'employee_id' => $employeeId,
        ]);
    }

    private function ensureNoDuplicateActiveRegistration(array $data): void
    {
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $employeeId = trim((string) ($data['employee_id'] ?? ''));

        if (Personnel::query()->where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['Email is already registered.'],
            ]);
        }

        if ($employeeId !== '' && Personnel::query()->where('employee_id', $employeeId)->exists()) {
            throw ValidationException::withMessages([
                'employee_id' => ['Employee ID is already registered.'],
            ]);
        }

        $duplicate = $this->model->newQuery()
            ->whereIn('status', [
                PersonnelRegistration::STATUS_PENDING,
                PersonnelRegistration::STATUS_APPROVED,
            ])
            ->where(function ($query) use ($email, $employeeId) {
                $query->where('email', $email);

                if ($employeeId !== '') {
                    $query->orWhere('employee_id', $employeeId);
                }
            })
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'email' => ['A pending or approved registration already exists for this email or employee ID.'],
            ]);
        }
    }
}
