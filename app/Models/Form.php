<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Form extends BaseModel
{
    use HasFactory;

    protected $table = 'forms';
    public $incrementing = false; // Disable auto-incrementing
    protected $keyType = 'string'; // Set the primary key as a string
    protected $fillable = [
        'id',
        'event_id',
        'title',
        'description',
        'details',
        'date_from',
        'date_to',
        'time_from',
        'time_to',
        'venue',
        'is_suspended',
        'is_expired',
        'max_slots',
        'style_tokens',
    ];

    protected $casts = [
        'id' => 'string',
        'date_from' => 'datetime',
        'date_to' => 'datetime',
        'style_tokens' => 'array',
    ];

    protected $hidden = [
        'updated_at',
    ];

    protected array $searchable = [
        'id',
        'event_id',
        'title',
        'description',
        'details',
        'date_from',
        'date_to',
        'time_from',
        'time_to',
        'venue',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function participants(): HasManyThrough
    {
        return $this->hasManyThrough(
            Participant::class,
            Registration::class,
            'event_id', // Foreign key on registrations table
            'id', // Foreign key on participants table
            'event_id', // Local key on forms table
            'participant_id' // Local key on registrations table
        );
    }

    public function responses(): HasManyThrough
    {
        return $this->hasManyThrough(
            EventSubformResponse::class,
            EventRequirement::class,
            'event_id',
            'form_parent_id',
            'id',
            'id'
        );
    }
    public function requirements(): HasMany
    {
        return $this->hasMany(EventRequirement::class, 'event_id', 'id');
    }

    public function isSuspended(): bool
    {
        return $this->is_suspended;
    }

    public function isFull(): bool
    {
        return $this->participants()->count() >= $this->max_slots;
    }

    public function isExpired(): bool
    {
        $expirationTime = Carbon::parse($this->date_to)->setTimeFromTimeString($this->time_to);
        return $this->is_expired && $expirationTime->isPast();
    }
}
