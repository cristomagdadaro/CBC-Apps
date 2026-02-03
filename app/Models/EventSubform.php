<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EventSubform extends BaseModel
{
    use HasFactory, SoftDeletes, Auditable;

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $table = 'event_subforms';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'event_id',
        'form_type',
        'step_type',
        'step_order',
        'is_enabled',
        'open_from',
        'open_to',
        'is_required',
        'max_slots',
        'config',
        'visibility_rules',
        'completion_rules',
    ];

    protected $casts = [
        'id' => 'string',
        'is_required' => 'boolean',
        'max_slots' => 'integer',
        'config' => 'array',
        'is_enabled' => 'boolean',
        'open_from' => 'datetime:Y-m-d H:i:s',
        'open_to' => 'datetime:Y-m-d H:i:s',
        'visibility_rules' => 'array',
        'completion_rules' => 'array',
    ];

    protected array $searchable = [
        'event_id',
        'form_type',
        'max_slots',
        'config'
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

        $openFrom = $this->open_from ?? data_get($this->config, 'open_from');
        $openTo = $this->open_to ?? data_get($this->config, 'open_to');

        if ($openFrom && $now->lt(Carbon::parse($openFrom))) {
            return false;
        }

        if ($openTo && $now->gt(Carbon::parse($openTo))) {
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
            'id',              // event_subforms.id
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
            'id',              // event_subforms.id
            'participant_id'   // event_subform_responses.participant_id
        );
    }
}
