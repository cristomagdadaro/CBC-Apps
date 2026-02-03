<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipantStepState extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'participant_step_states';

    protected $fillable = [
        'event_id',
        'participant_id',
        'event_subform_id',
        'status',
        'completed_at',
        'completion_hash',
        'meta',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'meta' => 'array',
    ];

    public const STATUS_LOCKED = 'locked';
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_DISABLED = 'disabled';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_FULL = 'full';
    public const STATUS_HIDDEN = 'hidden';
}
