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
        'status',
        'email_verified_at',
        'verification_sent_at',
        'rejection_remarks',
        'reviewed_by',
        'reviewed_at',
        'personnel_id',
    ];

    protected $casts = [
        'is_philrice_employee' => 'boolean',
        'email_verified_at' => 'datetime',
        'verification_sent_at' => 'datetime',
        'reviewed_at' => 'datetime',
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
        'status',
    ];

    protected $appends = [
        'full_name',
        'is_email_verified',
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
}
