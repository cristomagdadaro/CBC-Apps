<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class EventRequirement extends BaseModel
{
    use HasFactory;

    protected $table = 'event_requirements';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'event_id',
        'form_type',
        'is_required',
        'config',
    ];

    protected $casts = [
        'id' => 'string',
        'is_required' => 'boolean',
        'config' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class, 'event_id', 'event_id');
    }

    /**
     * Determine if this requirement/form is currently open based on its window.
     */
    public function isOpen(): bool
    {
        $now = Carbon::now();

        if ($this->open_from && $now->lt($this->open_from)) {
            return false;
        }

        if ($this->open_to && $now->gt($this->open_to)) {
            return false;
        }

        return true;
    }

    public function subformResponses()
    {
        return $this->hasMany(EventSubformResponse::class, 'form_parent_id', 'id');
    }

    public function registrations()
    {
        return $this->hasManyThrough(
            Registration::class,
            EventSubformResponse::class,
            'form_parent_id', // event_subform_responses.form_parent_id
            'id',              // registrations.id
            'id',              // event_requirements.id
            'participant_id'   // event_subform_responses.participant_id
        );
    }

    public function responses()
    {
        return $this->hasMany(EventSubformResponse::class, 'form_parent_id', 'id')->with('participant');
    }

    // get all participants who submitted responses for this requirement
    public function participants()
    {
        return $this->hasManyThrough(
            Participant::class,
            EventSubformResponse::class,
            'form_parent_id', // event_subform_responses.form_parent_id
            'id',              // participants.id
            'id',              // event_requirements.id
            'participant_id'   // event_subform_responses.participant_id
        );
    }
}
