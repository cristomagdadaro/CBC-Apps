<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Str;

class EventSubformResponse extends Model
{
    use HasFactory, HasUuids;

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $table = 'event_subform_responses';

    protected $fillable = [
        'id',
        'form_parent_id', //event_subforms -> id
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
        return $this->belongsTo(EventSubform::class, 'form_parent_id', 'id');
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class, 'participant_id');
    }


    public function participant()
    {
        return $this->hasOneThrough(
            Participant::class,
            Registration::class,
            'id',              // registrations.id
            'id',              // participants.id
            'participant_id', // event_subform_responses.participant_id
            'participant_id'  // registrations.participant_id
        );
    }
}
