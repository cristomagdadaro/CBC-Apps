<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Form extends BaseModel
{
    use HasFactory;

    protected   $table = 'forms';
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
        'has_pretest',
        'has_posttest',
        'has_preregistration',
    ];

    protected $casts = [
        'id' => 'string',
        'date_from' => 'datetime',
        'date_to' => 'datetime',
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
}
