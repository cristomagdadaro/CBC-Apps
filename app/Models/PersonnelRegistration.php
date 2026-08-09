<?php

namespace App\Models;

use App\Traits\Auditable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonnelRegistration extends BaseModel
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const TYPE_PHILRICE_EMPLOYEE = 'philrice_employee';
    public const TYPE_STUDENT = 'student';
    public const TYPE_OJT = 'ojt';
    public const TYPE_THESIS = 'thesis';

    protected $fillable = [
        'fname',
        'mname',
        'lname',
        'suffix',
        'position',
        'phone',
        'address',
        'email',
        'employee_id',
        'is_philrice_employee',
        'registration_type',
        'course_program',
        'affiliation',
        'id_photo_path',
        'status',
        'email_verified_at',
        'verification_sent_at',
        'rejection_remarks',
        'reviewed_by',
        'reviewed_at',
        'personnel_id',
        'id_issued_at',
        'expires_at',
    ];

    protected $casts = [
        'is_philrice_employee' => 'boolean',
        'email_verified_at' => 'datetime',
        'verification_sent_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'id_issued_at' => 'datetime',
        'expires_at' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected array $searchable = [
        'fname',
        'mname',
        'lname',
        'suffix',
        'email',
        'employee_id',
        'registration_type',
        'course_program',
        'affiliation',
        'status',
    ];

    protected $appends = [
        'full_name',
        'is_email_verified',
        'requires_cbc_id_card',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('g:i A M j, Y');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'id');
    }

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class, 'personnel_id', 'id');
    }

    public function getFullNameAttribute(): string
    {
        return collect([$this->fname, $this->mname, $this->lname, $this->suffix])
            ->filter()
            ->implode(' ');
    }

    public function getIsEmailVerifiedAttribute(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function getRequiresCbcIdCardAttribute(): bool
    {
        return in_array($this->registration_type, self::idCardTypes(), true);
    }

    public static function idCardTypes(): array
    {
        return [
            self::TYPE_STUDENT,
            self::TYPE_OJT,
            self::TYPE_THESIS,
        ];
    }
}
