<?php

namespace App\Repositories;

use App\Events\PersonnelRegistrationSubmitted;
use App\Mail\PersonnelRegistrationApprovedMail;
use App\Mail\PersonnelRegistrationVerificationMail;
use App\Models\Personnel;
use App\Models\PersonnelRegistration;
use App\Services\Personnel\PersonnelIdService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\UploadedFile;
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
        $data = $this->prepareRegistrationPayload($data);

        return DB::transaction(function () use ($data) {
            /** @var PersonnelRegistration $registration */
            $registration = $this->model->newQuery()->create([
                ...$data,
                'status' => PersonnelRegistration::STATUS_PENDING,
                'verification_sent_at' => now(),
            ]);

            Mail::to($registration->email)
                ->queue((new PersonnelRegistrationVerificationMail($registration))->afterCommit());

            PersonnelRegistrationSubmitted::dispatch($registration);

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

            $bypassEmail = $data['bypass_email_verification'] ?? false;

            if ($registration->status !== PersonnelRegistration::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'status' => ['This registration has already been reviewed.'],
                ]);
            }

            if ($status === PersonnelRegistration::STATUS_APPROVED && $registration->email_verified_at === null && !$bypassEmail) {
                throw ValidationException::withMessages([
                    'status' => ['Email must be verified before approval.'],
                ]);
            }

            if ($status === PersonnelRegistration::STATUS_APPROVED && $bypassEmail && $registration->email_verified_at === null) {
                $registration->email_verified_at = now();
                $registration->save();
            }

            if ($status === PersonnelRegistration::STATUS_APPROVED && $registration->requires_cbc_id_card) {
                $this->ensureRegistrationCanReceiveIdCard($registration);
            }

            $idIssuedAt = null;

            $payload = [
                'status' => $status,
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
            ];

            if ($status === PersonnelRegistration::STATUS_REJECTED) {
                $payload['rejection_remarks'] = $data['rejection_remarks'] ?? null;
            }

            if ($status === PersonnelRegistration::STATUS_APPROVED) {
                $personnel = $this->createPersonnelFromRegistration($registration, $idIssuedAt);
                $payload['personnel_id'] = $personnel->id;
                $payload['id_issued_at'] = $idIssuedAt;
            }

            $registration->forceFill($payload)->save();

            $registration = $registration->fresh(['personnel']);

            if ($status === PersonnelRegistration::STATUS_APPROVED && $registration->requires_cbc_id_card) {
                Mail::to($registration->email)
                    ->queue((new PersonnelRegistrationApprovedMail($registration))->afterCommit());
            }

            return $registration;
        });
    }

    private function createPersonnelFromRegistration(PersonnelRegistration $registration, mixed $idIssuedAt = null): Personnel
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
            'registration_type' => $registration->registration_type,
            'course_program' => $registration->course_program,
            'id_photo_path' => $registration->id_photo_path,
            'id_issued_at' => $idIssuedAt,
        ]);
    }

    private function ensureRegistrationCanReceiveIdCard(PersonnelRegistration $registration): void
    {
        if (!$registration->course_program || !$registration->id_photo_path) {
            throw ValidationException::withMessages([
                'status' => ['Course/program and 2x2 ID photo are required before issuing a CBC ID card.'],
            ]);
        }
    }

    public function getApprovedIdCardRegistrations(): \Illuminate\Support\Collection
    {
        return $this->model->newQuery()
            ->with('personnel')
            ->where('status', PersonnelRegistration::STATUS_APPROVED)
            ->whereIn('registration_type', PersonnelRegistration::idCardTypes())
            ->whereNotNull('personnel_id')
            ->whereNotNull('id_photo_path')
            ->whereNull('id_issued_at')
            ->orderByDesc('reviewed_at')
            ->get();
    }

    public function markIdCardsAsPrinted(array $registrationIds): void
    {
        DB::transaction(function () use ($registrationIds) {
            $now = now();

            $registrations = $this->model->newQuery()
                ->whereIn('id', $registrationIds)
                ->where('status', PersonnelRegistration::STATUS_APPROVED)
                ->whereNull('id_issued_at')
                ->get();

            $personnelIds = $registrations->pluck('personnel_id')->filter()->unique()->toArray();

            if ($registrations->isNotEmpty()) {
                $this->model->newQuery()
                    ->whereIn('id', $registrations->pluck('id'))
                    ->update(['id_issued_at' => $now]);
            }

            if (!empty($personnelIds)) {
                Personnel::query()
                    ->whereIn('id', $personnelIds)
                    ->update(['id_issued_at' => $now]);
            }
        });
    }

    private function prepareRegistrationPayload(array $data): array
    {
        $isPhilRiceEmployee = (bool) ($data['is_philrice_employee'] ?? false);
        $data['registration_type'] = $isPhilRiceEmployee
            ? PersonnelRegistration::TYPE_PHILRICE_EMPLOYEE
            : (string) ($data['registration_type'] ?? PersonnelRegistration::TYPE_STUDENT);

        if ($isPhilRiceEmployee) {
            $data['course_program'] = null;
            unset($data['id_photo']);

            return $data;
        }

        if (($data['id_photo'] ?? null) instanceof UploadedFile) {
            $data['id_photo_path'] = $data['id_photo']->store('personnel/id-photos');
        }

        unset($data['id_photo']);

        return $data;
    }

    private function ensureNoDuplicateActiveRegistration(array $data): void
    {
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $employeeId = trim((string) ($data['employee_id'] ?? ''));
        $fname = $this->normalizeNameValue($data['fname'] ?? null);
        $lname = $this->normalizeNameValue($data['lname'] ?? null);

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

        if ($fname !== '' && $lname !== '' && $this->personnelNameExists($fname, $lname)) {
            throw ValidationException::withMessages([
                'fname' => ['Personnel is already registered.'],
                'lname' => ['Personnel is already registered.'],
            ]);
        }

        $duplicate = $this->model->newQuery()
            ->whereIn('status', [
                PersonnelRegistration::STATUS_PENDING,
                PersonnelRegistration::STATUS_APPROVED,
            ])
            ->where(function ($query) use ($email, $employeeId, $fname, $lname) {
                $query->where('email', $email);

                if ($employeeId !== '') {
                    $query->orWhere('employee_id', $employeeId);
                }

                if ($fname !== '' && $lname !== '') {
                    $this->applyNameMatch($query, $fname, $lname, 'or');
                }
            })
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'email' => ['A pending or approved registration already exists for this personnel, email, or employee ID.'],
                'fname' => ['A pending or approved registration already exists for this personnel, email, or employee ID.'],
                'lname' => ['A pending or approved registration already exists for this personnel, email, or employee ID.'],
            ]);
        }
    }

    private function personnelNameExists(string $fname, string $lname): bool
    {
        $query = Personnel::query();
        $this->applyNameMatch($query, $fname, $lname);

        return $query->exists();
    }

    private function applyNameMatch(Builder $query, string $fname, string $lname, string $boolean = 'and'): void
    {
        $method = $boolean === 'or' ? 'orWhere' : 'where';

        $query->{$method}(function (Builder $subQuery) use ($fname, $lname) {
            $subQuery
                ->whereRaw('LOWER(TRIM(fname)) = ?', [$fname])
                ->whereRaw('LOWER(TRIM(lname)) = ?', [$lname]);
        });
    }

    private function normalizeNameValue(mixed $value): string
    {
        return strtolower(trim((string) $value));
    }
}
