<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSubformResponse extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'event_subform_responses';

    protected $fillable = [
        'id',
        'form_parent_id', //event_requirements -> event_id
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

    public function participant()
    {
        return $this->belongsTo(Participant::class, 'participant_id', 'id');
    }
}
