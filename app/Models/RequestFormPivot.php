<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class RequestFormPivot extends BaseModel
{
    use HasFactory, HasUuids, Auditable;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_RELEASED = 'released';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_REJECTED = 'rejected';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $table = 'request_forms_pivot';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'requester_id',
        'form_id',
        'request_status',
        'agreed_clause_1',
        'agreed_clause_2',
        'agreed_clause_3',
        'approval_constraint',
        'disapproved_remarks',
        'approved_by',
        'approved_at',
        'released_by',
        'released_at',
        'returned_by',
        'returned_at',
        'overdue_notified_at',
    ];

    protected array $searchable = [
        'id',
        'request_status',
        'requester.name',
        'requester.affiliation',
        'requester.position',
        'requester.philrice_id',
        'request_form.project_title',
        'request_form.request_purpose',
    ];

    protected $casts = [
        'id' => 'string',
        'agreed_clause_1' => 'boolean',
        'agreed_clause_2' => 'boolean',
        'agreed_clause_3' => 'boolean',
        'approved_at' => 'datetime',
        'released_at' => 'datetime',
        'returned_at' => 'datetime',
        'overdue_notified_at' => 'datetime',
    ];

    protected $appends = [
        'display_status',
        'is_overdue',
        'schedule_end_at',
        'next_action_label',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Requester::class, 'requester_id', 'id');
    }

    public function request_form(): BelongsTo
    {
        return $this->belongsTo(UseRequestForm::class, 'form_id', 'id');
    }

    public function getScheduleEndAtAttribute(): ?string
    {
        $form = $this->relationLoaded('request_form') ? $this->request_form : null;

        if (!$form?->date_of_use_end || !$form?->time_of_use_end) {
            return null;
        }

        return Carbon::parse("{$form->date_of_use_end} {$form->time_of_use_end}")->toDateTimeString();
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->request_status !== self::STATUS_RELEASED || $this->returned_at !== null) {
            return false;
        }

        $endAt = $this->schedule_end_at;

        return $endAt !== null && now()->greaterThan(Carbon::parse($endAt));
    }

    public function getDisplayStatusAttribute(): string
    {
        return $this->is_overdue ? 'overdue' : (string) $this->request_status;
    }

    public function getNextActionLabelAttribute(): ?string
    {
        return match ($this->request_status) {
            self::STATUS_PENDING => 'Approve',
            self::STATUS_APPROVED => 'Release',
            self::STATUS_RELEASED => 'Return',
            default => null,
        };
    }
}
