<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class EventSubformResponse extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'event_subform_responses';

    protected $fillable = [
        'id',
        'form_parent_id', //event_requirements -> id
        'participant_id', //registrations -> id
        'subform_type',  // preregistration, feedback, etc.
        'response_data', // JSON or text data
    ];

    protected $casts = [
        'id' => 'string',
        'response_data' => 'array',
    ];

    public function formParent()
    {
        return $this->belongsTo(EventRequirement::class, 'form_parent_id', 'id');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'participant_id', 'id');
    }

    public function participant(): HasOneThrough
    {
        return $this->hasOneThrough(
            Participant::class,
            Registration::class,
            'id', // Registration table primary key
            'id', // Participant table primary key
            'participant_id', // Foreign key on subform responses pointing to registrations.id
            'participant_id' // Foreign key on registrations pointing to participants.id
        );
    }
}
